<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

class UseDependencyVisitor extends NodeVisitorAbstract
{

	protected $useStatements = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof Use_ || $node instanceof TraitUse) {
			$this->useStatements[] = $node;
		}
	}

	public function getUseStatements()
	{
		return $this->useStatements;
	}

}