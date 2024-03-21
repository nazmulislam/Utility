<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Console\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use NazmulIslam\Utility\Console\Traits\CommonTraits;

/**
 * //TODO the relative file paths need to be fixed for installation
 */
class PhinxCreateMigrationCommand extends Command
{

    use CommonTraits;

    protected $commandName = 'run:phinx-create';
    protected $commandDescription = "Run Phinx create";

    protected function configure()
    {


        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription)
            ->setHelp('This command allows you to run phinx migration file')
            ->addArgument('migration_name', InputArgument::REQUIRED, 'database_type option is required');;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $migrationName = $input->getArgument(name: 'migration_name');



        $loutput = null;
        $retval = null;

        exec(PHINX_CLI_PATH . ' create ' . $migrationName, $loutput, $retval);



        $output->writeln('Create Migration completed');

        return 0;
    }
}
