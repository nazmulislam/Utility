<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NazmulIslam\Utility\Console\Traits\CodeTemplateTraits;

class CreateDMRFilesCommand extends Command {

    use CodeTemplateTraits;

    protected $commandName = 'create:dmr-files';
    protected $commandDescription = "Create domain, model and route files";

    protected function configure() {
        $this
                ->setName($this->commandName)
                ->setDescription($this->commandDescription)
                ->setHelp('This command allows you to create domain files from template')
                ->addArgument('domain-name', InputArgument::REQUIRED, 'The domain name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $domainName = $input->getArgument('domain-name');
        $this->setReplaceText($domainName);
        $this->copyDomainTemplateFiles($domainName);
        $this->copyModelTemplateFiles($domainName);
        $this->copyRouteTemplateFiles($domainName);
        
            $output->writeln('The domain, model and route files for '.$domainName .' has been successfully created. Remeber to inlude the route file in the config file.');
     
            return 0;
    }

    
}
