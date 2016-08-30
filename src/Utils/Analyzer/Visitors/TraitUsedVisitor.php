<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\TraitUse;
use PhpParser\NodeVisitorAbstract;

class TraitUsedVisitor extends NodeVisitorAbstract
{

	protected $traitsUsed = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof TraitUse) {
			$this->traitsUsed[] = $node;
		}
	}

	public function getTraitsUsed()
	{
		return $this->traitsUsed;
	}

}