<?php

namespace Collector\Commands;

use Collector\Splitter;
use Collector\Utils\Helpers\Collector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectHelpers extends Command
{

	protected $collector;

	public function __construct()
	{
		parent::__construct();
		$this->collector = new Collector;
	}

	protected function configure()
	{
		$this->setName('collect:helpers')
			 ->setDescription('Pulls the helper files from the Laravel code-base.')
			 ->addArgument('remote', InputArgument::REQUIRED, 'The remote branch name')
			 ->addArgument('local', InputArgument::REQUIRED, 'The name of the destination path.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->collector->setNotifiers(function($message, $type) use ($output) {
			$output->writeln("<{$type}>{$message}</{$type}>");
		});

		
		$remote = $input->getArgument('remote');
		$local  = $input->getArgument('local');

		$this->collector->collectHelpers($remote, $local);
	}

}