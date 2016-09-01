<?php

namespace Collector\Utils\Analyzer\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Interface_;

class ClassVisitor extends NodeVisitorAbstract
{

	/**
	 * The last visited Class_ instance.
	 * 
	 * @var PhpParser\Node\Stmt\Class_
	 */
	protected $class = null;

	public function enterNode(Node $node)
	{
		if ($node instanceof Class_ || $node instanceof Trait_ || $node instanceof Interface_) {
			$this->class = $node;
		}
	}

	/**
	 * Gets the last class discovered by the visitor.
	 * 
	 * @return PhpParser\Node\Stmt\Class_
	 */
	public function getClass()
	{
		return $this->class;
	}

}