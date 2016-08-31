<?php

use Collector\Support\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{

	const CONFIG_DIR = __DIR__.'/../files/sample_config';

	public function testThatConfigCantBeInstantiatedDirectly()
	{
		$reflection  = new ReflectionClass(Config::class);
		$constructor = $reflection->getConstructor();
		$this->assertFalse($constructor->isPublic());
	}

	public function testThatConfigReturnsSameInstance()
	{
		$instanceOne = Config::getInstance(self::CONFIG_DIR);
		$instanceTwo = Config::getInstance();

		$this->assertSame($instanceOne, $instanceTwo);
	}

	public function testThatConfigReturnsCorrectValue()
	{
		$config = Config::getInstance(self::CONFIG_DIR);
		$this->assertEquals($config->get('test.test'), 'value');
		$this->assertEquals($config->get('example.another'), 'key');
	}

	public function testThatConfigHelperReturnsSameValues()
	{
		$config = Config::getInstance(self::CONFIG_DIR);
		$this->assertEquals($config->get('test.test'), config('test.test'));
	}

}