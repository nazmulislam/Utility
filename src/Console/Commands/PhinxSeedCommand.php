<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use NazmulIslam\Utility\Console\Traits\CodeTemplateTraits;


/**
 * //TODO the relative file paths need to be fixed for installation
 */
class PhinxSeedCommand extends Command
{

    use CodeTemplateTraits;

    protected $commandName = 'run:phinx-seed';
    protected $commandDescription = "Run Phinx seed for a tenant";

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription)
            ->setHelp('This command allows you to run phinx seed for a tenant')
            ->addOption(name: 'seed', shortcut: 's', mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->executeSeed($input, $output);
        $output->writeln('Seed completed');

        return 0;
    }

    function executeSeed(InputInterface $input, OutputInterface $output)
    {

        $options = $input->getOptions();
        $loutput = null;
        $retval = null;
        $loutput = null;
        $retval = null;

            if (isset($options['seed']) && count($options) > 0 && is_array($options)) {

                foreach ($options['seed'] as $option) 
                {
                    exec(PHINX_CLI_PATH . ' seed:run -s ' . $option, $loutput, $retval);
                    echo "<pre>", print_r($loutput);
                    $output->writeln('Seed ' . $option .  ' has successfully completed');
                }
                $output->writeln('Seed for tenant has successfully completed');
            } else {
                exec(PHINX_CLI_PATH . ' seed:run', $loutput, $retval);
                echo "<pre>", print_r($loutput);
                $output->writeln('Seed for tenant : has successfully completed');
            }
    }

}
