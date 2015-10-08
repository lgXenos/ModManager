<?php

class myCore {

	private static $instance = false;
	public static $currentAction = '';

	/**
	 * singleton
	 * 
	 * @return class instance
	 */
	public static function getInstance() {
		if (!(self::$instance instanceof self)) {
			self::$instance = new self;
			self::$currentAction = myRoute::getActionAndParseCurrentURI();
		}

		return self::$instance;
	}

	/**
	 * список необходимых файлов для корректной работы с модом
	 * 
	 * @return type 
	 */
	public static function getRequiredListComponentsForMod() {
		return array('controller.php', 'model.php', 'view.php');
	}

	/**
	 * парсим INI-файл мода и возвращаем что нашли
	 * 
	 * @param type $fullModPath
	 * @return type
	 */
	public static function readModIniFile($fullModPath) {

		$iniArray = false;
		$_iniFilePath = $fullModPath . '/info.ini';
		if (file_exists($_iniFilePath)) {
			$iniArray = parse_ini_file($_iniFilePath, true);
		}

		return $iniArray;
	}

	/**
	 * валидация аддона тем, что есть минимально необходимые файлы
	 * 
	 * @param type $fullModPath
	 * @return boolean
	 */
	public static function validateMod($fullModPath) {

		$isValid = true;
		foreach (self::getRequiredListComponentsForMod() as $component) {
			$checkingFile = $fullModPath . '/' . $component;
			if (!file_exists($checkingFile)) {
				$isValid = false;
				break;
			}
		}

		return $isValid;
	}

	/**
	 * считать инфу о моде
	 * 
	 * @param type $ret
	 * @param type $entry
	 * @param type $readIniFile
	 * @return boolean
	 */
	public static function getModInfo(&$ret, $entry, $readIniFile = false) {

		$modsPath = myConfig::get('modsPath');
		$fullModPath = $modsPath . '/' . $entry;
		if (!is_dir($fullModPath)) {
			return false;
		}

		$title = $entry;

		// считывание конфига для мода + его тайтла
		$iniArray = $readIniFile ? self::readModIniFile($fullModPath) : false;
		if (is_array($iniArray) && isset($iniArray['info']['title'])) {
			$title = $iniArray['info']['title'];
		}


		$ret[$entry] = array(
			'path' => $fullModPath,
			'ini' => $iniArray,
			'title' => $title,
			'name' => $entry,
			'isValid' => self::validateMod($fullModPath)
		);
	}

	/**
	 * модули, доступные для обработки
	 * 
	 * @param type $modName
	 * @param type $readIniFile
	 * @return array
	 */
	public static function getModsList($modName = false, $readIniFile = false) {

		$ret = array();
		$modsPath = myConfig::get('modsPath');
		$d = array_diff(scandir($modsPath), array('..', '.'));

		foreach ($d as $entry) {
			// читаем или если это запрос на все или если нашли один искомый
			if (
					(!$modName) ||
					($modName && $entry == $modName)
			) {
				self::getModInfo($ret, $entry, $readIniFile);
			}
		}

		return $ret;
	}

	/**
	 * попытка подинклудить мод в систему для использования
	 * 
	 * @param type $modName
	 * @return boolean
	 */
	public static function tryIncludeMod($modName) {

		$ret = array();
		self::getModInfo($ret, $modName);

		if (isset($ret[$modName]) && $ret[$modName]['isValid']) {

			// подключаем стандартные компоненты приложения
			foreach (self::getRequiredListComponentsForMod() as $component) {
				include_once $ret[$modName]['path'] . '/' . $component;
			}

			return true;
		} else {
			return false;
		}
	}

	public static function render404() {
		header("HTTP/1.0 404 Not Found");
		myOutput::out('<h1>Page not found</h1><h3>Server response code 404</h3>');
		myOutput::out('<h3><a href="/">To Home /</a></h3>');
		exit();
	}
	
	public static function redirectToUrl($url) {
		header("Location: $url");
		exit;
	}

	private function __construct() {
		
	}

	private function __clone() {
		
	}

	private function __wakeup() {
		
	}

}
