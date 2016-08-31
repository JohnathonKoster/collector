<?php

namespace Collector\Utils;

use SplFileInfo;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class File
{
	use Notifier;

	/**
	 * The root directory of the collector tool.
	 *
	 * @var string
	 */
	protected $collectorRoot = '';

	public function __construct()
	{
		$this->collectorRoot = __DIR__.'/../../';
	}

	/**
	 * Sets the collector root.
	 *
	 * @param string $rootDirectory
	 */
	public function setCollectorRoot($rootDirectory)
	{
		$this->collectorRoot = $rootDirectory;
	}

	/**
	 * Gets the root directory.
	 *
	 * @return string
	 */
	public function getRootDirectory()
	{
		return $this->collectorRoot;
	}

	/**
	 * Gets the path to the configured temporary directory.
	 *
	 * @param  string $version
	 *
	 * @return string
	 */
	public function getTempDirectory($version)
	{
		$path = $this->collectorRoot.config('split.source').'/'.$version;

		$this->makeDir($path);
		return $path;
	}

	public function getDirectories($remote, $destination)
	{
		$outputDirectory    = $this->getOutputDirectory($destination);
		$sourceDirectory    = $this->getTempDirectory($remote);
		$supportDirectory   = $sourceDirectory.'/src/Illuminate/Support';
		$contractsDirectory = $sourceDirectory.'/src/Illuminate/Contracts';
		$collectionPath     = $supportDirectory.'/Collection.php';
		$helpersFile        = $sourceDirectory.'/src/Illuminate/Support/helpers.php';

		return (object) array_map(function($path) {
			return realpath($path);
		}, [
			'output'        => $outputDirectory,
			'source'        => $sourceDirectory,
			'support'       => $supportDirectory,
			'contracts'     => $contractsDirectory,
			'helpers'       => $helpersFile,
			'collection'    => $collectionPath
		]);
	}

	public function normalizePath($path)
	{
		return str_replace('\\', '/', $path);
	}

	public function getOutputDirectory($branch)
	{
		$path = __DIR__.'/../../'.config('split.output').'/'.$branch;
		$this->makeDir($path);
		return realpath($path);
	}

	/**
	 * Recursively delete a directory and all of it's contents - e.g.the equivalent of `rm -r` on the command-line.
	 * Consistent with `rmdir()` and `unlink()`, an E_WARNING level error will be generated on failure.
	 * 
	 * # http://stackoverflow.com/a/3352564/283851
	 * # https://gist.github.com/XzaR90/48c6b615be12fa765898
	 * # Forked from https://gist.github.com/mindplay-dk/a4aad91f5a4f1283a5e2
	 *
	 * @param string $source absolute path to directory or file to delete.
	 * @param bool   $removeOnlyChildren set to true will only remove content inside directory.
	 *
	 * @return bool true on success; false on failure
	 */
	public function deleteDirectory($source, $removeOnlyChildren = false)
	{
	    if(empty($source) || file_exists($source) === false)
	    {
	        return false;
	    }

	    if(is_file($source) || is_link($source))
	    {
	        return unlink($source);
	    }

	    $files = new RecursiveIteratorIterator
	    (
	        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
	        RecursiveIteratorIterator::CHILD_FIRST
	    );

	    //$fileinfo as SplFileInfo
	    foreach($files as $fileinfo)
	    {
	        if($fileinfo->isDir())
	        {
	            if($this->deleteDirectory($fileinfo->getRealPath()) === false)
	            {
	                return false;
	            }
	        }
	        else
	        {
	        	// Permissions hack, we're just deleting all this stuff anyway right now.
	        	chmod($fileinfo->getRealPath(), 0777);
	            if(unlink($fileinfo->getRealPath()) === false)
	            {
	                return false;
	            }
	        }
	    }

	    if($removeOnlyChildren === false)
	    {
	        return rmdir($source);
	    }

	    return true;
	}

	public function resetDirectory($directory)
	{
		$this->deleteDirectory($directory, true);
	}

	/**
	 * Just trims the namespace from a string.
	 * 
	 * @param  string $namespace
	 * @param  string $dependency
	 * @return string
	 */
	public function getSourcePath($namespace, $dependency) {
		return mb_substr($dependency, mb_strlen($namespace));
	}

	/**
	 * Generates a mapping of source files to destination files.
	 * 
	 * @param  array  $dependencies
	 * @param  string $sourceDirectory
	 * @param  string $destinationDirectory
	 * @return array
	 */
	public function getCopyMap($dependencies, $sourceDirectory, $destinationDirectory) {

		$copyMap = [
			'contracts' => [],
			'support'   => []
		];

		$collectionPath = $sourceDirectory.'/src/Illuminate/Support';
		$contractPath   = $sourceDirectory.'/src/Illuminate/Contracts';
		$destinationDirectory = $destinationDirectory.'/src/';

		// Process the contracts.
		foreach ($dependencies['contracts'] as $contract) {
			$copyMap['contracts'][] = [
				$contractPath.$this->getSourcePath('Illuminate/Contracts', $contract),
				$destinationDirectory.$contract
			];
		}

		foreach ($dependencies['support'] as $class) {
			$copyMap['support'][] = [
				$collectionPath.$this->getSourcePath('Illuminate/Support', $class),
				$destinationDirectory.$class
			];
		}

		return $copyMap;
	}

	/**
	 * Recursively makes a directory.
	 * 
	 * @param  string $path
	 */
	public function makeDir($path) {
		$path = $this->normalizePath($path);
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
	}

	public function doClassReplacements($path) {
		$path = $this->normalizePath($path);

		if (file_exists($path)) {
			file_put_contents($path, strtr(file_get_contents($path), config('split.replace_class')));
		}
	}

	/**
	 * Copies a file from one location to another.
	 *
	 * Will create the destination directory.
	 * 
	 * @param  string $from
	 * @param  string $to
	 * @return bool
	 */
	public function copyFile($from, $to) {
		$from = $this->normalizePath($from);
		$to   = $this->normalizePath($to);

		$this->makeDir(dirname($to));
		$this->reportInfo("Copying '{$from}' to '{$to}'");

		$copyResult = copy($from, $to);

		$this->doClassReplacements($to);

		return $copyResult;
	}

	public function copyStub($stub, $to) {
		$stubPath = realpath(__DIR__.'/../storage/stubs/'.$stub);
		$stubPath = $this->normalizePath($stubPath);

		if (file_exists($stubPath)) {
			$this->copyFile($stubPath, $to.'/'.$stub);
		}
	}

	public function copyStubs(array $stubs, $to) {
		foreach ($stubs as $stub) {
			$this->copyStub($stub, $to);
		}
	}

	public function copyFiles($sourceFiles, $sourceDirectory, $destinationDirectory) {
		foreach ($sourceFiles as $file) {
			$this->copyFile(
				$sourceDirectory.'/'.$file,
				$destinationDirectory.'/'.$file
			);
		}
	}

	/**
	 * Creates a new file structure based on the copy map.
	 * 
	 * @param  array $map
	 * @return void
	 */
	public function copyMap($map) {

		// Copy the contracts over.
		foreach ($map['contracts'] as $mapping) {
			$this->copyFile($mapping[0], $mapping[1]);
		}

		// Copy over the rest!
		foreach ($map['support'] as $mapping) {
			$this->copyFile($mapping[0], $mapping[1]);
		}

	}

}