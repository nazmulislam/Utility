<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NazmulIslam\Utility\Console\Traits\CommonTraits;

/**
 * //TODO the relative file paths need to be fixed for installation
 */
class PhinxMigrateCommand extends Command
{

    use CommonTraits;

    protected $commandName = 'run:phinx-migrate';
    protected $commandDescription = "Run Phinx migrate for all tenants";

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription)
            ->setHelp('This command allows you to run phinx migrate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $this->migrate($input, $output);

        $output->writeln('Migration complete');

        return 0;
    }

    protected function migrate(InputInterface $input, OutputInterface $output)
    {

        $loutput = null;
        $retval = null;

        exec(PHINX_CLI_PATH . ' migrate', $loutput, $retval);
        echo "<pre>", print_r($loutput);
        $output->writeln('migrated DB');
        $output->writeln('Migration complete');
    }
}
