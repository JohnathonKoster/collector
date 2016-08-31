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

	/**
	 * A File instnace.
	 * 
	 * @var File
	 */
	protected $file;

	/**
	 * The virtual path.
	 *
	 * @var string
	 */
	protected $virtualPath = 'fst';

	public function setUp()
	{
		$this->setUpVfs();
		$this->file = new File;
		$this->file->setCollectorRoot($this->getPath());
	}

	protected function getCodePath()
	{
		return realpath(__DIR__.'/../files/code').'/';
	}

	protected function getFile($sourceFile)
	{
		return normalize_line_endings(file_get_contents($this->getCodePath().$sourceFile.'.php'));
	}

	protected function getExpected($sourceFile)
	{
		return normalize_line_endings(file_get_contents(__DIR__.'/../files/expected_code/'.$sourceFile.'.php'));
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

	public function testThatMakeDirMakesDir()
	{
		$dirPath = $this->getPath('nested/directory/structure');
		$this->file->makeDir($dirPath);
		$this->assertTrue(file_exists($dirPath));
		$this->assertTrue(is_dir($dirPath));
	}

	public function testThatClassReplacementsCanReplaceFromConfig()
	{
		$path = $this->getPath('test.php');
		file_put_contents($path, $this->getFile('ClassReplacement'));
		$contents = $this->file->doClassReplacements($path);
		$this->assertEquals($contents, file_get_contents($path));

		$stuff = include $path;
		$this->assertEquals('Illuminate\Support\Collection', $stuff['Illuminate\Support\Collection']);
		$this->assertEquals('Illuminate\Database\Eloquent\Collection', $stuff['Illuminate\Database\Eloquent\Collection']);
	}

	public function testThatClassReplacementsCanBeOverriden()
	{
		$path = $this->getPath('test.php');
		file_put_contents($path, $this->getFile('ClassReplacement'));
		$contents = $this->file->doClassReplacements($path, [
			'Illuminate\Database\Eloquent\Collection' => 'overriden_first',
        	'Test\Class\Name' => 'overriden_second',
		]);
		$this->assertEquals($contents, file_get_contents($path));

		$stuff = include $path;
		$this->assertEquals('overriden_first',  $stuff['overriden_first']);
		$this->assertEquals('overriden_second', $stuff['overriden_second']);
	}

}