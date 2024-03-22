<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Console\Commands;

use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NazmulIslam\Utility\Console\Traits\CommonTraits;
use NazmulIslam\Utility\Core\CipherSweetEncryption\EncryptModel;
use NazmulIslam\Utility\Core\CipherSweetEncryption\Methods;
use NazmulIslam\Utility\Logger\Logger;
use NazmulIslam\Utility\Utility;
use ParagonIE\ConstantTime\Hex;

/**
 * //TODO the relative file paths need to be fixed for installation
 */
class UpdateEncryptionKeyCommand extends Command
{

    use CommonTraits;

    protected $commandName = 'update:encryption-key';
    protected $commandDescription = "Update encryption key for all models which are applicable";

    protected function configure()
    {


        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription)
            ->setHelp('This command allows you to write encryption logic for every model which has encrypted columns')
            ->addArgument('db', InputArgument::REQUIRED, 'db option is required');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $oldKey = CIPHER_SWEET_KEY;

        $newKey = $this->generateRandomHexKey();

        $data = ['old_env_key' => $oldKey];


        $database = $input->getArgument(name: 'db');

        if ($database == 'tenant') {
            $output->writeln('Starting to configure encryption for models');
            $this->runForEveryTenant($output, $oldKey, $newKey, $data);
        } else {
            $output->writeln('No need to configure');
        }

        return 0;
    }



    /**
     * @TODO need to remove the tenant logic and run for a current DB
     */
    private function runForEveryTenant($output, $oldKey, $newKey, $data)
    {

        // $tenants = \NazmulIslam\Utility\Models\NazmulIslam\Utility\Tenant::get();

        // $checkTenants       = 0;
        // $updatableTenantIds = [];
        // $retrieveOldKey     = null;

        // foreach ($tenants->toArray() as $tenant) {

        //     putenv(sprintf('%s=%s', 'DB_NAME', $tenant['tenant_db_name']));
        //     $this->setupTenantDB();

        //     if (Utility::tenantEncryptionKey($tenant['tenant_account_name']) == null) {


        //         $tenantConfig = $tenant['tenant_configuration'] != null ? json_decode($tenant['tenant_configuration'], true) : null;

        //         if ($retrieveOldKey === null && $tenantConfig === null) {
        //             $tenantConfig = ['tempEncKey' => $newKey];
        //         } else if ($retrieveOldKey !== null && $tenantConfig === null) {
        //             $tenantConfig = ['tempEncKey' => $retrieveOldKey];
        //         } else if ($tenantConfig !== null && is_array($tenantConfig)) {

        //             if (array_key_exists('tempEncKey', $tenantConfig)) {
        //                 $newKey = $tenantConfig['tempEncKey'];
        //                 Logger::debug('TEMP KEY', [$tenantConfig['tempEncKey']]);
        //             } else {
        //                 $tenantConfig['tempEncKey'] = $newKey;
        //             }
        //         }

        //         // set the putenv so that when new record is created its created using the new key
        //         putenv("CIPHER_SWEET_KEY=" . $newKey);


        //         \NazmulIslam\Utility\Models\NazmulIslam\Utility\Tenant::where('tenant_id', $tenant['tenant_id'])->update([
        //             'tenant_configuration' => json_encode($tenantConfig)
        //         ]);



        //         $updatableTenantIds[] = $tenant['tenant_id'];

        //         $cipherSweetMethods = new Methods();

        //         $stringModels = $this->fetchAllEncryptedModels();

        //         $cipherSweetMethods->backupOldEncryptedData($stringModels);
        //         $cipherSweetMethods->backupBlindIndexes();

        //         DB::connection('app')->beginTransaction();

        //         DB::connection('app')->table('encryption_history')
        //             ->updateOrInsert(
        //                 ['old_env_key' => $oldKey], // Search condition
        //                 $data // Data to insert or update
        //             );

        //         try {

        //             $this->updateModels($output, $oldKey, $newKey);

        //             DB::connection('app')->commit();

        //             $checkTenants++;

        //             $output->writeln('encryption successful for database => ' . $tenant['tenant_db_name']);
        //         } catch (\Exception $ex) {
        //             Logger::error('Cauaght Exception error  on ' . gethostname() . ' ' . $ex->getMessage(), (array) $ex->getTraceAsString());
        //             \Resque_Event::trigger('onFailure', [$ex->getMessage(), (array) $ex->getTraceAsString()]);

        //             Db::connection('app')->rollBack();

        //             $data = [
        //                 'error_data' => json_encode(['message' => $ex->getMessage(), 'trace' => $ex->getTraceAsString()])
        //             ];

        //             DB::connection('app')->table('encryption_history')
        //                 ->where('old_env_key', $oldKey)->update($data);

        //             $output->writeln('encryption unsuccessful for database => ' . $tenant['tenant_db_name']);
        //         }
        //     }
        // }

        // RESETTING LOGIC FOR TENANTS DATA
        // if ($checkTenants === count($updatableTenantIds)) {

        //     $this->updateEnvironmentVariable('CIPHER_SWEET_KEY', $newKey);

        //     $this->removeTempKeys($output, $updatableTenantIds);
        // } else {

        //     $this->resetEncryption($output, $updatableTenantIds);

        //     $output->writeln('Error encountered. Trying to reset data');
        // }


        // Update the environment variable
    }


    public function removeTempKeys($output, $tenantIds)
    {

        //$tenantConfig = $tenant['tenant_configuration'] != null ? json_decode($tenant['tenant_configuration'], true) : null;
        $tenantConfig = null;
        if ($tenantConfig !== null && is_array($tenantConfig)) {

            if (array_key_exists('tempEncKey', $tenantConfig)) {
                unset($tenantConfig['tempEncKey']);
            }
        }
        $output->writeln('Removing temp keys from database');
    }

    public function resetEncryption($output, $tenantIds)
    {

        



            $stringModels = $this->fetchAllEncryptedModels();

            $cipherSweetMethods = new Methods();

            $cipherSweetMethods->resetAllFields($stringModels);
            $cipherSweetMethods->resetAllBlindIndexes();


            $output->writeln('Resetting data ');
    }


    private function fetchAllEncryptedModels()
    {
        return DB::connection('app')->table('blind_indexes')
            ->distinct('indexable_type')
            ->pluck('indexable_type')
            ->toArray();
    }

    private function updateEnvironmentVariable($variable, $value)
    {
        $envFilePath = __DIR__ . '/../../../.env';

        // Read the contents of the .env file
        $envContent = file_get_contents($envFilePath);

        // Define the new variable assignment
        $newVariableAssignment = "{$variable}={$value}";

        // Check if the variable already exists in the file
        if (preg_match("/^{$variable}=.*/m", $envContent)) {
            // PATTERN NOTE 
            // ^ represents the start of line
            // the variable & equals sign in the string and * represents the string afterwords
            // /m is a flag that makes it multi line search 
            $updatedEnvContent = preg_replace(
                "/^{$variable}=.*/m",
                $newVariableAssignment,
                $envContent
            );
        } else {
            // If it doesn't exist, add it to the end of the file
            $updatedEnvContent = "{$envContent}\n{$newVariableAssignment}\n";
        }

        // Write the updated content back to the .env file
        file_put_contents($envFilePath, $updatedEnvContent);
    }


    private function generateRandomHexKey()
    {
        $keyLength = 32;
        $bytes = random_bytes($keyLength);
        return Hex::encode($bytes);
        // return bin2hex($bytes);
    }


    private function updateModels($output, $oldKey, $newKey)
    {
        $systemModels = DB::connection('app')
            ->table('encryption_models')
            ->select(['model_name', 'model_path', 'encryption_model_id'])
            ->get();

        $modelsEncrypted = [];
        foreach ($systemModels ?? [] as $model) {


            if ((new $model->model_path)->count()) {

                $modelsEncrypted[] = $model->model_path;

                $tableName = (new $model->model_path)->getTable(); // Get the table name associated with the model

                // Get all column names for the table
                $columns = DB::connection('app')->select("SHOW COLUMNS FROM $tableName");

                // Extract column names from the result
                $columnNames = array_column($columns, 'Field');

                $columnsToEncrypt = DB::connection('app')
                    ->table('encryption_model_column_details')
                    ->where('encryption_model_id', $model->encryption_model_id)
                    ->pluck('column_name')
                    ->toArray();

                // Find the columns that are the same in both arrays
                $commonColumns = array_intersect($columnNames, $columnsToEncrypt);

                if (count($commonColumns)) {

                    // try {

                    $encryptModels = new EncryptModel($model->model_path, $newKey, $oldKey);
                    $encryptModels->handle();

                    DB::connection('app')
                        ->table('encryption_model_column_details')
                        ->where('encryption_model_id', $model->encryption_model_id)
                        ->update(['is_encrypted' => 1]);


                    DB::connection('app')
                        ->table('encryption_models')
                        ->where('model_path', $model->model_path)
                        ->update(['is_encrypted' => 1]);



                    // call model encryption job
                    // $encryptionJob = new SpecificModelEncryptionJob($model->model_path, $commonColumns);
                    // $encryptionJob->handle();

                    $output->writeln('successfully encrypted model => ' . $model->model_name);
                    // } catch (\Exception $ex) {



                    //     Logger::error('Cauaght Exception error  on ' . gethostname() . ' ' . $ex->getMessage(), (array) $ex->getTraceAsString());

                    //     \Resque_Event::trigger('onFailure', [$ex->getMessage(), (array) $ex->getTraceAsString()]);

                    //     $output->writeln('error while encrypting  model => ' . $model->model_name);
                    // }
                } else {
                    $output->writeln('no column found to encrypt  model => ' . $model->model_name);
                }

                $jsonEncodedModels = json_encode($modelsEncrypted);

                DB::connection('app')->table('encryption_history')
                    ->where('old_env_key', $oldKey)
                    ->update(['model_names' => $jsonEncodedModels]);
            }
        }
    }
}
