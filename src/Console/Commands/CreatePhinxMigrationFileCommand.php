<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NazmulIslam\Utility\Console\Traits\CodeTemplateTraits;


/**
 * //TODO the relative file paths need to be fixed for installation
 */
class CreatePhinxMigrationFileCommand extends Command {

    use CodeTemplateTraits;

    protected $commandName = 'create:phinx-file';
    protected $commandDescription = "Create phinx file";

    protected function configure() {
        $this
                ->setName($this->commandName)
                ->setDescription($this->commandDescription)
                ->setHelp('This command allows you to create phinx file')
                ->addArgument('phinx-name', InputArgument::REQUIRED, 'The phinx name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $domainName = $input->getArgument('phinx-name');
        $this->setReplaceText($domainName);
       $this->copyPhinxTemplateFiles($domainName);
            $output->writeln('Created phinx File');
     
            return 0;
    }

   
}
