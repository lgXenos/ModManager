<?php

/**
 * класс для работы с гитовыми командами
 */
class gitActionModel {

	// массив доступных репозиториев
	public $gitReps = array();
	// активный элемент массива
	public $currentGitRep = false;
	// непосредственно путь к текущему репу
	public $gitDir = '.';
	// имя все опции из custom.ini, кроме репозиториев
	private $customOptions = false;
	// имя куки
	public $currentGitRepCoockieName = "currentGitRep";

	// hard-coding
	public function __construct() {
		$this->initGitReps();
	}

	/**
	 * подгружает доступные репы и заполняет переменные
	 * - $this->gitDir
	 * - $this->currentGitRep
	 */
	private function initGitReps() {
		$reps = array();

		$iniArray = myCore::readModIniFile(false, 'custom');
		if (!is_array($iniArray) || !isset($iniArray['repositories'])) {
			var_dump($iniArray);
			exit('Error: copy <u>/manager/mods/git/custom.ini.sample</u> to *.ini and refresh page.');
		}

		foreach ($iniArray['repositories'] as $repData) {
			$reps[] = array('name' => $repData['name'], 'path' => $repData['path']);
		}
		unset($iniArray['repositories']);

		// отберем валидные из списка
		foreach ($reps as $rep) {
			$repPath = $rep['path'];
			if (file_exists($repPath)) {
				$this->gitReps[$repPath] = $rep;
			}
		}
		// по умолчанию - активный первый
		$currentRep = current($this->gitReps);

		// если есть кука текущего репа - поискать такой реп
		if (isset($_COOKIE[$this->currentGitRepCoockieName])) {

			$cookRepName = $_COOKIE[$this->currentGitRepCoockieName];
			if (isset($this->gitReps[$cookRepName])) {
				$currentRep = $this->gitReps[$cookRepName];
				$this->gitReps[$cookRepName]['active'] = 1;
			}
		}

		$this->gitDir = $currentRep['path'];
		$this->currentGitRep = $currentRep;
		$this->customOptions = $iniArray;
	}

	/**
	 * получить данные для заглавной страницы
	 * 
	 * @return type
	 */
	public function getIndexData() {

		$this->checkGitPHPAvailability();

		$branches = $this->getBranches();
		$lastChanges = $this->getStatus();
		$this->appendFetchGitCommand($lastChanges, 'git stash list');

		return array(
			'branches' => $branches,
			'status' => $lastChanges,
			'reps' => $this->gitReps,
		);
	}

	/**
	 * добавялем новый бранч из состояния текущего
	 * 
	 * @return type
	 */
	public function addNewBranch($branchName) {

		// уберем пробелы из имени ветки
		$branchName ? $branchName = str_replace(' ', '_', $branchName) : '';

		$branches = $this->getBranches();
		$lastChanges = $this->getStatus();

		// проверим, чтоб все было комиченным
		$currStatus = $this->fetchGitCommand('git status -s');
		if (!is_array($currStatus) || count($currStatus) > 0) {
			$lastChanges[] = 'ERROR$ You must commit your changes first';
		}
		// проверим, чтоб такого бранча не было
		elseif (isset($branches[$branchName]) AND isset($branches[$branchName]['local'])) {
			$lastChanges[] = 'ERROR$ A branch named "' . $branchName . '" already exists.';
		}
		else {
			$this->appendFetchGitCommand($lastChanges, 'git checkout -b ' . $branchName, true);
			$branches = $this->getBranches();
			$this->appendFetchGitCommand($lastChanges, 'git status -s --no-column', true);
		}
		$this->fixFilesPermissionsOnGitRoot();


		return array(
			'branches' => $branches,
			'status' => $lastChanges,
			'reps' => $this->gitReps,
		);
	}

	/**
	 * получить данные для заглавной страницы
	 * 
	 * @return type
	 */
	public function getUpdateRemotesStatus() {

		$lastChanges = array();
		$this->appendFetchGitCommand($lastChanges, 'git remote update', true);
		$this->appendFetchGitCommand($lastChanges, 'git remote prune origin', true);
		$this->appendFetchGitCommand($lastChanges, 'git status -s --no-column', true);
		$branches = $this->getBranches();

		return array(
			'branches' => $branches,
			'status' => $lastChanges,
			'reps' => $this->gitReps,
		);
	}

	/**
	 * комитимся
	 * 
	 * @return type
	 */
	public function makeCommit($text) {
		$res = array();
		$this->appendFetchGitCommand($res, 'git add . && git commit -am "' . $text . '"', true);
		$this->appendFetchGitCommand($res, 'git status', true);
		return $res;
	}

	/**
	 * пушим себя в свою ветку
	 * 
	 * @return type
	 */
	public function pushSelf() {
		$res = array();
		// current получаем из branches
		$current = $this->getBranches(true);
		if (!is_string($current)) {
			return false;
		}
		// проверим, чтоб все было комиченным
		$currStatus = $this->fetchGitCommand('git status -s');
		if (!is_array($currStatus) || count($currStatus) > 0) {
			$res[] = 'ERROR$ You must commit your changes first';
		}
		else {
			$this->appendFetchGitCommand($res, 'git pull origin ' . $current, true);
			$this->appendFetchGitCommand($res, 'git push origin ' . $current, true);
			$this->appendFetchGitCommand($res, 'git log origin/' . $current . "   --format='%ai' -1", true);
			$this->fixFilesPermissionsOnGitRoot();
			/**
			 * @todo обновить список бранчей
			 * $this->getBranches(true);
			 */
		}
		return $res;
	}

	/**
	 * получить массив с локальными и удаленными ветками
	 * 
	 * @param type $getOnlyCurrent	-	получить только имя текущей
	 * @return boolean
	 */
	private function getBranches($getOnlyCurrent = false) {
		$ret = array();

		$locals = $this->fetchGitCommand('git branch -l');
		foreach ($locals as $ficha) {

			if (strpos($ficha, '*') === 0) {
				$ficha = substr($ficha, 1);
				$ficha = trim($ficha);
				if ($getOnlyCurrent) {
					return $ficha;
				}
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
		$this->appendFetchGitCommand($ret, 'git status -s --no-column', $withCommand);
		if (count($ret) == 0) {
			$lastDate = current($this->fetchGitCommand("git log  --format='%ai' -1"));
			$ret[] = '~$ already up to date ' . $lastDate;
		}

		return $ret;
	}

	/**
	 * пробует спулится 
	 * 
	 * @param type $branchName
	 */
	public function pullOrigin($branchName) {

		// проверим, чтоб все было комиченным
		$currStatus = $this->fetchGitCommand('git status -s');
		if (!is_array($currStatus) || count($currStatus) > 0) {
			return myCore::returnErrorArray('You must commit your changes first');
		}

		$ret = array();
		$this->appendFetchGitCommand($ret, 'git pull --commit origin ' . $branchName, true);

		$this->fixFilesPermissionsOnGitRoot();

		return $ret;
	}

	/**
	 * пробует смержиться 
	 * 
	 * @param type $branchName
	 */
	public function mergeLocal($branchName) {

		// проверим, чтоб все было комиченным
		$currStatus = $this->fetchGitCommand('git status -s');
		if (!is_array($currStatus) || count($currStatus) > 0) {
			return myCore::returnErrorArray('You must commit your changes first');
		}

		$ret = array();
		$this->appendFetchGitCommand($ret, 'git merge --commit ' . $branchName, true);

		$this->fixFilesPermissionsOnGitRoot();

		return $ret;
	}

	/**
	 * получаем список отложенных сташей
	 */
	public function getStashList() {

		$ret = array();
		$this->appendFetchGitCommand($ret, 'git stash list', true);

		return $ret;
	}

	/**
	 * откладываем текущие грязные правки
	 */
	public function getStashSave() {

		// проверим, чтоб был лишь 1 отложенный
		$currStatus = $this->fetchGitCommand('git stash list');
		if (!is_array($currStatus) || count($currStatus) > 0) {
			return myCore::returnErrorArray('More than 1 active stash not supported. Apply them or clear, Than try again.');
		}

		$ret = array();
		$this->appendFetchGitCommand($ret, 'git stash save', true);
		$this->appendFetchGitCommand($ret, 'git stash list', true);
		$this->fixFilesPermissionsOnGitRoot();

		return $ret;
	}

	/**
	 * применяем и удаляем последние грязные правки
	 */
	public function getStashPop() {

		$ret = array();
		$this->appendFetchGitCommand($ret, 'git stash pop', true);
		$this->appendFetchGitCommand($ret, 'git stash list', true);
		$this->appendFetchGitCommand($ret, 'git status -s', true);
		$this->fixFilesPermissionsOnGitRoot();

		return $ret;
	}

	/**
	 * прибиваем все грязные отложенности
	 */
	public function getStashClear() {

		$ret = array();
		$this->appendFetchGitCommand($ret, 'git stash clear', true);
		$this->appendFetchGitCommand($ret, 'git log -1', true);
		$this->appendFetchGitCommand($ret, 'git status -s', true);
		$this->fixFilesPermissionsOnGitRoot();

		return $ret;
	}

	/**
	 * меняем ветку
	 * 
	 * @param type $branchName
	 */
	public function checkoutBranch($branchName) {
		$ret = array();
		!($branchName) ? $ret[] = 'ERROR$ no branchName' : $this->appendFetchGitCommand($ret, 'git checkout ' . $branchName, true);
		$this->appendFetchGitCommand($ret, 'git status -s --no-column', true);
		$branches = $this->getBranches();
		$this->fixFilesPermissionsOnGitRoot();

		return array(
			'branches' => $branches,
			'status' => $ret,
			'reps' => $this->gitReps,
		);
	}

	/**
	 * прибиваем удаленную
	 * 
	 * @param type $branchName
	 */
	public function deleteRemote($branchName) {
		$ret = array();
		!($branchName) ? $ret[] = 'ERROR$ no branchName' : $this->appendFetchGitCommand($ret, 'git push origin :' . $branchName, true);
		$this->appendFetchGitCommand($ret, 'git status -s --no-column', true);
		$branches = $this->getBranches();

		return array(
			'branches' => $branches,
			'status' => $ret,
			'reps' => $this->gitReps,
		);
	}

	/**
	 * прибиваем удаленную
	 * 
	 * @param type $branchName
	 */
	public function deleteLocal($branchName) {
		$ret = array();
		$branches = $this->getBranches();
		// проверим, чтоб бранч был, и мы не были в нем
		if (isset($branches[$branchName]) AND ! isset($branches[$branchName]['current'])) {
			$this->appendFetchGitCommand($ret, 'git branch -D ' . $branchName, true);
		}
		else {
			if (!$branchName) {
				$ret[] = 'ERROR$ no branchName';
			}
			elseif (!isset($branches[$branchName])) {
				$ret[] = 'ERROR$ branch is not exists';
			}
			elseif (isset($branches[$branchName]['current'])) {
				$ret[] = 'ERROR$ branch is current';
			}
		}
		$this->appendFetchGitCommand($ret, 'git status -s --no-column', true);

		$branches = $this->getBranches();

		return array(
			'branches' => $branches,
			'status' => $ret,
			'reps' => $this->gitReps,
		);
	}

	/**
	 * сменить текущий репозиторий через проставление куки
	 * 
	 * @param type $repName
	 */
	public function changeRepository($repName) {
		setcookie($this->currentGitRepCoockieName, $repName, time() + 3600 * 24 * 31); /* период действия - 1 месяц */
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

		if (is_string($res)) {
			if ($res != '') {
				$res = explode("\n", $res);
			}
			else {
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
	 * - если забыли добавить разделы пользователя и emaila
	 * - если "наделали" файлов от своего юзера
	 * 
	 * @return boolean
	 */
	private function checkGitPHPAvailability($path = '/.git', $isAjax = false) {
		// проверка на доступность 
		$status = $this->fetchGitCommand('git log -1');
		// если ничего не получили - значит некий сбой. может - нет данных о себе
		if (!count($status)) {
			//return false;
			myCore::redirectToUrl(myRoute::getRoute('git', 'unavailability'));
			exit;
		}

		// проверка на утраченного владельца
		if ($this->customOptions['check to losted owner']['check'] == '1') {
			$wrongOwners = $this->customOptions['check to losted owner']['wrong_owners'];
			$warnings = '';
			foreach ($wrongOwners as $user) {
				$cmd = 'find ' . $this->gitDir . $path . ' -user ' . $user . ' ';
				$status = $this->fetchGitCommand($cmd);
				if (count($status)) {
					$warnings .= '<h3 data-cmd="' . $cmd . '">WARNING: found ' . count($status) . ' files from user ' . $user . '</h3>';
				}
			}
			/**
			 * @todo заюзать view мода/ядра
			 */
			if ($warnings != '') {
				$warnings = '<div class="gitWarnings">' . $warnings . '</div>' .
						'<div class="gitWarnSugest">sudo chown www-data ' . $this->gitDir . '/.* -R</div>';
				if ($isAjax) {
					$warnings = str_replace('</', "\n</", $warnings);
					$warnings = $cmd . "\n" . strip_tags($warnings);
					return explode("\n", $warnings);
				}
				else {
					echo $warnings;
				}
			}
		}

		return true;
	}

	/**
	 * проверить, чтоб на всех файлах не было лишних владельцев
	 */
	public function checkErrorsOwners() {
		$res = $this->checkGitPHPAvailability('/', true);
		if ($res === true) {
			$res = array(
				'find ' . $this->gitDir . '/ -user [fake_user] : Ok'
			);
		}

		return $res;
	}
	
	/**
	 * вывести последние логи
	 */
	public function gitLog() {
		$ret = array();
		$this->appendFetchGitCommand($ret, 'git log -3', true);
		return $ret;
	}

	/**
	 *  возникла проблема, что после пула-мержа пермишены становятся как
	 * www-data / www-data /rw- r-- r--
	 * 
	 * :(
	 * 
	 */
	public function fixFilesPermissionsOnGitRoot() {
		myConsole::execCommand('chmod -R g=rwx,u=rwx ' . $this->gitDir . '/.*');
	}

	/**
	 * читаем последние строчки 
	 * 
	 * @param int $linesCnt		-	сколько линий "откусывать" с конца
	 * @param string $path		-	файл для чтения последний строчек
	 * @return string
	 */
	public function readApacheErrorLog($linesCnt = 10) {

		$fileName = '/var/log/apache2/error.log';

		$fileContent = 'Notice: file ' . $fileName . ' is not readable!'
				. ' You may set read(r) and execute(x) permissions for parent folder.';

		$result = myConsole::readLastXLinesFromFile($fileName, $linesCnt);

		if (is_array($result)) {
			$fileContent = implode("\n", $result);
		}

		return $fileContent;
	}

}
