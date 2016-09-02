<?php

namespace Collector\Utils;

use SplFileInfo;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RecursiveCallbackFilterIterator;

class File
{
	use Notifier;

	const REGEX_REPLACE_MULTIPLE_FORWARD_SLASH = '/([^:])(\/{2,})/';

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
		$path = config('split.source').'/'.$version;
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
		$path = config('split.output').'/'.$version;
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
		$path  = str_replace('\\', '/', $path);
		$parts = preg_split('^://^', $path, 2);

		if (count($parts) > 1) {
			return $parts[0].'://'.preg_replace(self::REGEX_REPLACE_MULTIPLE_FORWARD_SLASH, '$1/', $parts[1]);
		}

		return preg_replace(self::REGEX_REPLACE_MULTIPLE_FORWARD_SLASH, '$1/', $path);
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

	    $files = new RecursiveIteratorIterator(
		  new RecursiveCallbackFilterIterator(
		    new RecursiveDirectoryIterator(
		      $source,
		      RecursiveDirectoryIterator::SKIP_DOTS
		    ),
		    function ($fileInfo, $key, $iterator) {
		      return $fileInfo->isFile() || !in_array($fileInfo->getBaseName(), ['.git']);
		    }
		  )
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
	 * Recursively copy a directory.
	 * 
	 * @param  string  $source
	 * @param  string  $destination
	 * @param  integer $permissions
	 * 
	 * @return boolean
	 */
	public function copyDirectory($source, $destination, $permissions = 0755)
	{
	    // Check for symlinks
	    if (is_link($source)) {
	        return symlink(readlink($source), $destination);
	    }

	    // Simple copy for a file
	    if (is_file($source)) {
	        return copy($source, $destination);
	    }

	    // Make destination directory
	    if (!is_dir($destination)) {
	        mkdir($destination, $permissions);
	    }

	    // Loop through the folder
	    $dir = dir($source);
	    while (false !== $entry = $dir->read()) {
	        // Skip pointers
	        if ($entry == '.' || $entry == '..') {
	            continue;
	        }

	        // Deep copy directories
	        $this->copyDirectory("$source/$entry", "$destination/$entry", $permissions);
	    }

	    // Clean up
	    $dir->close();
	    return true;
	}

	/**
	 * Recursively makes a directory.
	 * 
	 * @param  string $path
	 */
	public function makeDir($path) {
		$path = $this->normalizePath($path);
		if (! file_exists($path)) {
			mkdir($path, 0777, true);
		}
	}

	/**
	 * Performs class replacements on a file.
	 *
	 * @param  string $path
	 * @param  array  $replacements
	 * 
	 * @return string
	 */
	public function doClassReplacements($path, array $replacements = []) {

		$path = $this->normalizePath($path);

		if (count($replacements) == 0) {
			$replacements = config('split.replace_class');
		}

		$contents = strtr(file_get_contents($path), $replacements);

		file_put_contents($path, $contents);

		return $contents;
	}

	/**
	 * Copies a file from one location to another.
	 *
	 * Will create the destination directory and perform class replacements.
	 * 
	 * @param  string $from
	 * @param  string $to
	 * 
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

	/**
	 * Copies the source files from the source directory to the destination directory.
	 *
	 * @param  array  $sourceFiles
	 * @param  string $sourceDirectory
	 * @param  string $destinationDirectory
	 */
	public function copyFiles(array $sourceFiles, $sourceDirectory, $destinationDirectory) {
		foreach ($sourceFiles as $file) {
			$this->copyFile(
				$sourceDirectory.'/'.$file,
				$destinationDirectory.'/'.$file
			);
		}
	}

	/**
	 * Copies a stub to the desired path.
	 * 
	 * @param  string $stub
	 * @param  string $to
	 */
	public function copyStub($stub, $to) {
		$stubPath    = $this->collectorRoot.'/storage/stubs/'.$stub;
		$stubPath    = $this->normalizePath($stubPath);
		$destination = $this->normalizePath($to.'/'.$stub);

		// Only copy the stub if it actually exists.
		if (file_exists($stubPath)) {
			$this->copyFile($stubPath, $to.'/'.$stub);
		}
	}

	/**
	 * Copies multiple stubs to the destination directory.
	 * 
	 * @param  array  $stubs
	 * @param  string $to
	 */
	public function copyStubs(array $stubs, $to) {
		foreach ($stubs as $stub) {
			$this->copyStub($stub, $to);
		}
	}

}