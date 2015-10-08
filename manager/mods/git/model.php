<?php

/**
 * класс для работы с гитовыми командами
 */
class gitActionModel {

	public $gitDir = '.';

	// hard-coding
	public function __construct() {
		if (file_exists('/opt/var/www/instant')) {
			$this->gitDir = '/opt/var/www/instant';
		} else {
			$this->gitDir = '/var/www/mys';
		}
	}

	/**
	 * получить данные для заглавной страницы
	 * 
	 * @return type
	 */
	public function getIndexData() {
		
		if(!$this->checkGitPHPAvailability()){
			myCore::redirectToUrl( myRoute::getRoute('git', 'unavailability') );
			exit;
		}
		
		$branches = $this->getBranches();
		$lastChanges = $this->getStatus();

		return array(
			'branches' => $branches,
			'status' => $lastChanges,
		);
	}

	/**
	 * комитимся
	 * 
	 * @return type
	 */
	public function makeCommit($text) {
		$res = array();
		$this->appendFetchGitCommand($res, 'git commit -am "'.$text.'"', true);
		$this->appendFetchGitCommand($res, 'git status', true);
		return $res;
	}

	/**
	 * получить массив с локальными и удаленными ветками
	 * 
	 * @return boolean
	 */
	private function getBranches() {
		$ret = array();

		$locals = $this->fetchGitCommand('git branch -l');
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
		$remotes = $this->fetchGitCommand('git branch -r');
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
	 * @param type $withCommand - вернуть с выполненной командой
	 * 
	 * @return type
	 */
	private function getStatus($withCommand = false) {
		$ret = array();
		$status = $this->fetchGitCommand('git status -s --no-column', $withCommand);
		foreach ($status as $file) {
			$ret[] = $file;
		}

		return $ret;
	}

	/**
	 * 
	 * @param type $str
	 * @param type $withCommand - вернуть с выполненной командой
	 * 
	 * @return array
	 */
	private function fetchGitCommand($str, $withCommand = false) {
		
		// текущая
		$currDir = getcwd();
		// рабочая
		chdir($this->gitDir);

		$res = myConsole::execCommand($str . ';');
		
		if(is_string($res)){
			if ($res != '') {
				$res = explode("\n", $res);
			} else {
				$res = array();
			}
		}

		if ($withCommand) {
			array_unshift($res, '', '~$ ' . $str);
		}

		// текущая
		chdir($currDir);

		return $res;
	}

	/**
	 * добавляет результат команды к переданному массиву
	 * 
	 * @param type $res
	 * @param type $str
	 * @param type $withCommand
	 */
	private function appendFetchGitCommand(&$res, $str, $withCommand = false) {
		$ret = $this->fetchGitCommand($str, $withCommand);
		$res = myTools::arraysUnionWithoutIndex($res, $ret);
	}

	/**
	 * если забыли добавить разделы пользователя и emaila
	 * 
	 * @param type $count
	 * @return boolean
	 */
	private function checkGitPHPAvailability($count = 0){
		// проверка на доступность 
		// делаю через commit, т.к. более показательно
		$status = $this->fetchGitCommand('git commit');
		// если ничего не получили - значит некий сбой. может - нет данных о себе
		if( !count($status) ){
			return false;
		}
		/**
		 * @todo зашить в файл
		 */
		
		return true;
	}
}