<?php

namespace Collector\Utils\Tests;

use Closure;
use Collector\Utils\File;
use Collector\Utils\Notifier;
use Symfony\Component\Process\Process;

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
		$vendorTestPath   = $this->file->normalizePath(__DIR__.'/../../../vendor_test');
		$bootstrapPath    = $this->file->normalizePath(__DIR__.'/../../../storage/tests/bootstrap.php');
		$composerJson = file_get_contents($composerJsonPath);
		$newJson      = file_get_contents($this->file->normalizePath(
			__DIR__.'/../../../storage/stubs/test_composer.json'
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
		$process = new Process($testCommand);
		$process->setTimeout(0);
		$code = $process->run();
		// $code = $this->run($testCommand);

		if ($code == 2) {
			$this->error("Tests failed for {$output}");
		} elseif ($code == 1) {
			$this->error("There appears to be problems with the test Runner paths. Check the storage paths.");
		} elseif ($code == 0) {
			$this->info("Tests passed for {$output}");
		}

		$this->info("Restoring composer.json file...");
		file_put_contents($composerJsonPath, $composerJson);
		
		return $code;		
	}

}