<?php

namespace Collector;

use Collector\Commands\Tags;
use Collector\Support\Config;
use Collector\Commands\Collect;
use Collector\Commands\TestOutput;
use Collector\Commands\CollectHelpers;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{

	private static $instance;

	protected $loader;

	public function __construct()
	{
		parent::__construct('Collector', '1');

		// Initialize the config class.
		Config::getInstance();
	}

	protected function getDefaultCommands()
	{
		return array_merge(parent::getDefaultCommands(), [
				new Collect,
				new CollectHelpers,
				new Tags,
				new TestOutput,
			]);
	}

}