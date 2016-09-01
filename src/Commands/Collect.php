<?php

namespace Collector\Commands;

use Collector\Splitter;
use Collector\Utils\GitHub\Factory;
use Collector\Utils\VersionHistoryManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class Collect extends Command
{

	protected $splitter;

	protected $tagManager;

	protected $history;

	public function __construct()
	{
		parent::__construct();
		$this->splitter   = new Splitter;
		$this->tagManager = Factory::makeGitHubTagManager();
		$this->history    = new VersionHistoryManager;
		$this->history->load(__DIR__.'/../../storage/cache/tags/split.json');
	}

	protected function configure()
	{
		$this->setName('collect')
			 ->setDescription('Splits the Collection source from the Laravel code-base')
			 ->addOption('git', 'g', InputOption::VALUE_NONE, 'Do the git stuff?')
			 ->addOption('catchup', 'c', InputOption::VALUE_NONE, 'Only checkout versions not split')
			 ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the split process when in automatic mode');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->splitter->setNotifiers(function($message, $type) use ($output) {
			$output->writeln("<{$type}>{$message}</{$type}>");
		});

		$this->splitter->shouldSkipGitOperators(!$input->getOption('git'));
		$this->splitter->onlySupportNewGitOperations($input->getOption('catchup'));
		$this->splitter->forceSplit($input->getOption('force'));
		

		if (config('split.mode') == 'manual') {
			$output->writeln("Starting splitter in manual mode...\n");
			$versionsToSplit = config('split.versions');
			$this->splitter->split($versionsToSplit);
		} else {
			$output->writeln("Starting splitter in automatic mode...\n");
			$tagsAfterConfiguredStartTag = $this->tagManager->getTagsAfter(config('split.start_with'));
			$history = $this->history->getSplitHistory();

			if ($input->getOption('force')) {
				$versionsToSplit = $history;
			} else {
				$versionsToSplit = array_diff($tagsAfterConfiguredStartTag, $history);
			}

			// This will set the output directory name to the same the source directory name.
			$versionsToSplit = array_combine(array_values($versionsToSplit), array_values($versionsToSplit));

			if (count($versionsToSplit) > 0) {
				$output->writeln("There are ".count($versionsToSplit)." versions to automatically split.\n");
				$this->splitter->split($versionsToSplit);
			} else {
				$output->writeln('No new versions to split. If you believe this is an error, try removing the remote tag cache and running this command again.');
			}

		}

	}

}