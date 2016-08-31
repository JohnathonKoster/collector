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

	/**
	 * Gets the output directory.
	 *
	 * @param  string $version
	 *
	 * @return string
	 */
	public function getOutputDirectory($version)
	{
		$path = $this->collectorRoot.config('split.output').'/'.$version;
		$this->makeDir($path);

		return $path;
	}

	/**
	 * Gets the standard directories used by most split versions.
	 *
	 * @param      <type>  $remote       The remote
	 * @param      <type>  $destination  The destination
	 *
	 * @return     <type>  The directories.
	 */
	public function getDirectories($remote, $destination)
	{
		$outputDirectory    = $this->getOutputDirectory($destination);
		$sourceDirectory    = $this->getTempDirectory($remote);
		$supportDirectory   = $sourceDirectory.'/src/Illuminate/Support';
		$contractsDirectory = $sourceDirectory.'/src/Illuminate/Contracts';
		$collectionPath     = $supportDirectory.'/Collection.php';
		$helpersFile        = $sourceDirectory.'/src/Illuminate/Support/helpers.php';

		return (object) [
			'output'        => $outputDirectory,
			'source'        => $sourceDirectory,
			'support'       => $supportDirectory,
			'contracts'     => $contractsDirectory,
			'helpers'       => $helpersFile,
			'collection'    => $collectionPath
		];
	}

	/**
	 * Normalizes the path name.
	 *
	 * @param  string $path
	 *
	 * @return string
	 */
	public function normalizePath($path)
	{
		return str_replace('\\', '/', $path);
	}

	/**
	 * Recursively deletes a directory and all it's contents.
	 * 
	 * # http://stackoverflow.com/a/3352564/283851
	 * # https://gist.github.com/XzaR90/48c6b615be12fa765898
	 * # Forked from https://gist.github.com/mindplay-dk/a4aad91f5a4f1283a5e2
	 *
	 * @param string $source
	 * @param bool   $removeOnlyChildren
	 *
	 * @return bool
	 */
	public function deleteDirectory($source, $removeOnlyChildren = false)
	{
	    if (empty($source) || file_exists($source) === false) {
	        return false;
	    }

	    if (is_file($source) || is_link($source)) {
	        return unlink($source);
	    }

	    $files = new RecursiveIteratorIterator (
	        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
	        RecursiveIteratorIterator::CHILD_FIRST
	    );

	    //$fileinfo as SplFileInfo
	    foreach ($files as $fileinfo) {
	        if ($fileinfo->isDir()) {
	            if ($this->deleteDirectory($fileinfo->getRealPath()) === false) {
	                return false;
	            }
	        } else {
	        	// Permissions hack, we're just deleting all this stuff anyway right now.
	        	chmod($fileinfo->getRealPath(), 0777);
	            if (unlink($fileinfo->getRealPath()) === false) {
	                return false;
	            }
	        }
	    }

	    if ($removeOnlyChildren === false) {
	        return rmdir($source);
	    }

	    return true;
	}

	/**
	 * Recursively deletes a directory.
	 *
	 * @param string $directory
	 */
	public function resetDirectory($directory)
	{
		$this->deleteDirectory($directory, true);
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