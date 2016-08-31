<?php

use PhpParser\Node\Name;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Function_;
use Collector\Utils\Analyzer\Analyzer;

class AnalyzerTest extends PHPUnit_Framework_TestCase
{

	const TEST_PHP = '<?php namespace Test;';

	protected $analyzer;

	public function setUp()
	{
		$this->analyzer = new Analyzer;
		Analyzer::$previouslyAnalyzed = [];
	}


	protected function getCodePath()
	{
		return realpath(__DIR__.'/../files/code').'/';
	}

	protected function getFile($sourceFile)
	{
		return normalize_line_endings(file_get_contents($this->getCodePath().$sourceFile.'.php'));
	}

	protected function getExpected($sourceFile)
	{
		return normalize_line_endings(file_get_contents(__DIR__.'/../files/expected_code/'.$sourceFile.'.php'));
	}

	public function testThatAnalyzerReturnsCorrectSource()
	{
		$this->analyzer->analyze(self::TEST_PHP);
		$this->assertEquals(self::TEST_PHP, $this->analyzer->getSource());
	}

	public function testThatSourceDirectoryIsSetCorrectly()
	{
		$this->analyzer->setSourceDirectory('test');
		$this->assertEquals('test', $this->analyzer->getSourceDirectory());
	}

	public function testThatAnalyzeMethodReturnsInstanceOfAnalyze()
	{
		$instance = $this->analyzer->analyze(self::TEST_PHP);
		$this->assertInstanceOf(Analyzer::class, $instance);
		$this->assertSame($this->analyzer, $instance);
	}

	public function testThatAnalyzerCreatesStatementsArray()
	{
		$statements = $this->analyzer->analyze(self::TEST_PHP)->statements();
		$this->assertInternalType('array', $statements);
		$this->assertCount(1, $statements);
	}

	public function testThatAnalyzerGetsUsingStatements()
	{
		$using = $this->analyzer->analyze($this->getFile('UsingStatements'))->getUsingStatements();
		$this->assertCount(3, $using);
	}

	public function testThatPrinterPrintsCorrectSource()
	{
		$using = $this->analyzer->analyze($this->getFile('UsingStatements'))->getUsingStatements();
		$code  = $this->analyzer->printUses($using);
		$this->assertEquals($this->getExpected('UsingStatements'), $code);
	}

	public function testThatPrinterCanExlcudeItemsNotAnalyzed()
	{
		$using = $this->analyzer->analyze($this->getFile('UsingStatements'))->getUsingStatements();
		Analyzer::$previouslyAnalyzed = ['Illuminate\Support\Traits\Macroable'];

		$code = $this->analyzer->printUses($using, true);
		$this->assertEquals($this->getExpected('UsingStatements_Limited'), $code);
	}

	public function testThatAnalyzerGetsDefinedFunctions()
	{
		$functions = $this->analyzer->analyze($this->getFile('DefinedFunctions'))->getDefinedFunctions();
		$this->assertContains('array_only', $functions);
		$this->assertContains('array_pluck', $functions);
		$this->assertCount(2, $functions);
	}

	public function testThatAnalyzeCanReturnNodesForDefinedFunctions()
	{
		$functions = $this->analyzer->analyze($this->getFile('DefinedFunctions'))->getDefinedFunctions(true);
		$this->assertCount(2, $functions);
		$this->assertInstanceOf(Function_::class, $functions[0]);
	}

	public function testThatAnalyzerCanFindFunctionsCalls()
	{
		$calledFunctions = $this->analyzer->analyze($this->getFile('DefinedFunctions'))->getFunctionCalls();
		$this->assertCount(1, $calledFunctions);
		$this->assertContains('function_exists', $calledFunctions);
	}

	public function testThatAnalyzerCanReturnNodesForDefinedFunctions()
	{
		$calledFunctions = $this->analyzer->analyze($this->getFile('DefinedFunctions'))->getFunctionCalls(true);
		$this->assertCount(2, $calledFunctions);
		$this->assertInstanceOf(FuncCall::class, $calledFunctions[0]);
	}

	public function testThatAnalyzerGetsNamespace()
	{
		$namespace = $this->analyzer->analyze('<?php namespace Illuminate\Support\Traits;')->getNamespace();
		$this->assertInstanceOf(Name::class, $namespace);
		$this->assertEquals('Illuminate\Support\Traits', (string) $namespace);
	}

	public function testThatAnalyzerGetsClassName()
	{
		$className = $this->analyzer->analyze($this->getFile('ClassName'))->getClass();
		$this->assertEquals('Illuminate\Support\Collection', $className);
	}

	public function testThatAnalyzerCanGetClassNameWithoutNamespace()
	{
		$className = $this->analyzer->analyze($this->getFile('ClassName'))->getClass(false);
		$this->assertEquals('Collection', $className);
	}

	public function testThatAnalyzerCanResolveDependencies()
	{
		$this->analyzer->setSourceDirectory($this->getCodePath().'/src/');
		$dependencies = $this->analyzer->analyze($this->getFile('src/Illuminate/Support/Collection'))->getDependencies();

		$this->assertCount(5, $dependencies);
		$this->assertContains('Illuminate\Support\Arr', $dependencies);
		$this->assertContains('Illuminate\Support\Traits\Macroable', $dependencies);
		$this->assertContains('Illuminate\Contracts\Support\Jsonable', $dependencies);
		$this->assertContains('Illuminate\Contracts\Support\Arrayable', $dependencies);
		$this->assertContains('Illuminate\Support\Collection', $dependencies);
		
	}

	public function testThatAnalyzerCanReturnNonNamespacedItems()
	{
		$this->analyzer->setSourceDirectory($this->getCodePath().'/src/');
		$dependencies = $this->analyzer->analyze($this->getFile('src/Illuminate/Support/Collection'))->getDependencies(false, false);

		$this->assertCount(13, $dependencies);
		$this->assertContains('Countable', $dependencies);
		$this->assertContains('ArrayAccess', $dependencies);
		$this->assertContains('Traversable', $dependencies);
		$this->assertContains('ArrayIterator', $dependencies);
		$this->assertContains('CachingIterator', $dependencies);
		$this->assertContains('JsonSerializable', $dependencies);
		$this->assertContains('IteratorAggregate', $dependencies);
		$this->assertContains('InvalidArgumentException', $dependencies);
		$this->assertContains('Illuminate\Support\Arr', $dependencies);
		$this->assertContains('Illuminate\Support\Traits\Macroable', $dependencies);
		$this->assertContains('Illuminate\Contracts\Support\Jsonable', $dependencies);
		$this->assertContains('Illuminate\Contracts\Support\Arrayable', $dependencies);
		$this->assertContains('Illuminate\Support\Collection', $dependencies);

	}

	public function testThatAnalyzerCanReturnResolveDeeplyNestedDependencies()
	{
		$this->analyzer->setSourceDirectory($this->getCodePath().'/nested_src/');
		$dependencies = $this->analyzer->analyze($this->getFile('nested_src/Illuminate/Support/Collection'))->getDependencies();

		$this->assertCount(7, $dependencies);
		$this->assertContains('Illuminate\Support\Traits\Stop', $dependencies);
		$this->assertContains('Illumiante\Support\Traits\The\Madness', $dependencies);
		$this->assertContains('Illuminate\Support\Arr', $dependencies);
		$this->assertContains('Illuminate\Support\Traits\Macroable', $dependencies);
		$this->assertContains('Illuminate\Contracts\Support\Jsonable', $dependencies);
		$this->assertContains('Illuminate\Contracts\Support\Arrayable', $dependencies);
		$this->assertContains('Illuminate\Support\Collection', $dependencies);
	}

}