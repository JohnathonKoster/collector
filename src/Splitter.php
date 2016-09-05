<?php

namespace Collector;

use Closure;
use ErrorException;
use Collector\Utils\File;
use Collector\Utils\Notifier;
use Collector\Utils\Tests\Runner;
use Collector\Utils\GitHub\Publisher;
use Collector\Utils\Analyzer\Analyzer;
use Collector\Utils\VersionHistoryManager;
use Collector\Utils\Helpers\Collector as HelperCollector;
use Collector\Utils\Dependency\Collector as DependencyCollector;

class Splitter
{
	use Notifier;

	protected $file;

	protected $analyzer;

	protected $helperCollector;

	protected $dependencyCollector;

	protected $skipGitOperations = false;

	protected $onlyNewGitBranches = false;

	protected $paths;

	protected $history;

	protected $testRunner;

	protected $publisher;

	protected $forceSplit = false;

	protected $maxRetries = 2;

	protected $currentTry = 1;

	/**
	 * The required files for all splits.
	 *
	 * @var array
	 */
	protected $requiredFiles = [
		'/tests/Support/SupportCollectionTest.php',
		'.gitignore'
	];

	public function __construct()
	{
		$this->file                = new File;
		$this->analyzer            = new Analyzer;
		$this->helperCollector     = new HelperCollector;
		$this->dependencyCollector = new DependencyCollector;
		$this->history             = new VersionHistoryManager;
		$this->testRunner          = new Runner;
		$this->publisher           = new Publisher;
		$this->history->load(__DIR__.'/../storage/cache/tags/split.json');
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
		$this->publisher->setNotifiers($notifier);
		$this->helperCollector->setNotifiers($notifier);
		$this->dependencyCollector->setNotifiers($notifier);
		$this->testRunner->setNotifiers($notifier);
	}

	/**
	 * Guesses if there is a repository at the given path.
	 *
	 * @param  string  $path
	 *
	 * @return bool
	 */
	protected function checkIfRepoExists($path)
	{

		$composer = $this->file->normalizePath($path.'/composer.json');
		$testDir  = $this->file->normalizePath($path.'/tests');
		$srcDir   = $this->file->normalizePath($path.'/src');

		return (
			(file_exists($testDir) && is_dir($testDir)) &&
			(file_exists($srcDir)  && is_dir($srcDir))  &&
			(file_exists($composer))
		);
	}

	/**
	 * Attempts to guess if the current version has already been split.
	 *
	 * @return bool
	 */
	protected function alreadySplit()
	{
		if ($this->forceSplit) {
			return false;
		}

		return $this->checkIfRepoExists($this->paths->output);
	}

	/**
	 * Attempts to guess if a copy of the remote repository already exists.
	 *
	 * @return bool
	 */
	protected function localRepositoryExists()
	{
		return $this->checkIfRepoExists($this->paths->source);
	}

	public function split()
	{
		$args = func_get_args();
		if (count($args) == 2) {
			return $this->doSplit($args[0], $args[1]);
		} elseif (count($args) == 1 && is_array($args[0])) {
			foreach ($args[0] as $remote => $destination) {
				$this->doSplit($remote, $destination);
			}
		}

	}

	/**
	 * Performs the git clone operation.
	 * 
	 * @param string $remote
	 */
	protected function doGitOperation($remote)
	{
		// For now, we will just completely remove the source directory
		// and create it again. Later, we can probably optimize this
		// to just perform a git rebase or pull, but it works now.
		$this->file->deleteDirectory($this->paths->source);

		// Create the git command.
		$gitOperation = strtr(config('git.clone'), [
			'@version@' => $remote,
			'@source@'  => $this->paths->source
		]);

		$this->info("Cloning using '{$gitOperation}'");
		$this->run($gitOperation);
	}

	/**
	 * Orchestrates the actual split operation.
	 *
	 * @param string $remote
	 * @param string $destination
	 */
	protected function doSplit($remote, $destination)
	{
		$this->info("\nStarting to split {$remote} to {$destination}...");

		// Get the paths that will be needed to finish the split.
		$this->paths = $this->file->getDirectories($remote, $destination);
		
		$alreadySplit = $this->alreadySplit();

		// Perform the git clone operations if we aren't skipping it.
		if (!$this->skipGitOperations) {
			if ($this->onlyNewGitBranches && !$alreadySplit && !$this->localRepositoryExists() || !$this->onlyNewGitBranches) {
				$this->doGitOperation($remote);
			} else {
				$this->line("Skipping git operations for {$remote}. Local copy already exists.");
			}
		} else {
			$this->line("Skipping git operations for {$remote}. git operations disabled.");
		}

		if (!$alreadySplit) {

			try {
				// Copy the files that will be common to all splits.
				$this->report('Copying required files to output...');
				$this->file->copyFiles($this->requiredFiles, $this->paths->source, $this->paths->output);
				$this->report('Copying required stubs to output...');
				$this->file->copyStubs(config('split.stubs'), $this->paths->output);
			} catch (ErrorException $e) {
				// The most likely reason that we have entered into this catch
				// block is that a split process was forcefully terminated;
				// the git repository does not contain a required file.
				if ($this->currentTry <= $this->maxRetries) {
					$this->doGitOperation($remote);
					$this->currentTry++;
					$this->doSplit($remote, $destination);
					return;
				} else {
					// Just give up after the max retries has been hit.
					throw $e;
				}
			}

			// This will analyze all of the classes that are specified in the
			// split.classes configuration entry, get any dependencies and
			// copy the file and directory structures to the output dir
			$this->dependencyCollector->collect($remote, $destination);

			// Generate the new helper files. This method uses static analysis, so
			// we do not have call this method in a separate process for each
			// of the different Illuminate branches that we are splitting.
			$helpersWritten = $this->helperCollector->collect($remote, $destination);



			// This will update the composer.json file to remove the helpers.php entry.
			if ($helpersWritten == 0) {
				$this->warn("\tThe resulting composer.json file will be updated to reflect this.");
				$composerJson = file_get_contents($this->paths->output."/composer.json");
				$composerJson = str_replace('"src/Illuminate/Support/helpers.php"', '', $composerJson);
				file_put_contents($this->paths->output."/composer.json", $composerJson);
				$this->warn("\tThe 'helpers.php' file was removed from the 'composer.json' file!");
			}

			$this->file->copyFile(
				$this->paths->collection, $this->paths->output.'/src/Illuminate/Support/Collection.php'
			);

			file_put_contents($this->paths->output.'/.collector.json', json_encode([
				'version' => $destination,
				'time'    => time()
			]));

			$this->history->addSplitToHistory($destination);

			$this->info("Starting test runner...");

			if ($this->testRunner->runTestsOn($destination) == 0) {
				$this->report("Tests passed for {$destination}");

				if (config('git.publish') !== null) {
					$publishResult = $this->publisher->publish($destination);
					$this->publisher->updateRepository();
				}

			}

		} else {
			$outputDir = $this->paths->output;
			$this->line("Skipping generation of {$remote}. It has already been split. If this is not desired, please remove the output directory at {$outputDir}");
		}

		$this->currentTry = 1;
	}

	/**
	 * Sets if git operations should be skipped.
	 *
	 * @param boolean $skip
	 */
	public function shouldSkipGitOperators($skip = true)
	{
		$this->skipGitOperations = $skip;
	}

	/**
	 * Sets if only missing remotes should be cloned.
	 *
	 * @param boolean $onlyNew
	 */
	public function onlySupportNewGitOperations($onlyNew)
	{
		$this->onlyNewGitBranches = $onlyNew;
	}

	public function forceSplit($force)
	{
		$this->forceSplit = $force;
	}

}