<?php

namespace Collector\Utils\Tests;

use Closure;
use Collector\Utils\File;
use Collector\Utils\Notifier;

class Runner
{
	use Notifier;

	/**
	 * The File instance.
	 * 
	 * @var Collector\Utils\File
	 */
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

	/**
	 * Runs the PHPUnit test command on target output directory.
	 * 
	 * @param  string $output
	 * @return int
	 */
	public function runTestsOn($output)
	{
		$this->info("Preparing to run tests on {$output}...");
		$outputDirectory = $this->file->getDirectories('', $output)->output;

		$this->info("Creating a backup of the existing composer.json file...");
		$composerJsonPath = $this->file->normalizePath($outputDirectory.'/composer.json');
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
		

		$testCommand = strtr(config('tests.run'), [
			'@bootstrap@' => $bootstrapPath,
			'@outputDir@' => $outputDirectory,
			'@vendor@'    => $vendorTestPath,
			'@version@'   => $output
		]);

		$this->info("Running tests using {$testCommand}");
		
		$code = $this->run($testCommand);

		if ($code == 2) {
			$this->error("Tests failed for {$outputDirectory}");
		} elseif ($code == 1) {
			$this->error("There appears to be problems with the test Runner paths. Check the storage paths.");
		} elseif ($code == 0) {
			$this->info("Tests passed for {$outputDirectory}");
		}

		$this->info("Restoring composer.json file...");
		file_put_contents($composerJsonPath, $composerJson);
		
		return $code;		
	}

}