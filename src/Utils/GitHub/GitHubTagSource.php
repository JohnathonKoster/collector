<?php

namespace Collector\Utils\GitHub;

use Github\Client;
use Github\HttpClient\CachedHttpClient;
use Github\HttpClient\Cache\FilesystemCache;

class GitHubTagSource extends AbstractTagSource
{

	/**
	 * The CachedHttpClient instance.
	 * 
	 * @var Github\HttpClient\CachedHttpClient
	 */
	protected $client;

	/**
	 * The Client instance.
	 * 
	 * @var Github\Client;
	 */
	protected $github;

	public function __construct()
	{
		$this->client = new CachedHttpClient;
		$this->client->setCache(
			new FilesystemCache(__DIR__.'/../../../storage/cache/github')
		);
		$this->github = new Client($this->client);
	}

	/**
	 * Gets the remote tags.
	 * 
	 * @return array
	 */
	public function getTags()
	{
		$tags = $this->github->api('repo')->tags('laravel', 'framework');
		
		return $tags;
	}

}