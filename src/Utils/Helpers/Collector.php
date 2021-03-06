<?php

namespace Collector\Utils\Helpers;

use Collector\Utils\AbstractCollector;
use Collector\Utils\Dependency\Collector as DependencyCollector;

class Collector extends AbstractCollector
{

	/**
	 * A list of paths to common Collection utility files.
	 * 
	 * @var array
	 */
	protected $paths;

	/**
	 * A list of helper functions that should always be included (if they exist).
	 * 
	 * @var array
	 */
	protected $helpersToAlwaysInclude = [
		'collect'
	];

	/**
	 * The DependencyCollector instance.
	 * 
	 * @var Collector\Utils\Dependency\Collector
	 */
	protected $dependencyCollector;

	public function __construct()
	{
		parent::__construct();
		$this->dependencyCollector = new DependencyCollector;
	}

	/**
	 * Gets the support helper functions from the Laravel code base.
	 * 
	 * @param  boolean $returnNodes
	 * @return array
	 */
	protected function getIlluminateHelperFunctions($returnNodes = false)
	{
		$functions = $this->analyzer->analyze(file_get_contents($this->paths->helpers))->getDefinedFunctions($returnNodes);
		return $functions;
	}

	/**
	 * Gets any dependencies required by the helper functions.
	 * 
	 * @return array
	 */
	protected function getHelperDependencies()
	{
		return $this->analyzer->analyze(file_get_contents($this->paths->helpers))->getUsingStatements();
	}

	/**
	 * Determines which helper functions were used within the Collection.php file.
	 * 
	 * @param  array $helpersToLookFor
	 * @return array
	 */
	protected function getUsedIlluminateHelpers(array $helpersToLookFor)
	{
		$functionCalls = $this->analyzer->analyze(file_get_contents($this->paths->collection))->getFunctionCalls();

		$helpersCalled = [];

		foreach ($functionCalls as $funcCall) {
			if (in_array($funcCall, $helpersToLookFor)) {
				$helpersCalled[] = $funcCall;
			}
		}

		return array_unique($helpersCalled);
	}

	/**
	 * Writes the new helpers.php file to the output directory.
	 * 
	 * @param  string $remote
	 * @param  array  $helpers
	 * 
	 */
	protected function writeNewHelperFile($remote, $helpers)
	{
		$helperSource  = explode("\n", file_get_contents($this->paths->helpers));

		$functions = $this->getIlluminateHelperFunctions(true);
		$deps      = $this->getHelperDependencies();

		// This will generate the beginning of our helpers file.
		$newHelperFile = $this->analyzer->printUses($deps, true);

		$this->info("Creating output helpers file...");

		// Simply iterate over the functions and build up the new helpers file.
		foreach ($functions as $func) {
			if (in_array($func->name, $helpers)) {
				$attributes = $func->getAttributes();
				$start      = $attributes['startLine'] - 1;
				$end        = $attributes['endLine'];
				$comment    = $attributes['comments'][0]->getText();
				
				// Get the source lines for the function.
				$lines = array_slice($helperSource, $start, ($end - $start));

				$this->report("Writing code block for helper function: {$func->name}");
				
				$comments = "\n\t{$comment}";
				$newHelperFile .= "\n\nif (! function_exists('{$func->name}')) {{$comments}\n".implode("\n", $lines)."\n}";
			}
		}

		$pathToNewHelpers = $this->paths->output.'/src/Illuminate/Support/helpers.php';
		$this->file->makeDir(dirname($pathToNewHelpers));
		file_put_contents($pathToNewHelpers, $newHelperFile);
		$pathToNewHelpers = realpath($pathToNewHelpers);
		$this->info("New output helpers file written to {$pathToNewHelpers}");
		
	}

	/**
	 * Collects the helper functions used and generates a new helpers.php file.
	 * 
	 * @param  string $remote
	 * @param  string $local
	 * 
	 * @return int
	 */
	public function collect($remote, $local)
	{
		$this->paths = $this->file->getDirectories($remote, $local);

		$this->info("Collecting helper functions from {$remote}...");
		$this->report("Discovering the Illuminate helper functions...");

		$helperFunctions = $this->getIlluminateHelperFunctions();
		$countHelperFunctions  = count($helperFunctions);

		$this->report("Discovered {$countHelperFunctions} Illuminate helper functions! So many!");
		$this->info("Searching the '{$remote}' code-base for used helper functions...");

		$helpersCalled = $this->getUsedIlluminateHelpers($helperFunctions);
		$countHelpersCalled = count($helpersCalled);

		$this->report("Discovered {$countHelpersCalled} being used in the Collection source file.");
		
		// Bail at this point if now helpers were discovered.
		if ($countHelpersCalled == 0) {
			$this->warn("Helpers are not being written for {$remote}. No helpers used!");
			return 0;
		}

		// Display the helpers that were discovered.
		foreach ($helpersCalled as $helper) {
			$this->report("\t{$helper}");
		}

		$this->info("Merging required helpers and sorting discovered helper functions...");

		sort($helpersCalled);
		$helpersCalled = array_merge($this->helpersToAlwaysInclude, $helpersCalled);

		$this->report("The resulting helper list is:");

		// Display the final list of helpers.
		foreach ($helpersCalled as $helper) {
			$this->report("\t{$helper}");
		}

		$this->info("Creating modified helper file...");

		// Actually write the new helpers file.
		$this->writeNewHelperFile($remote, $helpersCalled);

		return $countHelpersCalled;
	}


}