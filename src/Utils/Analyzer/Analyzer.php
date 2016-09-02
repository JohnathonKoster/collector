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

    /**
     * The source code to analyze.
     * 
     * @var string
     */
    protected $source = '';

    /**
     * The Parser instance.
     * 
     * @var PhpParser\ParserAbstract
     */
    protected $parser;

    /**
     * An array of statements.
     * 
     * @var array
     */
    protected $statements;

    /**
     * The directory to look for additional dependencies in.
     * 
     * @var null|string
     */
    protected $sourceDirectory = null;

    /**
     * The File instance.
     * 
     * @var File
     */
    protected $file;

    /**
     * A maintained list of previously analyzed classes, traits, etc.
     * 
     * @var array
     */
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
     * Gets the source directory.
     * 
     * @return string
     */
    public function getSourceDirectory()
    {
        return $this->sourceDirectory;
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
     * Gets the source code that is being analyzed.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
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

    /**
     * Gets the "use" statements for the current source.
     * 
     * @return array
     */
    public function getUsingStatements()
    {
        $traverser = new NodeTraverser;
        $visitor   = new UseDependencyVisitor;

        $traverser->addVisitor($visitor);
        $traverser->traverse($this->statements);

        return $visitor->getUseStatements();
    }

    /**
     * Prints a usable code snippet containing the provided "use" statements.
     * 
     * @param  array   $statements
     * @param  boolean $limitToDiscoveredDependencies
     * 
     * @return string
     */
    public function printUses(array $statements, $limitToDiscoveredDependencies = false)
    {
        $printer = new StandardPrinter;

        $statements = array_filter($statements, function($statement) {
            return $statement instanceof Use_;
        });

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
     * @return array
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

        return array_unique($functions);
    }

    /**
     * Get the functions called in a source file.
     *
     * @param  boolean $returnNodes
     *
     * @return array
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

        return array_unique($calls);
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

    /**
     * Gets the class name for the current source.
     *
     * @param  boolean $includeNamespace
     * 
     * @return string
     */
    public function getClass($includeNamespace = true)
    {
        $traverser = new NodeTraverser;
        $visitor   = new ClassVisitor;

        $traverser->addVisitor($visitor);
        $traverser->traverse($this->statements);

        $className = '';

        if ($visitor->getClass() !== null) {
            $className = $visitor->getClass()->name;
        }

        if ($includeNamespace) {
            $namespace = $this->getNamespace();

            return "{$namespace}\\{$className}";
        }

        return $className;
    }

    /**
     * Get the dependencies for the current source.
     *
     * @param  boolean $returnNodes
     * @param  boolean $onlyNamespaced
     *
     * @return UseDependencyVisitor|array
     */
    public function getDependencies($returnNodes = false, $onlyNamespaced = true)
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

        $traverser         = new NodeTraverser;
        $visitor           = new UseDependencyVisitor;
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
                }

            } elseif ($use instanceof TraitUse) {
                $name = $use->traits[0]->toString();
            }

            $uses[] = $name;
        }

        // Quick little performance improvement.
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

                        // Merge the dependencies.
                        $uses = array_merge($uses, $nestedDependencies);
                    }                }
            }
        }

        $uses = array_unique($uses);

        if ($onlyNamespaced) {
            $uses = array_filter($uses, function($dependency) {
                return (count(explode('\\', $dependency)) > 1);
            });
        }

        return $uses;
    }

}