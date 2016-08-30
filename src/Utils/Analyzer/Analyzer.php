<?php

namespace Collector\Utils\Analyzer;

use PhpParser\Error;

use Collector\Utils\File;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\TraitUse;
use Collector\Utils\Analyzer\Visitors\ClassVisitor;
use Collector\Utils\Analyzer\Visitors\TraitUsedVisitor;
use PhpParser\PrettyPrinter\Standard as StandardPrinter;
use Collector\Utils\Analyzer\Visitors\StaticCallVisitor;
use Collector\Utils\Analyzer\Visitors\NameResolverVisitor;
use Collector\Utils\Analyzer\Visitors\FunctionCallVisitor;
use Collector\Utils\Analyzer\Visitors\UseDependencyVisitor;
use Collector\Utils\Analyzer\Visitors\FunctionDefinitionVisitor;

class Analyzer
{

	protected $source = '';

	protected $parser;

	public $statements;

	protected $sourceDirectory = null;

	protected $file;

	public static $previouslyAnalyzed = [];

	public function __construct()
	{
		$this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
		$this->file   = new File;
	}

	/**
	 * Sets the source directory.
	 *
	 * @param string $directory
	 */
	public function setSourceDirectory($directory)
	{
		$this->sourceDirectory = $directory;
	}

	/**
	 * Analyzes the source.
	 *
	 * @param  string $source
	 *
	 * @return Analyzer
	 */
	public function analyze($source)
	{
		$this->source = $source;
		$this->statements = $this->parser->parse($source);
		return $this;
	}

	/**
	 * Returns the statements for the current source.
	 *
	 * @return array
	 */
	public function statements()
	{		
		return $this->statements;
	}

	public function getUsingStatements()
	{
		$traverser = new NodeTraverser;
		$visitor   = new UseDependencyVisitor;
		$traverser->addVisitor($visitor);

		$traverser->traverse($this->statements);

		return $visitor->getUseStatements();
	}

	public function printUses($statements, $limitToDiscoveredDependencies = false)
	{
		$printer = new StandardPrinter;

		if ($limitToDiscoveredDependencies) {
			$newStatements = [];
			foreach ($statements as $statement) {
				if ($statement instanceof Use_) {
					$name = $statement->uses[0]->name->toString();
					if (in_array($name, self::$previouslyAnalyzed)) {
						$newStatements[] = $statement;
					}
				}
			}
			$statements = $newStatements;
		}

		$code = $printer->prettyPrintFile($statements);

		return $code;
	}

	/**
	 * Get the functions defined in a source file.
	 *
	 * @param  boolean $returnNodes
	 *
	 * @return FunctionDefinitionVisitor|array
	 */
	public function getDefinedFunctions($returnNodes = false)
	{
		$traverser = new NodeTraverser;
		$visitor   = new FunctionDefinitionVisitor;
		$traverser->addVisitor($visitor);

		$traverser->traverse($this->statements);
		$functionsFound = $visitor->getFunctionDefinitions();

		if ($returnNodes) {
			return $functionsFound;
		}

		$functions = [];

		foreach ($functionsFound as $func) {
			$functions[] = $func->name;
		}

		return $functions;
	}

	/**
	 * Get the functions called in a source file.
	 *
	 * @param  boolean $returnNodes
	 *
	 * @return FunctionDefinitionVisitor|array
	 */
	public function getFunctionCalls($returnNodes = false)
	{
		$traverser = new NodeTraverser;
		$visitor   = new FunctionCallVisitor;
		$traverser->addVisitor($visitor);

		$traverser->traverse($this->statements);
		$calledFunctions = $visitor->getFunctionCalls();

		if ($returnNodes) {
			return $calledFunctions;
		}

		$calls = [];

		foreach ($calledFunctions as $func) {
			$calls[] = $func->name->getFirst();
		}

		return $calls;
	}

	/**
	 * Gets the namespace for the current source.
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		$traverser = new NodeTraverser;
		$visitor   = new NameResolverVisitor;
		$traverser->addVisitor($visitor);
		$traverser->traverse($this->statements);
		$namespace = $visitor->getNamespace();

		if ($namespace === null) {
			return '';
		}

		return $namespace;
	}

	public function getTraitsUsed()
	{
		$traverser = new NodeTraverser;
		$visitor   = new TraitUsedVisitor;
		$traverser->addVisitor($visitor);
		$traverser->traverse($this->statements);

		$traits = $visitor->getTraitsUsed();

		return $traits;
	}

	/**
	 * Gets the class name for the current source.
	 *
	 * @return string
	 */
	public function getClass()
	{
		$traverser = new NodeTraverser;
		$visitor   = new ClassVisitor;
		$traverser->addVisitor($visitor);
		$traverser->traverse($this->statements);

		$namespace = $this->getNamespace();

		$className = '';

		if ($visitor->getClass() !== null) {
			$className = $visitor->getClass()->name;
		}


		return "{$namespace}\\{$className}";
	}

	/**
	 * Get the dependencies for the current source.
	 *
	 * @param  boolean $returnNodes
	 *
	 * @return UseDependencyVisitor|array
	 */
	public function getDependencies($returnNodes = false)
	{
		// Resolve the names for the current scope. This
		// is important, especially when dealing with
		// static method calls from classes within
		// the current namespace. i.e., Arr::()
		$currentNamespace = $this->getNamespace();
		$currentClass     = $this->getClass();

		// Add the current class to the list
		// of previously analyzed objects.
		self::$previouslyAnalyzed[] = $currentClass;

		$traverser = new NodeTraverser;
		$visitor   = new UseDependencyVisitor;
		$staticCallVisitor = new StaticCallVisitor;
		$traitsUsedVisitor = new TraitUsedVisitor;
		$traverser->addVisitor($visitor);
		$traverser->addVisitor($staticCallVisitor);
		$traverser->addVisitor($traitsUsedVisitor);
		$traverser->traverse($this->statements);
		$useStatements = $visitor->getUseStatements();


		if ($returnNodes == true) {
			return $useStatements;
		}

		$uses = [];

		// Get them pesky static calls.
		$staticCalls = $staticCallVisitor->getStaticCalls();
		$calls = [];

		// Add the static calls to the $uses array.
		foreach ($staticCalls as $call) {
			$className = $call->class->toString();
			$calls[] = $className;
			$uses[] = $className;
		}

		// Add the explicit use statements to the array.
		foreach ($useStatements as $use) {
			$name = '';

			if ($use instanceof Use_) {
				$name = $use->uses[0]->name->toString();

				// For now we will just assume that if any dependency
				// does not have more that one part to its name it
				// belongs to the core PHP framework offerings.
				if (count($use->uses[0]->name->parts) == 1 && !in_array($name, self::$previouslyAnalyzed)) {
					self::$previouslyAnalyzed[] = $name;
					continue;
				}
			} elseif ($use instanceof TraitUse) {
				$name = $use->traits[0]->toString();
			}

			$uses[] = $name;
		}

		$uses = array_unique($uses);

		// If there is a source directory set, we will recursively analyze the
		// dependencies that have been declared in the current class. This
		// will also be able to find any implicit dependencies, such as
		// those classes that are called statically in the namespace.
		if ($this->sourceDirectory !== null) {
			foreach ($uses as $use) {
				if (!in_array($use, self::$previouslyAnalyzed)) {
					$usePath = $this->file->normalizePath($this->sourceDirectory."/src/{$use}.php");
					if (file_exists($usePath)) {

						$analyzer = new self;
						$analyzer->setSourceDirectory($this->sourceDirectory);
						$nestedDependencies = $analyzer->analyze(file_get_contents($usePath))->getDependencies();

						// Again, just another way to make sure we are only returning
						// namespaced class names to not include PHP classes, etc.
						$uses = array_merge($uses, array_filter($nestedDependencies, function ($dependency) {
							return count(explode('\\', $dependency) > 1);
						}));
					}
				}
			}
		}

		return $uses;
	}


}