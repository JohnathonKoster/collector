<?php

namespace Collector\Utils\Dependency;

use Collector\Utils\AbstractCollector;

class Collector extends AbstractCollector
{

	protected $paths;

	protected function getClassDependencies($className)
	{
		$path = $this->paths->support."/{$className}";
		$this->analyzer->setSourceDirectory($this->paths->source);
		$dependencies = $this->analyzer->analyze(file_get_contents($path))->getDependencies();

		$dependencies = array_unique(array_filter($dependencies, function($dependency) {
			return mb_strpos($dependency, 'Illuminate') === 0;
		}));

		return $dependencies;
	}

	public function collect($remote, $local)
	{
		$this->paths = $this->file->getDirectories($remote, $local);
		$this->info("\nSearching for dependencies from {$remote}... in '{$this->paths->support}'");

		foreach (config('split.classes') as $class) {
			$this->report("Searching {$class} for dependencies...");
			$dependencies = $this->getClassDependencies($class);

			foreach ($dependencies as $dependency) {
				$this->report("Found explicit dependency {$dependency}");
				$this->file->copyFile(
					$this->paths->source."/src/{$dependency}.php",
					$this->paths->output."/src/{$dependency}.php"
				);
			}
		}

	}


}