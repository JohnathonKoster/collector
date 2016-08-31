<?php

namespace Collector\Utils;

use Exception;

class VersionHistoryManager
{

	/**
	 * The path to the version history file.
	 * 
	 * @var string
	 */
	protected $splitHistoryFile = null;

	/**
	 * The cached history items.
	 * 
	 * @var array
	 */
	protected $cachedHistory = [];

	/**
	 * Loads the history file at the given path.
	 * 
	 * @param  string $pathToHistory
	 * 
	 * @return array
	 */
	public function load($pathToHistory)
	{
		$this->splitHistoryFile = $pathToHistory;
		$this->cachedHistory    = $this->getSplitHistory();

		return $this->cachedHistory;
	}

	/**
	 * Creates the history file if it doesn't exit.
	 * 
	 */
	protected function ensureHistoryExists()
	{
		if ($this->splitHistoryFile === null) {
			throw new Exception('Cannot interact with version history without first loading history file.');
		}

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
		$this->ensureHistoryExists();

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
		$this->ensureHistoryExists();
		
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