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

function psr4($namespace, $path)
{
	Collector\Application::getInstance()->getLoader()->setPsr4($namespace, $path);
}

function process($process)
{
	return new Symfony\Component\Process\Process($process);
}