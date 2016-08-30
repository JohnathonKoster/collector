<?php

/**
 * Gets the value for the provided configuration key.
 *
 * @param  string
 * @return mixed
 */
function config($key)
{
	return Collector\Support\Config::getInstance()->get($key);
}