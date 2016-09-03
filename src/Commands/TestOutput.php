<?php

namespace Collector\Commands;

use Collector\Utils\Tests\Runner;
use Collector\Utils\VersionHistoryManager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
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
		$history = $this->history->getSplitHistory();
		$data = [];
		$progressBar = new ProgressBar($output, count($history));
		$progressBar->setFormat("%message%\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%");
		$progressBar->setMessage('Building your report...');
		foreach ($history as $splitVersion) {
			$progressBar->setMessage('Running tests on '.$splitVersion.'...');
			$code = $this->runner->runTestsOn($splitVersion);
			$message = 'Passed';

			if ($code !== 0) {
				$message = 'Failed';
			}

			$data[] = [$splitVersion, $message];
			$progressBar->setMessage('Running tests on '.$splitVersion.'... '.$message);
			$progressBar->advance();
		}
		$progressBar->finish();

		$table = new Table($output);
		$table->setHeaders(['Version', 'Passed'])
		->setRows($data)->render();
	}

}