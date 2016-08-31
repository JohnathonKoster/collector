<?php

use Collector\Utils\VersionHistoryManager;
use Collector\Utils\FilesystemVirtualization\Assertions;
use Collector\Utils\FilesystemVirtualization\FilesystemVirtualization;

class HistoryManagerTest extends PHPUnit_Framework_TestCase
{
	use FilesystemVirtualization;

	/**
	 * The history manager instance.
	 * 
	 * @var VersionHistoryManager
	 */
	protected $history;

	/**
	 * The virtual path.
	 *
	 * @var string
	 */
	protected $virtualPath = 'fst';

	public function setUp()
	{
		$this->setUpVfs();
		$this->history = new VersionHistoryManager;
		$this->history->load($this->getPath('split.json'));
	}

	public function tearDown()
	{
		$this->tearDownVfs();
	}

	public function testThatHistoryManagerCreatesFileIfItDoesNotExist()
	{
		$history = new VersionHistoryManager;
		$history->load($this->getPath('history.json'));

		$this->assertFileExists($this->getPath('history.json'));
	}

	public function testThatHistoryManagerWillLoadExistingFile()
	{
		$history = new VersionHistoryManager;
		file_put_contents($this->getPath('history.json'), json_encode([
			'v1', 'v2'
		]));
		$historyItems = $history->load($this->getPath('history.json'));

		$this->assertCount(2, $historyItems);
		$this->assertInternalType('array', $historyItems);
		$this->assertContains('v1', $historyItems);
		$this->assertContains('v2', $historyItems);
	}

	public function testThatHistoryCanBeRetrievedWithGetterMethod()
	{
		$history = new VersionHistoryManager;
		file_put_contents($this->getPath('history.json'), json_encode([
			'v1', 'v2'
		]));
		$history->load($this->getPath('history.json'));

		$historyItems = $history->getSplitHistory();

		$this->assertCount(2, $historyItems);
		$this->assertInternalType('array', $historyItems);
		$this->assertContains('v1', $historyItems);
		$this->assertContains('v2', $historyItems);
	}

	public function testThatAddingToHistoryWritesToFile()
	{
		$this->history->add('v3');
		$history = new VersionHistoryManager;
		$history = $history->load($this->getPath('split.json'));

		$this->assertContains('v3', $history);
	}

	public function testThatYouCanCheckIfAnItemExistsInHistory()
	{
		$this->history->add('v3');
		$this->assertTrue($this->history->has('v3'));
		$this->assertTrue($this->history->existsInHistory('v3'));
		$this->assertFalse($this->history->has('nope'));
	}

	/**
	 * @expectedException Exception
	 */
	public function testThatCallingAddSplitHistoryWithoutLoadingFails()
	{
		$history = new VersionHistoryManager;
		$history->addSplitToHistory('v3');
	}

	/**
	 * @expectedException Exception
	 */
	public function testThatCallingGetHistoryWithoutLoadingFails()
	{
		$history = new VersionHistoryManager;
		$history->getSplitHistory();
	}

	/**
	 * @expectedException Exception
	 */
	public function testThatCallingHistoryHasWithoutLoadingFails()
	{
		$history = new VersionHistoryManager;
		$history->has('v3');
	}

}