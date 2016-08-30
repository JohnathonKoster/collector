<?php

namespace Collector\Utils;

use Closure;
use Collector\Utils\File;
use Collector\Utils\Notifier;
use Collector\Utils\Analyzer\Analyzer;

abstract class AbstractCollector
{
	use Notifier;

	protected $file;

	protected $analyzer;

	public function __construct()
	{
		$this->file = new File;
		$this->analyzer = new Analyzer;
	}

	public function setNotifiers(Closure $notifier)
	{
		$this->setNotifier($notifier);
		$this->file->setNotifier($notifier);
	}


}