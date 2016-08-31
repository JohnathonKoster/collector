<?php

namespace Collector\Utils;

class VersionHistoryManager
{

	/**
	 * The path to the version history file.
	 * 
	 * @var string
	 */
	protected $splitHistoryFile;

	/**
	 * The cached history items.
	 * 
	 * @var array
	 */
	protected $cachedHistory = [];

	public function __construct()
	{
		$this->splitHistoryFile = __DIR__.'/../storage/cache/tags/split.json';
		$this->cachedHistory = $this->getSplitHistory();
	}

	/**
	 * Creates the history file if it doesn't exit.
	 * 
	 */
	protected function ensureHistoryExists()
	{
		if (! file_exists($this->splitHistoryFile)) {
			file_put_contents($this->splitHistoryFile, json_encode([]));
		}
	}

	/**
	 * Adds a version to the split history.
	 * 
	 * @param  string $splitVersion
	 *
	 * @return array
	 */
	public function addSplitToHistory($splitVersion)
	{
		if (! is_array($splitVersion)) {
			$splitVersion = (array) $splitVersion;
		}

		$history = $this->getSplitHistory();
		$history = array_unique(array_merge($history, $splitVersion));
		file_put_contents($this->splitHistoryFile, json_encode($history));
		$this->cachedHistory = $history;
		return $history;
	}

	/**
	 * Adds a version to the split history.
	 * 
	 * @param  string $splitVersion
	 *
	 * @return array
	 */
	public function add($splitVersion)
	{
		return $this->addSplitToHistory($splitVersion);
	}

	/**
	 * Gets the split version history.
	 * 
	 * @return array
	 */
	public function getSplitHistory()
	{
		$this->ensureHistoryExists();
		$history = json_decode(file_get_contents($this->splitHistoryFile));

		if ($history === null) {
			return [];
		}

		return $history;
	}

	/**
	 * Determines if a version exists in history.
	 * 
	 * @param  string $version
	 * 
	 * @return bool
	 */
	public function existsInHistory($version)
	{
		return in_array($version, $this->cachedHistory);
	}

	/**
	 * Determines if a version exists in history.
	 * 
	 * @param  string $version
	 * 
	 * @return bool
	 */
	public function has($version)
	{
		return $this->existsInHistory($version);
	}

}