<?php

namespace Huangdijia\ComposerPackageBuilder;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

class CommandProvider implements CommandProviderCapability
{
    /**
     * Retrieves an array of commands
     *
     * @return \Composer\Command\BaseCommand[]
     */
    public function getCommands(): array
    {
        return [
            new Commands\BuildCommand('build:php-package'),
            new Commands\BuildCommand('build:laravel-package'),
            new Commands\BuildCommand('build:thinkphp-package'),
            new Commands\BuildCommand('build:composer-plugin'),
        ];
    }
}
