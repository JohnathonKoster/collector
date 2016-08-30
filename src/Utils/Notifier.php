<?php

namespace Collector\Utils;

use Closure;
use Symfony\Component\Process\Process;

trait Notifier
{
	protected $notifier = null;

	public function setNotifier(Closure $notifier)
	{
		$this->notifier = $notifier;
	}

	protected function notify($message, $type = 'info')
	{
		if ($this->notifier !== null) {
			$this->notifier->__invoke($message, $type);
		}
	}

	protected function error($message)
	{
		$this->notify($message, 'error');
	}

	protected function warn($message)
	{
		$this->notify($message, 'bg=yellow;fg=black');
	}

	protected function comment($message)
	{
		$this->notify($message, 'comment');
	}

	protected function line($message)
	{
		$this->notify($message, 'fg=black');
	}

	protected function info($message)
	{
		$this->notify($message);
	}

	protected function note($message)
	{
		$this->notify($message, 'bg=cyan');
	}

	protected function reportInfo($message)
	{
		$this->info("\t{$message}");
	}

	protected function report($message)
	{
		$this->notify("\t{$message}", 'fg=magenta');
	}

	protected function run($process)
	{
		$process = new Process($process);
		$process->setTimeout(0);

		return $process->run(function ($type, $buffer) {
			$this->report($buffer);
		});
	}

}