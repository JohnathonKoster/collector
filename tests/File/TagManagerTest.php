<?php

use Mockery as m;
use Collector\Utils\GitHub\TagManager;
use Collector\Utils\GitHub\AbstractTagSource;
use Collector\Utils\FilesystemVirtualization\Assertions;
use Collector\Utils\FilesystemVirtualization\FilesystemVirtualization;

class TagManagerTest extends PHPUnit_Framework_TestCase
{
	use FilesystemVirtualization;

	protected $virtualPath = 'fst';

	protected $tags = [
			'v5.3.4','v5.3.3','v5.3.2','v5.3.1','v5.3.0','v5.3.0-RC1','v5.2.45','v5.2.44','v5.2.43','v5.2.42','v5.2.40','v5.2.39','v5.2.38','v5.2.37','v5.2.36','v5.2.35','v5.2.34','v5.2.33','v5.2.32','v5.2.31','v5.2.30','v5.2.29','v5.2.28','v5.2.27','v5.2.26','v5.2.25','v5.2.24','v5.2.23','v5.2.22','v5.2.21'	
		];

	protected $tagManager;

	public function setUp()
	{
		$this->setUpVfs();
		$tagSource = m::mock(AbstractTagSource::class);
		$tagSource->shouldReceive('getTags')->andReturn($this->tags);
 		$this->tagManager = new TagManager($tagSource);
	}

	public function tearDown()
	{
		$this->tearDownVfs();
	}

	public function testThatGetCacheTagsWillCreateCacheFile()
	{
		$this->tagManager->setCacheFile($this->getPath('split.json'));
		$this->tagManager->getCacheTags();
		$this->assertFileExists($this->getPath('split.json'));
	}

	public function testThatGetCacheTagsReturnsArray()
	{
		$this->tagManager->setCacheFile($this->getPath('history.json'));
		$return = $this->tagManager->getCacheTags();
		$this->assertInternalType('array', $return);
		$this->assertCount(count($this->tags), $return);
		$this->assertSame($this->tags, $return);
	}

	public function testThatGetVersionAfterReturnsCorrectValues()
	{
		$this->tagManager->setCacheFile($this->getPath('history.json'));
		$this->tagManager->getCacheTags();
		$after = $this->tagManager->getTagsAfter('v5.3.1');

		$this->assertCount(4, $after);
		$this->assertContains('v5.3.4', $after);
		$this->assertContains('v5.3.3', $after);
		$this->assertContains('v5.3.2', $after);
		$this->assertContains('v5.3.1', $after);
	}

}