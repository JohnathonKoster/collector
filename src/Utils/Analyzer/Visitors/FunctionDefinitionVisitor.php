<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;

class FunctionDefinitionVisitor extends NodeVisitorAbstract
{

	protected $functionDefinitions = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof Function_) {
			$this->functionDefinitions[] = $node;
		}
	}

	public function getFunctionDefinitions()
	{
		return $this->functionDefinitions;
	}

}