<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeVisitorAbstract;

class FunctionCallVisitor extends NodeVisitorAbstract
{

	protected $functionCalls = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof FuncCall) {
			if ($node->name instanceof Name) {
				$this->functionCalls[] = $node;
			}
		}
	}

	public function getFunctionCalls()
	{
		return $this->functionCalls;
	}

}