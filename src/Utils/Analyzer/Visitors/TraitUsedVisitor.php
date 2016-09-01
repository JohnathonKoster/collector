<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\TraitUse;
use PhpParser\NodeVisitorAbstract;

class TraitUsedVisitor extends NodeVisitorAbstract
{

	/**
	 * A list of traits discovered by the visitor.
	 * 
	 * @var array
	 */
	protected $traitsUsed = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof TraitUse) {
			$this->traitsUsed[] = $node;
		}
	}

	/**
	 * Gets the traits that have been used in the current statement tree.
	 * 
	 * @return array
	 */
	public function getTraitsUsed()
	{
		return $this->traitsUsed;
	}

}