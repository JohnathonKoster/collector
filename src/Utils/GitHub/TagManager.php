<?php

namespace Collector\Utils\GitHub;

use Github\Client;
use Github\HttpClient\CachedHttpClient;
use Github\HttpClient\Cache\FilesystemCache;

class TagManager
{

	protected $client;
	protected $github;

	protected $cacheTagFile;

	public function __construct()
	{
		$this->client = new CachedHttpClient;
		$this->client->setCache(
			new FilesystemCache(__DIR__.'/../../storage/cache/github')
		);
		$this->github = new Client($this->client);

		$this->cacheTagFile = __DIR__.'/../../storage/cache/tags/remote.json';
	}

	public function getTags()
	{
		$tags = $this->github->api('repo')->tags('laravel', 'framework');
		
		return $tags;
	}

	public function cacheTags()
	{
		$tags = $this->getTags();


		$justName = [];

		foreach ($tags as $tag) {
			$justName[] = $tag['name'];
		}

		file_put_contents($this->cacheTagFile, json_encode($justName));
	}

	public function getCacheTags()
	{
		if (!file_exists($this->cacheTagFile)) {
			$this->cacheTags();
		}

		return json_decode(file_get_contents($this->cacheTagFile));
	}

	public function getTagsAfter($after)
	{
		$tags = array_reverse($this->getCacheTags());
		$tags = array_slice($tags, array_search($after, $tags));
		return $tags;
	}

}