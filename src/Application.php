<?php

namespace Collector;

use Dotenv\Dotenv;
use ErrorException;
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

        set_error_handler([$this, 'handleError']);

        $this->registerEnvironmentConfiguration();
    }

     /**
     * Convert a PHP error to an ErrorException.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * 
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if ($level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
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