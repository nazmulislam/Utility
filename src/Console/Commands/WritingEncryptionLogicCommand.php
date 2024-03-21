<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Console\Commands;

use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use NazmulIslam\Utility\Console\Traits\CommonTraits;

use NazmulIslam\Utility\Core\CipherSweetEncryption\WriteModelEncryptionLogic;
use NazmulIslam\Utility\Jobs\SpecificModelEncryptionJob;
use NazmulIslam\Utility\Logger\Logger;

/**
 * //TODO the relative file paths need to be fixed for installation
 */
class WritingEncryptionLogicCommand extends Command
{

    use CommonTraits;

    protected $commandName = 'write:encryption-logic';
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

        $currCipherSweetKey = CIPHER_SWEET_KEY;

     
        $database = $input->getArgument(name: 'db');

        if ($database == 'tenant') {
            $output->writeln('Starting to configure encryption for models');
            $this->runForEveryTenant($output, $currCipherSweetKey);

            // Execute the composer dump-autoload command
            // exec('php Console.php run:encrypt-models tenant');
        } else {
            $output->writeln('No need to configure');
        }


        return 0;
    }

    private function runForEveryTenant($output, $currentKey)
    {

        
        // foreach ($tenants->toArray() as $tenant) {

        //     putenv(sprintf('%s=%s', 'DB_NAME', $tenant['tenant_db_name']));

        //     $this->setupTenantDB();
        //     $this->updateModels($output, $currentKey);

        //     $output->writeln('encryption written for database => ' . $tenant['tenant_db_name']);
        // }
    }

    private function updateModels($output, $currentKey)
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

                try {

                    $writeInModels = new WriteModelEncryptionLogic($model->model_path, $commonColumns);
                    $writeInModels->handle();



                    // $encryptModels = new EncryptModel($model->model_path, $currentKey, $currentKey);
                    // $encryptModels->handle();

                    // call model encryption job
                    // $encryptionJob = new SpecificModelEncryptionJob($model->model_path, $commonColumns);
                    // $encryptionJob->handle();

                    $output->writeln('successfully wrote encryption logic for model => ' . $model->model_name);
                } catch (\Exception $ex) {

                    Logger::error('Cauaght Exception error  on ' . gethostname() . ' ' . $ex->getMessage(), (array) $ex->getTraceAsString());
                    \Resque_Event::trigger('onFailure', [$ex->getMessage(), (array) $ex->getTraceAsString()]);

                    $output->writeln('error while writing encryption for model => ' . $model->model_name);
                }
            } else {
                $output->writeln('no column found to write encryption for model => ' . $model->model_name);
            }
        }
    }
}
