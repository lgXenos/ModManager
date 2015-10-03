<?php

/**
 * класс для работы с гитовыми командами
 */
class gitActionModel {

	public $gitDir = '.';
	
	// hard-coding
	public function __construct() {
		if(file_exists('/opt/var/www/instant')){
			$this->gitDir = '/opt/var/www/instant';
		}
		else {
			$this->gitDir = '/var/www/html';
		}
	}

	/**
	 * получить данные для заглавной страницы
	 * 
	 * @return type
	 */
	public function getIndexData() {
		$branches = $this->getBranches();
		$lastChanges = $this->getStatus();

		return [
			'branches' => $branches,
			'status' => $lastChanges,
		];
	}
	
	public function makeCommit(){
		return true;
	}

	/**
	 * получить массив с локальными и удаленными ветками
	 * 
	 * @return boolean
	 */
	private function getBranches() {
		$ret = [];

		$locals = explode("\n", $this->fetchGitCommand('git branch -l'));
		foreach ($locals as $ficha) {

			if (strpos($ficha, '*') === 0) {
				$ficha = substr($ficha, 1);
				$ficha = trim($ficha);
				$ret[$ficha]['current'] = 1;
			}

			$ficha = trim($ficha);
			$num = true;
			$_r = false;
			if (preg_match('/^([\d]+)/', $ficha, $_r)) {
				$num = $_r[0];
			}

			$ret[$ficha]['local'] = $num;
		}
		$remotes = explode("\n", $this->fetchGitCommand('git branch -r'));
		foreach ($remotes as $ficha) {

			$ficha = str_replace('origin/', '', $ficha);
			$ficha = trim($ficha);
			$num = true;
			if (preg_match('/^([\d]+)/', $ficha, $_r)) {
				$num = $_r[0];
			}

			$ret[$ficha]['remote'] = $num;
		}

		return $ret;
	}

	/**
	 * получить массив строчек от git status
	 * 
	 * @return type
	 */
	private function getStatus() {
		$ret = [];
		$status = explode("\n", $this->fetchGitCommand('git status -s --no-column'));
		foreach ($status as $file) {
			$ret[] = $file;
		}

		return $ret;
	}

	private function fetchGitCommand($str) {
		$cd = 'cd ' . $this->gitDir . '; ';
		return myConsole::fetchExec($cd . $str);
	}

}
