<?php

namespace Collector\Utils\GitHub;

class Factory
{

	/**
	 * Makes a TagManager configured for use with the GitHubTagSource
	 * 
	 * @return Collector\Utils\GitHub\TagManager
	 */
	public static function makeGitHubTagManager()
	{
		$manager = new TagManager(new GitHubTagSource);
		$manager->setCacheFile(__DIR__.'/../../../storage/cache/tags/remote.json');

		return $manager;
	}

}