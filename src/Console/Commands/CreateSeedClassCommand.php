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
class CreateSeedClassCommand extends Command {

    use CommonTraits;

    protected $commandName = 'create:seed-file';
    protected $commandDescription = "Create Seed file";

   
    protected function configure() {
        $this
                ->setName($this->commandName)
                ->setDescription($this->commandDescription)
                ->setHelp('This command allows you to create seed file for the model')
                ->addArgument('domain-name', InputArgument::REQUIRED, 'The domain name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $domainName = $input->getArgument('domain-name');
        $this->setReplaceText($domainName);
        $this->copySeedTemplateFiles($domainName);
        $output->writeln('Created seed File');

        return 0;
    }

}
