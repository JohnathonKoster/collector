<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Interface_;

class ClassVisitor extends NodeVisitorAbstract
{

	protected $class = null;

	public function enterNode(Node $node)
	{
		if ($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Interface_) {
			$this->class = $node;
		}
	}

	
	public function getClass()
	{
		return $this->class;
	}

}