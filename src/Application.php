<?php

namespace Collector;

use Dotenv\Dotenv;
use Collector\Commands;
use Collector\Support\Config;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    
    /**
     * @throws \Dotenv\Exception\InvalidPathException
     */
    public function __construct()
    {
        parent::__construct('Collector', '1');

        $this->registerEnvironmentConfiguration();
    }

    /**
     * Registers the environment and configuration.
     * 
     */
    protected function registerEnvironmentConfiguration()
    {
        // Load the environment.
        (new Dotenv(__DIR__.'/../'))->load();

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