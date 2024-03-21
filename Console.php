<?php

declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';


if (file_exists(__DIR__ . '/../config/.env')) {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config/');
} else {

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
}


$dotenv->load();

use NazmulIslam\Utility\Console\Commands\ConfigureModelEncryptionCommand;
use Symfony\Component\Console\Application;
use NazmulIslam\Utility\Console\Commands\CreateUnitTestClassCommand;
use NazmulIslam\Utility\Console\Commands\CreateDomainFilesCommand;
use NazmulIslam\Utility\Console\Commands\CreateModelFileCommand;
use NazmulIslam\Utility\Console\Commands\CreateRouteFileCommand;
use NazmulIslam\Utility\Console\Commands\CreateDMRFilesCommand;
use NazmulIslam\Utility\Console\Commands\CreatePhinxMigrationFileCommand;
use NazmulIslam\Utility\Console\Commands\CreateRbacFileCommand;
use NazmulIslam\Utility\Console\Commands\CreateSeedClassCommand;
use NazmulIslam\Utility\Console\Commands\PhinxMigrateCommand;
use NazmulIslam\Utility\Console\Commands\PhinxSeedCommand;
use NazmulIslam\Utility\Console\Commands\PhinxCreateMigrationCommand;
use NazmulIslam\Utility\Console\Commands\UpdateEncryptionKeyCommand;
use NazmulIslam\Utility\Console\Commands\WritingEncryptionLogicCommand;


/**
 * Ensure only console use
 */
if (php_sapi_name() === 'cli') {
    $application = new Application();
    # add our commands

    $application->add(new CreateUnitTestClassCommand());
    $application->add(new CreateDomainFilesCommand());
    $application->add(new CreateModelFileCommand());
    $application->add(new CreateRouteFileCommand());
    $application->add(new CreateDMRFilesCommand());
    $application->add(new CreatePhinxMigrationFileCommand());
    $application->add(new CreateRbacFileCommand());
    $application->add(new CreateSeedClassCommand());
    $application->add(new PhinxMigrateCommand());
    $application->add(new PhinxSeedCommand());
    $application->add(new PhinxCreateMigrationCommand());
    $application->add(new WritingEncryptionLogicCommand());
    $application->add(new ConfigureModelEncryptionCommand());
    $application->add(new UpdateEncryptionKeyCommand());



    $application->run();
} else {
    echo "Nothing to do";
}
