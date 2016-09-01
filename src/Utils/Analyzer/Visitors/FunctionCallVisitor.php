<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeVisitorAbstract;

class FunctionCallVisitor extends NodeVisitorAbstract
{

	/**
	 * List of all function calls found.
	 * 
	 * @var array
	 */
	protected $functionCalls = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof FuncCall) {
			if ($node->name instanceof Name) {
				$this->functionCalls[] = $node;
			}
		}
	}

	/**
	 * Gets the function calls discovered by the visitor.
	 * 
	 * @return array
	 */
	public function getFunctionCalls()
	{
		return $this->functionCalls;
	}

}