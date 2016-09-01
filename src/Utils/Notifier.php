<?php

namespace Collector\Utils;

use Closure;
use Symfony\Component\Process\Process;

trait Notifier
{

	/**
	 * The notifier callback.
	 *
	 * @var Closure
	 */
	protected $notifier = null;

	/**
	 * Indicates if the notifier should output or not.
	 * 
	 * @var boolean
	 */
	protected $isQuiet = false;

	/**
	 * Sets the notifier callback.
	 *
	 * @param Closure $notifier
	 */
	public function setNotifier(Closure $notifier)
	{
		$this->notifier = $notifier;
	}

	/**
	 * Sets whether or not the notifier is quiet.
	 * 
	 * @param boolean $isQuiet
	 */
	public function isQuet($isQuiet)
	{
		$this->isQuiet = $isQuiet;
	}

	/**
	 * Prints a message using the notifier callback.
	 *
	 * @param  string $message
	 * @param  string $type
	 */
	protected function notify($message, $type = 'info')
	{
		if ($this->notifier !== null && !$this->isQuiet) {
			$this->notifier->__invoke($message, $type);
		}
	}

	/**
	 * Prints a message with error styling.
	 *
	 * @param string $message
	 */
	protected function error($message)
	{
		$this->notify($message, 'error');
	}

	/**
	 * Prints a message with warning styling.
	 *
	 * @param string $message
	 */
	protected function warn($message)
	{
		$this->notify($message, 'bg=yellow;fg=black');
	}

	/**
	 * Prints a message with comment styling.
	 *
	 * @param string $message
	 */
	protected function comment($message)
	{
		$this->notify($message, 'comment');
	}

	/**
	 * Prints a message with standard styling.
	 *
	 * @param string $message
	 */
	protected function line($message)
	{
		$this->notify($message, 'fg=black');
	}

	/**
	 * Prints a message with informative styling.
	 *
	 * @param string $message
	 */
	protected function info($message)
	{
		$this->notify($message);
	}

	/**
	 * Prints a message with note styling.
	 *
	 * @param string $message
	 */
	protected function note($message)
	{
		$this->notify($message, 'bg=cyan');
	}

	/**
	 * Prints a message with tabbed informative styling.
	 *
	 * @param string $message
	 */
	protected function reportInfo($message)
	{
		$this->info("\t{$message}");
	}

	/**
	 * Prints a message with distinctive, tabbed styling.
	 *
	 * @param string $message
	 */
	protected function report($message)
	{
		$this->notify("\t{$message}", 'fg=magenta');
	}

	/**
	 * Runs an extenral process. Reports the output to the buffer.
	 *
	 * @param string $process
	 */
	protected function run($process)
	{
		$process = new Process($process);
		$process->setTimeout(0);

		return $process->run(function ($type, $buffer) {
			$this->report($buffer);
		});
	}

}