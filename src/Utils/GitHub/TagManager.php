<?php

namespace Collector\Utils\GitHub;

class TagManager
{

	/**
	 * The AbstractTagSource instance.
	 * 
	 * @var Collector\Utils\GitHub\AbstractTagSource
	 */
	protected $tagSource;

	/**
	 * The path to the cache file.
	 * 
	 * @var string
	 */
	protected $cacheTagFile;

	public function __construct(AbstractTagSource $tagSource)
	{
		$this->tagSource = $tagSource;
	}

	/**
	 * Sets the tag cache file location.
	 * 
	 * @param string $location
	 */
	public function setCacheFile($location)
	{
		$this->cacheTagFile = $location;
	}

	/**
	 * Caches the tags obtained from the tag source.
	 * 
	 */
	protected function cacheTags()
	{
		$tags = $this->tagSource->getTags();


		$justName = [];

		foreach ($tags as $tag) {
			$justName[] = $tag['name'];
		}

		file_put_contents($this->cacheTagFile, json_encode($justName));
	}

	/**
	 * Gets the cached tags.
	 * 
	 * @return string
	 */
	public function getCacheTags()
	{
		if (!file_exists($this->cacheTagFile)) {
			$this->cacheTags();
		}

		return json_decode(file_get_contents($this->cacheTagFile));
	}

	/**
	 * Gets the tags after a specified version.
	 * 
	 * @param  string $version
	 * @return array
	 */
	public function getTagsAfter($version)
	{
		$tags = array_reverse($this->getCacheTags());
		$tags = array_slice($tags, array_search($version, $tags));
		return $tags;
	}

}