<?php

namespace Collector\Utils\GitHub;

abstract class AbstractTagSource
{

	/**
	 * Gets the remote tags.
	 * 
	 * @return array
	 */
	abstract public function getTags();

}