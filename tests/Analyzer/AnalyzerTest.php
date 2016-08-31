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

	protected function getFile($sourceFile)
	{
		return file_get_contents(__DIR__.'/../files/code/'.$sourceFile.'.php');
	}

	protected function getExpected($sourceFile)
	{
		return file_get_contents(__DIR__.'/../files/expected_code/'.$sourceFile.'.php');
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

}