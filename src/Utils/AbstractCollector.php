<?php

namespace Collector\Utils;

use Closure;
use Collector\Utils\File;
use Collector\Utils\Notifier;
use Collector\Utils\Analyzer\Analyzer;

abstract class AbstractCollector
{
	use Notifier;

	/**
	 * The File instance.
	 * 
	 * @var Collector\Utils\File
	 */
	protected $file;

	/**
	 * The Analyzer instance.
	 * 
	 * @var Collector\Utils\Analyzer\Analyzer
	 */
	protected $analyzer;

	public function __construct()
	{
		$this->file     = new File;
		$this->analyzer = new Analyzer;
	}

	/**
	 * Sets the notifiers the collector will use.
	 * 
	 * @param Closure $notifier
	 */
	public function setNotifiers(Closure $notifier)
	{
		$this->setNotifier($notifier);
		$this->file->setNotifier($notifier);
	}


}