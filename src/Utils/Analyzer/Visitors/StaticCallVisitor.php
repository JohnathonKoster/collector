<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\NodeVisitorAbstract;

class StaticCallVisitor extends NodeVisitorAbstract
{

	/**
	 * A list of all static calls discovered by the visitor.
	 * 
	 * @var array
	 */
	protected $staticCalls = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof StaticCall) {
			$this->staticCalls[] = $node;
		}
	}

	/**
	 * Gets the static calls discovered by the visitor.
	 * 
	 * @return array
	 */
	public function getStaticCalls()
	{
		return $this->staticCalls;
	}

}