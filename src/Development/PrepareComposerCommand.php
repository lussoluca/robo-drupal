<?php

namespace LucaCracco\RoboDrupal\Development;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareComposerCommand extends BaseCommand {

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('drupalspoons:prepare');
        $this->setDescription('Prepare composer.json for DrupalSpoons.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $handler = new Handler($this->getComposer(), $this->getIO());
        $handler->configureComposerJson();
    }

}
