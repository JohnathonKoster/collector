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
	 * A File instance.
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

	protected $testFsDirectory;

	public function setUp()
	{
		$this->setUpVfs();
		$this->file = new File;
		$this->file->setCollectorRoot($this->getPath());

		$this->testFsDirectory = __DIR__.'/../files/fs/';

		if (file_exists($this->testFsDirectory)) {
			$this->file->deleteDirectory($this->testFsDirectory, true);
		} else {
			$this->file->makeDir($this->testFsDirectory);
		}

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
		$this->assertEquals('vst://user/home/', $this->file->normalizePath('vst:\\\\user\\\\\\home\\\\'));
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

	public function testThatFileCanCopyAFile()
	{
		$from = $this->getPath('from.php');
		$to   = $this->getPath('to/some/nested/destination.php');
		file_put_contents($from, $this->getFile('ClassReplacement'));

		$this->file->copyFile($from, $to);
		$this->assertFileExists($to);
	}

	public function testThatStubsCanBeCopied()
	{
		// Define some paths.
		$localCodePath          = $this->getCodePath().'/DefinedFunctions.php';
		$virtualStubPath        = $this->getPath('storage/stubs/DefinedFunctions.php');
		$virtualDestination     = $this->getPath('virtual/destination/');
		$virutalDestinationStub = $this->getPath('virtual/destination/DefinedFunctions.php');

		// Copy the stub path to the virtual file system.
		$this->file->copyFile($localCodePath, $virtualStubPath);
		$this->file->copyStub('DefinedFunctions.php', $virtualDestination);

		$this->assertFileExists($virutalDestinationStub);
		$this->assertSame(
			normalize_line_endings(file_get_contents($virtualStubPath)),
			normalize_line_endings(file_get_contents($virutalDestinationStub))
		);
	}

	public function testThatFileWillCopyMultipleFiles()
	{
		$testFilesLocation  = $this->getCodePath().'/src/Illuminate/Contracts/Support/';
		$virutalDestination = $this->getPath('virtual/destination/');

		$this->file->copyFiles([
			'Arrayable.php',
			'Jsonable.php'
		], $testFilesLocation, $virutalDestination);

		$this->assertFileExists($this->getPath('virtual/destination/Arrayable.php'));
		$this->assertFileExists($this->getPath('virtual/destination/Jsonable.php'));
	}

	public function testThatFileCopyMultipleStubs()
	{
		$localCodePath             = $this->getCodePath().'/ClassName.php';
		$localCodePathTwo          = $this->getCodePath().'/DefinedFunctions.php';
		$virtualStubPath           = $this->getPath('storage/stubs/ClassName.php');
		$virtualStubPathTwo        = $this->getPath('storage/stubs/DefinedFunctions.php');
		$virtualDestination        = $this->getPath('virtual/destination/');
		$virutalDestinationStub    = $this->getPath('virtual/destination/ClassName.php');
		$virutalDestinationStubTwo = $this->getPath('virtual/destination/DefinedFunctions.php');


		$this->file->copyFile($localCodePath, $virtualStubPath);	
		$this->file->copyFile($localCodePath, $virtualStubPathTwo);

		$this->file->copyStubs(['ClassName.php', 'DefinedFunctions.php'], $virtualDestination);

		$this->assertFileExists($virutalDestinationStub);
		$this->assertFileExists($virutalDestinationStubTwo);
	}

	public function testThatDirectoriesCanBeCopiedRecursively()
	{
		$testFolder = __DIR__.'/../files/code/src/';

		$this->file->copyDirectory($testFolder, $this->getPath('src'));

		$structure = [
			'Illuminate/Contracts/Support/Arrayable.php',
			'Illuminate/Contracts/Support/Jsonable.php',
			'Illuminate/Support/Traits/Macroable.php',
			'Illuminate/Support/Collection.php',
			'Illuminate/Support/helpers.php',
			'Illuminate/Contracts/Support',
			'Illuminate/Support/Arr.php',
			'Illuminate/Support/Traits',
			'Illuminate/Contracts',
			'Illuminate/Support',
			'Illuminate',
		];

		$this->assertFileExists($this->getPath('src'));

		foreach ($structure as $path) {
			$virtualPath = $this->getPath('src/'.$path);
			$originPath  = $testFolder.$path;
			$this->assertFileExists($virtualPath);

			if (is_file($originPath)) {
				$virtualContents = normalize_line_endings(file_get_contents($virtualPath));
				$originContents  = normalize_line_endings(file_get_contents($originPath));

				$this->assertSame($originContents, $virtualContents);
			}
		}

	}

	public function testThatFileCanRecursivelyDeleteADirectory()
	{
		$sourceFolder = __DIR__.'/../files/code/src/';
		$this->file->copyDirectory($sourceFolder, $this->testFsDirectory.'/src/');

		$structure = [
			'Illuminate/Contracts/Support/Arrayable.php',
			'Illuminate/Contracts/Support/Jsonable.php',
			'Illuminate/Support/Traits/Macroable.php',
			'Illuminate/Support/Collection.php',
			'Illuminate/Support/helpers.php',
			'Illuminate/Contracts/Support',
			'Illuminate/Support/Arr.php',
			'Illuminate/Support/Traits',
			'Illuminate/Contracts',
			'Illuminate/Support',
			'Illuminate',
		];

		$this->file->deleteDirectory($this->testFsDirectory.'/src/');

		foreach ($structure as $path) {
			$this->assertFileNotExists($this->testFsDirectory.'/src/'.$path);
		}

		$this->assertFileNotExists($this->testFsDirectory.'/src/');
	}

	public function testThatFileCanRecursivelyDeleteADirectoryButKeepParent()
	{
		$sourceFolder = __DIR__.'/../files/code/src/';
		$this->file->copyDirectory($sourceFolder, $this->testFsDirectory.'/src/');

		$structure = [
			'Illuminate/Contracts/Support/Arrayable.php',
			'Illuminate/Contracts/Support/Jsonable.php',
			'Illuminate/Support/Traits/Macroable.php',
			'Illuminate/Support/Collection.php',
			'Illuminate/Support/helpers.php',
			'Illuminate/Contracts/Support',
			'Illuminate/Support/Arr.php',
			'Illuminate/Support/Traits',
			'Illuminate/Contracts',
			'Illuminate/Support',
			'Illuminate',
		];

		$this->file->deleteDirectory($this->testFsDirectory.'/src/', true);

		foreach ($structure as $path) {
			$this->assertFileNotExists($this->testFsDirectory.'/src/'.$path);
		}

		$this->assertFileExists($this->testFsDirectory.'/src/');
	}

	public function testThatFileCanRecursivelyDeleteADirectoryAndExcludeFolders()
	{
		$sourceFolder = __DIR__.'/../files/code/src/';
		$this->file->copyDirectory($sourceFolder, $this->testFsDirectory.'/src/');

		$structure = [
			'Illuminate/Contracts/Support/Arrayable.php',
			'Illuminate/Contracts/Support/Jsonable.php',
			'Illuminate/Support/Traits/Macroable.php',
			'Illuminate/Support/Collection.php',
			'Illuminate/Support/helpers.php',
			'Illuminate/Contracts/Support',
			'Illuminate/Support/Arr.php',
			'Illuminate/Support/Traits',
			'Illuminate/Contracts',
			'Illuminate/Support',
			'Illuminate',
		];

		$this->file->makeDir($this->testFsDirectory.'/src/.git');

		$this->file->deleteDirectory($this->testFsDirectory.'/src/', true, ['.git']);

		foreach ($structure as $path) {
			$this->assertFileNotExists($this->testFsDirectory.'/src/'.$path);
		}

		$this->assertFileExists($this->testFsDirectory.'/src/');
		$this->assertFileExists($this->testFsDirectory.'/src/.git');
	}

	public function testThatFileCanRecursivelyDeleteADirectoryAndExcludeFoldersWithImplicitRemoveOnlyChildren()
	{
		$sourceFolder = __DIR__.'/../files/code/src/';
		$this->file->copyDirectory($sourceFolder, $this->testFsDirectory.'/src/');

		$structure = [
			'Illuminate/Contracts/Support/Arrayable.php',
			'Illuminate/Contracts/Support/Jsonable.php',
			'Illuminate/Support/Traits/Macroable.php',
			'Illuminate/Support/Collection.php',
			'Illuminate/Support/helpers.php',
			'Illuminate/Contracts/Support',
			'Illuminate/Support/Arr.php',
			'Illuminate/Support/Traits',
			'Illuminate/Contracts',
			'Illuminate/Support',
			'Illuminate',
		];

		$this->file->makeDir($this->testFsDirectory.'/src/.git');

		$this->file->deleteDirectory($this->testFsDirectory.'/src/', false, ['.git']);

		foreach ($structure as $path) {
			$this->assertFileNotExists($this->testFsDirectory.'/src/'.$path);
		}

		$this->assertFileExists($this->testFsDirectory.'/src/');
		$this->assertFileExists($this->testFsDirectory.'/src/.git');
	}

}