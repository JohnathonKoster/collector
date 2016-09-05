<?php

namespace Collector\Utils\GitHub;

class Factory
{

	public function make()
	{
		return call_user_func([$this, 'make'.config('split.tag_source').'TagManager']);
	}

	/**
	 * Makes a TagManager configured for use with the GitHubTagSource
	 * 
	 * @return Collector\Utils\GitHub\TagManager
	 */
	protected function makeGitHubTagManager()
	{
		$manager = new TagManager(new GitHubTagSource);
		$manager->setCacheFile(__DIR__.'/../../../storage/cache/tags/remote.json');

		return $manager;
	}

	protected function makeArrayTagManager()
	{
		$manager = new TagManager(new ArrayTagSource);
		$manager->setCacheFile(__DIR__.'/../../../storage/cache/tags/remote.json');

		return $manager;
	}

}