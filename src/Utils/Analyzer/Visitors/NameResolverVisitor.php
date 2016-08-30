<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\NodeVisitor\NameResolver;

class NameResolverVisitor extends NameResolver
{

	public function getNamespace()
	{
		return $this->namespace;
	}

	public function getAliases()
	{
		return $this->aliases;
	}

}