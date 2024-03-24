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
class CreateDomainFilesCommand extends Command {

    use CodeTemplateTraits;

    protected $commandName = 'create:domain-files';
    protected $commandDescription = "Create domain files";

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
            $output->writeln('Created domain files');
     
            return 0;
    }

    
}
