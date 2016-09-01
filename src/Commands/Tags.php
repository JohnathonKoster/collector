<?php

namespace Collector\Commands;

use Collector\Utils\GitHub\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Tags extends Command
{

	protected $tagManager;

	public function __construct()
	{
		parent::__construct();
		$this->tagManager = Factory::makeGitHubTagManager();
	}

	protected function configure()
	{
		$this->setName('collect:tags')
			 ->setDescription('Gets the Laravel Framework releases.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$tags = $this->tagManager->getCacheTags();
		
		$output->writeln('<info>Collected '.count($tags).' tags.</info>');

	}

}