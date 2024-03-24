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
use NazmulIslam\Utility\Logger\Logger;


/**
 * //TODO the relative file paths need to be fixed for installation
 */
class ConfigureModelEncryptionCommand extends Command
{

    use CommonTraits;

    protected $commandName = 'run:encrypt-models';
    protected $commandDescription = "Run encryption for all models which are applicable";

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

        $database = $input->getArgument(name: 'db');

        if ($database == 'tenant') {
            $output->writeln('Starting to configure encryption for models');
            $this->runForEveryTenant($output);
        } else {
            $output->writeln('No need to configure');
        }

        return 0;
    }

    private function runForEveryTenant($output)
    {




        $this->setupTenantDB();

        //$currCipherSweetKey = Utility::tenantEncryptionKey($tenant['tenant_account_name']) ?? CIPHER_SWEET_KEY;
        $currCipherSweetKey =  CIPHER_SWEET_KEY;

        DB::connection('app')->beginTransaction();

        try {
            $this->updateModels($output, $currCipherSweetKey);

            DB::connection('app')->commit();
        } catch (\Exception $ex) {
            Logger::error('Cauaght Exception error  on ' . gethostname() . ' ' . $ex->getMessage(), (array) $ex->getTraceAsString());


            Db::connection('app')->rollBack();
        }


        $output->writeln('encryption successful for database ');
    }

    private function updateModels($output, $currCipherSweetKey)
    {
        $systemModels = DB::connection('app')
            ->table('encryption_models')
            ->select(['model_name', 'model_path', 'encryption_model_id'])
            ->get();

        foreach ($systemModels as $model) {

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


                $encryptModels = new EncryptModel($model->model_path, $currCipherSweetKey, $currCipherSweetKey);
                $encryptModels->handle();

                DB::connection('app')
                    ->table('encryption_model_column_details')
                    ->where('encryption_model_id', $model->encryption_model_id)
                    ->update(['is_encrypted' => 1]);


                DB::connection('app')
                    ->table('encryption_models')
                    ->where('model_path', $model->model_path)
                    ->update(['is_encrypted' => 1]);


                $output->writeln('successfully encrypted model => ' . $model->model_name);
            } else {
                $output->writeln('no column found to encrypt  model => ' . $model->model_name);
            }
        }
    }
}
