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
class CreateRouteFileCommand extends Command {

    use CodeTemplateTraits;

    protected $commandName = 'create:route-file';
    protected $commandDescription = "Create the route file";

    protected function configure() {
        $this
                ->setName($this->commandName)
                ->setDescription($this->commandDescription)
                ->setHelp('This command allows you to create route file')
                ->addArgument('route-name', InputArgument::REQUIRED, 'The route name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $domainName = $input->getArgument('route-name');
        $this->setReplaceText($domainName);
       $this->copyRouteTemplateFiles($domainName);
            $output->writeln('Created Route File');
     
            return 0;
    }

   
}
