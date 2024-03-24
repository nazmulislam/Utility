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
class CreateUnitTestClassCommand extends Command {

    use CodeTemplateTraits;

    protected $commandName = 'create:unit-test-class';
    protected $commandDescription = "Create Unit Test File";

   
    protected function configure() {
        $this
                ->setName($this->commandName)
                ->setDescription($this->commandDescription)
                ->setHelp('This command allows you to create unit test files')
                ->addArgument('domain-name', InputArgument::REQUIRED, 'The domain name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $domainName = $input->getArgument('domain-name');
        $this->setReplaceText($domainName);
        $this->copyUnitTestTemplateFiles($domainName);
        $output->writeln('Created Unit Tests File');

        return 0;
    }

}
