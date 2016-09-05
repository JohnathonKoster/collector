<?php

namespace Collector\Utils\GitHub;

use Closure;
use Collector\Utils\File;
use Collector\Utils\Notifier;

class Publisher
{
	use Notifier;

	/**
	 * The File instance.
	 * 
	 * @var Collector\Utils\File
	 */
	protected $file;

	/**
	 * The files helper object.
	 * 
	 * @var \stdClass
	 */
	protected $files;

	public function __construct()
	{
		$this->file = new File;
	}

	/**
	 * Sets the notifiers.
	 *
	 * @param Closure  $notifier
	 */
	public function setNotifiers(Closure $notifier)
	{
		$this->setNotifier($notifier);
		$this->file->setNotifier($notifier);
	}

	/**
	 * Copies the generated output to the git repository and commit/tags it.
	 *
	 * @param  string $version
	 *
	 * @return integer
	 */
	public function publish($version)
	{
		$this->info("Preparing to publish version {$version}...");
		$source = $this->file->normalizePath($this->files = $this->file->getDirectories('', $version)->output);
		$publishDir = $this->file->normalizePath(config('split.publish'));

		if (file_exists($source) === false || is_dir($source) === false) {
			$this->error("Cannot find source for version {$version}");
			return 1;
		}

		$this->info("Resetting the files in {$publishDir}");
		$this->file->deleteDirectory($publishDir, true, ['.git']);

		$this->info("Copying source from {$source} to {$publishDir}");
		$this->file->copyDirectory($source, $publishDir);

		$gitCommand = strtr(config('git.publish'), [
			'@publishDir@' => $publishDir,
			'@version@' => $version,
		]);

		$gitUpdate = strtr(config('git.update'), [
			'@publishDir@' => $publishDir,
			'@version@' => $version,
		]);

		$this->info("Updating git repository using\n{$gitCommand}");

		return $this->run($gitCommand);
	}

	/**
	 * Attempts to update the remote git repository.
	 * 
	 */
	public function updateRepository()
	{
		$publishDir = $this->file->normalizePath(config('split.publish'));

		$gitUpdate = strtr(config('git.update'), [
			'@publishDir@' => $publishDir,
		]);

		passthru($gitUpdate);
	}

}