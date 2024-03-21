<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Core\CipherSweetEncryption;

use NazmulIslam\Utility\Logger\Logger;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Arr;
use ParagonIE\CipherSweet\CipherSweet as CipherSweetEngine;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\Exception\InvalidCiphertextException;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use ParagonIE\CipherSweet\KeyRotation\RowRotator;
use ParagonIE\ConstantTime\Hex;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class Methods
{
    public function ensureValidInput($modelClass): bool
    {
        /** @var class-string<\Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted> $modelClass */

        if (!class_exists($modelClass)) {


            return false;
        }

        $newClass = (new $modelClass());

        $newClass::boot();

        if (!$newClass instanceof CipherSweetEncrypted) {
            return false;
        }

        return true;
    }


    /**
     * @param class-string<\Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted> $modelClass
     *
     * @return void
     */
    public function encryptModelValues(string $modelClass, string $newKey, string $oldKey): void
    {


        $newCipherSweetEngine = new CipherSweetEngine(new StringProvider($newKey));
        $oldCipherSweetEngine = new CipherSweetEngine(new StringProvider($oldKey));


        $newClass = (new $modelClass());

        DB::connection('app')->table($newClass->getTable())
            ->orderBy((new $modelClass())
                ->getKeyName(), 'asc')
            ->each(function (object $model) use ($modelClass, $newClass, $newCipherSweetEngine, $oldCipherSweetEngine) {
                $model = (array) $model;

                $oldRow = new EncryptedRow($oldCipherSweetEngine, $newClass->getTable());
                // $modelClass::boot();
                $modelClass::configureCipherSweet($oldRow);


                $newRow = new EncryptedRow(
                    $newCipherSweetEngine,
                    $newClass->getTable(),
                );

                // $modelClass::boot();
                $modelClass::configureCipherSweet($newRow);

                Logger::debug('old row', [$oldRow]);
                Logger::debug('new row', [$newRow]);


                $rotator = new RowRotator($oldRow, $newRow);

                Logger::debug('check', [$rotator->needsReEncrypt($model)]);
                Logger::debug('model', [$model]);
                if ($rotator->needsReEncrypt($model)) {
                    try {


                        [$ciphertext, $indices] = $rotator->prepareForUpdate($model);
                    } catch (InvalidCiphertextException $e) {
                        [$ciphertext, $indices] = $newRow->prepareRowForStorage($model);
                    }

                    DB::connection('app')->table($newClass->getTable())
                        ->where($newClass->getKeyName(), $model[$newClass->getKeyName()])
                        ->update(Arr::only($ciphertext, $newRow->listEncryptedFields()));

                    foreach ($indices as $name => $value) {
                        DB::connection('app')->table('blind_indexes')->updateOrInsert([
                            'indexable_type' => $newClass->getMorphClass(),
                            'indexable_id' => $model[$newClass->getKeyName()],
                            'name' => $name,
                        ], [
                            'value' => $value,
                        ]);
                    }
                }
            });
    }


    public function backupOldEncryptedData($stringModels): void
    {
        // Loop through each model
        foreach ($stringModels ?? [] as $stringModel) {

            $columnNamesRaw = DB::connection('app')->table('blind_indexes')
                ->where('indexable_type', $stringModel)
                ->distinct('name')
                ->pluck('name')
                ->toArray();

            $correctColumnNames = [];

            foreach ($columnNamesRaw as $rawColumnName) {
                $correctColumnNames[] = str_replace('_index', '', $rawColumnName);
            }

            // Make an instance
            $modelInstance = new $stringModel;

            $tableName = $modelInstance->getTable();

            $backupKey  = "encryption_key_rotation_backup";
            $idColumn   = $modelInstance->getKeyName();

            $columnsToFetchIncludingId = array_merge($correctColumnNames, [$idColumn]);

            $results = DB::connection('app')->table($tableName)->select($columnsToFetchIncludingId)->get();

            foreach ($results as $result) {

                $columnWiseValues = [];

                foreach ($correctColumnNames as $column) {

                    $originalValue = $result->{$column}; // Get the original value from the column

                    // make an easy to decrypt array for data backup i.e if column email then => array['email'] = old encrypted value 
                    $columnWiseValues[$column] = $originalValue;
                }

                // Fill up the backup column with data
                DB::connection('app')->table($tableName)
                    ->where($idColumn, $result->{$idColumn})
                    ->update([
                        $backupKey => json_encode($columnWiseValues)
                    ]);
            }
        }
    }

    public function backupBlindIndexes($stringModel = null): void
    {
        $query = DB::connection('app')->table('blind_indexes');

        if ($stringModel !== null) {
            $query = $query->where('indexable_type', $stringModel);
        }

        $records = $query->get();

        foreach ($records ?? [] as $record) {
            DB::connection('app')->table('blind_indexes')
                ->where('indexable_id', $record->indexable_id)
                ->where('indexable_type', $record->indexable_type)
                ->update(['encryption_key_rotation_backup' => $record->value]);
        }
    }

    public function resetAllFields($stringModels): void
    {
        // Loop through each model
        foreach ($stringModels as $stringModel) {

            // Make an instance
            $modelInstance = new $stringModel;

            $tableName = $modelInstance->getTable();
            $backupKey = "encryption_key_rotation_backup";
            $idKey     = $modelInstance->getKeyName();

            $results = DB::connection('app')->table($tableName)->get();

            Logger::debug('results from tenant', [$results]);
            foreach ($results as $result) {
                if ($result->{$backupKey} != null) {
                    $array = json_decode($result->{$backupKey}, true);

                    $arrayToUpdate = [];

                    if (is_array($array) && count($array)) {

                        // REQUIRES DECRYPTION TWICE IN ORDER TO EXTRACT THE VALUE
                        foreach ($array as $key => $value) {
                            $arrayToUpdate[$key] = $value;
                        }

                        DB::connection('app')->table($tableName)
                            ->where($idKey, $result->{$idKey})
                            ->update($arrayToUpdate);
                    }
                }
            }
        }
    }

    public function resetAllBlindIndexes(): void
    {
        $records = DB::connection('app')->table('blind_indexes')->get();

        foreach ($records as $record) {
            DB::connection('app')->table('blind_indexes')
                ->where('indexable_id', $record->indexable_id)
                ->where('indexable_type', $record->indexable_type)
                ->update(['value' => $record->encryption_key_rotation_backup]);
        }
    }
}
