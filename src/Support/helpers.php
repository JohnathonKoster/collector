<?php

/**
 * Super simple configuration stuff.
 *
 * @param  string
 * @return mixed
 */
function config($key)
{
	return Collector\Support\Config::getInstance()->get($key);
}