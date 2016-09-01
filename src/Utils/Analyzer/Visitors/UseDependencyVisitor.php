<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

class UseDependencyVisitor extends NodeVisitorAbstract
{

	/**
	 * A list of use statements discovered by the visitor.
	 *
	 * Contains both namespaces and traits.
	 * 
	 * @var array
	 */
	protected $useStatements = [];

	public function enterNode(Node $node)
	{
		if ($node instanceof Use_ || $node instanceof TraitUse) {
			$this->useStatements[] = $node;
		}
	}

	/**
	 * Gets the namespaces and traits used in the current statement tree.
	 * 
	 * @return array
	 */
	public function getUseStatements()
	{
		return $this->useStatements;
	}

}