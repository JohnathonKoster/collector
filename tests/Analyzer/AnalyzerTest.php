<?php


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

}