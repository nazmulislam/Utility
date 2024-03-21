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
class CreateRbacFileCommand extends Command {

    use CommonTraits;

    protected $commandName = 'create:rbac-file';
    protected $commandDescription = "Create rbac file";

    protected function configure() {
        $this
                ->setName($this->commandName)
                ->setDescription($this->commandDescription)
                ->setHelp('This command allows you to create rbac file')
                ->addArgument('module-name', InputArgument::REQUIRED, 'The RBAC module name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $domainName = $input->getArgument('module-name');
        $this->setReplaceText($domainName);
       $this->copyRbacModuleTemplateFiles($domainName);
            $output->writeln('Created rbac module File');
     
            return 0;
    }

   
}
