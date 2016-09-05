<?php

namespace Collector\Utils\GitHub;

class ArrayTagSource extends AbstractTagSource
{

	/**
	 * Gets the remote tags.
	 * 
	 * @return array
	 */
	public function getTags()
	{
		return json_decode(file_get_contents(realpath(__DIR__.'/../../../storage/releases.json')));
	}

}