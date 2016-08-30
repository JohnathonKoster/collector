<?php

namespace Collector\Support;

use DirectoryIterator;

class Config
{

	private static $instance;

	protected $configurationValues = [];

	protected function __construct($path)
	{
		foreach (new DirectoryIterator($path) as $fileInfo) {
			if (!$fileInfo->isDot() && $fileInfo->isFile()) {
				$path = $fileInfo->getPathname();
				$this->configurationValues[$fileInfo->getBasename('.'.$fileInfo->getExtension())] = require_once $path;
			}
		}
	}

	private function __clone()
	{

	}

	/**
	 * Gets an instance of Config.
	 *
	 * @return Config
	 */
	public static function getInstance($path = null)
	{
		if (static::$instance === null) {
			static::$instance = new static($path);
		}

		return static::$instance;
	}

	/**
	 * Super simple configuration stuff.
	 *
	 * @param  string
	 * @return mixed
	 */
	public function get($key)
	{
		$parts = explode('.', $key);

		return $this->configurationValues[$parts[0]][$parts[1]];
	}

}