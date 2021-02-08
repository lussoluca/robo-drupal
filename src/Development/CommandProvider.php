<?php

namespace LucaCracco\RoboDrupal\Development;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Command\BaseCommand;

class CommandProvider implements CommandProviderCapability {

    /**
     * Retrieves an array of commands
     *
     * @return BaseCommand[]
     */
    public function getCommands(): array {
        return [
            new PrepareComposerCommand(),
            new PrepareProjectCommand(),
        ];
    }
}
