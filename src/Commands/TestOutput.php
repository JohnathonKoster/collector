<?php

namespace Collector\Commands;

use Collector\Utils\Tests\Runner;
use Collector\Utils\VersionHistoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestOutput extends Command
{

	protected $runner;

	protected $history;

	public function __construct()
	{
		parent::__construct();
		$this->runner  = new Runner;
		$this->history = new VersionHistoryManager;
		$this->history->load(__DIR__.'/../../storage/cache/tags/split.json');
	}

	protected function configure()
	{
		$this->setName('test:output')
			 ->setDescription('Runs the unit tests on the splitter output');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($input->getOption('verbose')) {
			$this->runner->setNotifiers(function($message, $type) use ($output) {
				$output->writeln("<{$type}>{$message}</{$type}>");
			});
		}

		foreach ($this->history->getSplitHistory() as $splitVersion) {
			$this->runner->runTestsOn($splitVersion);
		}
	}

}