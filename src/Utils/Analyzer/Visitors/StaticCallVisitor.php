<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeVisitorAbstract;

class StaticCallVisitor extends NodeVisitorAbstract
{

	protected $staticCalls = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof StaticCall) {
			$this->staticCalls[] = $node;
		}
	}

	public function getStaticCalls()
	{
		return $this->staticCalls;
	}

}