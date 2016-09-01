<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;

class FunctionDefinitionVisitor extends NodeVisitorAbstract
{

	/**
	 * List of function definitions discovered by the visitor.
	 * 
	 * @var array
	 */
	protected $functionDefinitions = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof Function_) {
			$this->functionDefinitions[] = $node;
		}
	}

	/**
	 * Gets the function definitions discovered by the visitor.
	 * 
	 * @return array
	 */
	public function getFunctionDefinitions()
	{
		return $this->functionDefinitions;
	}

}