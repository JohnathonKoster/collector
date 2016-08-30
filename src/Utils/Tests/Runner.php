<?php

namespace Collector\Utils\Tests;

use Closure;
use Collector\Utils\File;
use Collector\Utils\Notifier;

class Runner
{
	use Notifier;

	protected $file;

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

	public function runTestsOn($output)
	{
		$this->info("Preparing to run tests on {$output}...");
		$output = $this->file->getDirectories('', $output)->output;

		$this->info("Creating a backup of the existing composer.json file...");
		$composerJsonPath = $this->file->normalizePath($output.'/composer.json');
		$vendorTestPath   = $this->file->normalizePath(realpath(__DIR__.'/../../../vendor_test'));
		$bootstrapPath    = $this->file->normalizePath(realpath(__DIR__.'/../../storage/tests/bootstrap.php'));
		$composerJson = file_get_contents($composerJsonPath);
		$newJson      = file_get_contents($this->file->normalizePath(
			__DIR__.'/../../storage/stubs/test_composer.json'
		));

		// Create the new mappings.
		$mappings = [
			'@test_vendor@' => $vendorTestPath
		];

		$newJson = strtr($newJson, $mappings);
		$this->info("Writing new the composer.json file required for testing...");
		file_put_contents($composerJsonPath, $newJson);
		

		$testCommand = sprintf(config('tests.run'), $output, $vendorTestPath, $bootstrapPath);

		$this->info("Running tests using {$testCommand}");
		$this->run($testCommand);

	}

}