<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\NodeVisitor\NameResolver;

class NameResolverVisitor extends NameResolver
{

	/**
	 * Gets the namespace for the statement tree.
	 * 
	 * @return null|PhpParser\Node\Name
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * Gets the aliases discovered in the current statement tree.
	 * 
	 * @return array
	 */
	public function getAliases()
	{
		return $this->aliases;
	}

}