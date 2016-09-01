<?php

namespace Collector;

use Collector\Commands;
use Collector\Support\Config;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    
    public function __construct()
    {
        parent::__construct('Collector', '1');

        // Initialize the config class.
        Config::getInstance(__DIR__.'/../config');
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array Symfony\Component\Console\Command
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), [
                new Commands\Collect,
                new Commands\Tags,
                new Commands\TestOutput,
            ]);
    }

}