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
class CreateModelFileCommand extends Command {

    use CodeTemplateTraits;

    protected $commandName = 'create:model-file';
    protected $commandDescription = "Create the model file";
  
   

    protected function configure() {
        $this
                ->setName($this->commandName)
                ->setDescription($this->commandDescription)
                ->setHelp('This command allows you to create model class')
                ->addArgument('model-name', InputArgument::REQUIRED, 'The domain name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $domainName = $input->getArgument('model-name');
        $this->setReplaceText($domainName);
        $this->copyModelTemplateFiles($domainName);
            $output->writeln('Created model file');
     
            return 0;
    }

    
}
