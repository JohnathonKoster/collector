<?php

use Collector\Utils\File;
use Collector\Support\Config;
use Collector\Utils\FilesystemVirtualization\Assertions;
use Collector\Utils\FilesystemVirtualization\FilesystemVirtualization;

class FileTest extends PHPUnit_Framework_TestCase
{
	use FilesystemVirtualization, Assertions {
		FilesystemVirtualization::getPath insteadof Assertions;
	}

	protected $file;

	protected $virtualPath = 'fst';

	public function setUp()
	{
		$this->setUpVfs();
		$this->file = new File;
		$this->file->setCollectorRoot($this->getPath());
	}

	public function tearDown()
	{
		$this->tearDownVfs();
	}

	public function testThatRootDirectoryCanBeChanged()
	{
		$this->file->setCollectorRoot('test');
		$this->assertEquals('test', $this->file->getRootDirectory());
	}

	public function testThatFileReturnsCorrectTempDirectory()
	{
		$dir = $this->file->getTempDirectory('5.3.22');
		$this->assertFileExists($dir);
	}

	public function testThatFileReturnsCorrectOutputDirectory()
	{
		$dir = $this->file->getOutputDirectory('5.3.22');
		$this->assertFileExists($dir);
	}

	public function testThatFileReturnsStandardDirectories()
	{
		$dirs = $this->file->getDirectories('5.3.22', '5.3.22');
		
		$this->assertTrue(is_object($dirs));

		$dirs = (array) $dirs;
		$this->assertArrayHasKey('output', $dirs);
		$this->assertArrayHasKey('source', $dirs);
		$this->assertArrayHasKey('support', $dirs);
		$this->assertArrayHasKey('contracts', $dirs);
		$this->assertArrayHasKey('helpers', $dirs);
		$this->assertArrayHasKey('collection', $dirs);
		$this->assertFileExists($dirs['output']);
		$this->assertFileExists($dirs['source']);
	}

	public function testFileNormalization()
	{
		$this->assertEquals('/user/home', $this->file->normalizePath('\\user\\home'));
	}

}