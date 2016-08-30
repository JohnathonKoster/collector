<?php

namespace Collector\Utils;

class VersionHistoryManager
{

	protected $splitHistoryFile;

	protected $cachedHistory = [];

	public function __construct()
	{
		$this->splitHistoryFile = __DIR__.'/../storage/cache/tags/split.json';
		$this->cachedHistory = $this->getSplitHistory();
	}

	protected function ensureHistoryExists()
	{
		if (!file_exists($this->splitHistoryFile)) {
			file_put_contents($this->splitHistoryFile, json_encode([]));
		}
	}

	public function addSplitToHistory($splitVersion)
	{
		if (!is_array($splitVersion)) {
			$splitVersion = (array) $splitVersion;
		}

		$history = $this->getSplitHistory();
		$history = array_unique(array_merge($history, $splitVersion));
		file_put_contents($this->splitHistoryFile, json_encode($history));
		$this->cachedHistory = $history;
		return $history;
	}

	public function getSplitHistory()
	{
		$this->ensureHistoryExists();
		$history = json_decode(file_get_contents($this->splitHistoryFile));

		if ($history === null) {
			return [];
		}

		return $history;
	}

	public function existsInHistory($version)
	{
		return in_array($version, $this->cachedHistory);
	}

}