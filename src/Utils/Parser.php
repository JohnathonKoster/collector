<?php

namespace Collector\Utils;

class Parser
{

	public function getNamespace($source)
	{
		$matches = [];
		preg_match_all('/namespace (.*?);/', $source, $matches);

		if (count($matches) > 1) {
			return $matches[1][0];
		}

		return false;
	}

	public function getClassName($source)
	{
		$matches = [];
		preg_match('/class[\s\n]+([a-zA-Z0-9_]+)[\s\na-zA-Z0-9_,]+\{/', $source, $matches);

		if (count($matches) > 1) {
			return $matches[1];
		}

		return false;
	}

	/**
	 * Parses the Illuminate dependencies from source.
	 * 
	 * @param  string $source
	 * @return array
	 */
	public function getIlluminateDependencies($source)
	{
		$matches = [];
		preg_match_all('/use Illuminate.*?;/', $source, $matches);

		if (count($matches) == 0 || count($matches[0]) == 0) {
			return [];
		}

		$matches = $matches[0];

		$matches = array_map(function($dependency) {
			return str_replace('\\', '/', mb_substr($dependency, 4, -1)).'.php';
		}, $matches);

		$dependencies = [
			'support'   => [],
			'contracts' => []
		];

		foreach ($matches as $match) {
			if (mb_strpos($match, 'Illuminate/Support') === 0) {
				$dependencies['support'][] = $match;
			} elseif (mb_strpos($match, 'Illuminate/Contracts') === 0) {
				$dependencies['contracts'][] = $match;
			}
		}

		// This will catch any classes, and automatically add them.
		$class = $this->getClassName($source);
		if ($class !== false) {
			// Also get the namespace.
			$namespace = $this->getNamespace($source);
			$fullClass = $namespace.'\\'.$class;
			$fullClass = str_replace('\\', '/', $fullClass);
			$dependencies['support'][] = $fullClass.'.php';
		}

		return $dependencies;
	}

}