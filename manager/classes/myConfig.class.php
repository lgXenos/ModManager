<?php

class myConfig {

	private static $cfg = false;
	private static $instance = false;

	/**
	 * singleton
	 * 
	 * @return class instance
	 */
	public static function getInstance($fullPath) {
		if (!(self::$instance instanceof self)) {
			self::$instance = new self;
			if (!file_exists($fullPath)) {
				throw new Exception('"fullPath" is not valid');
			}
			self::$cfg = array('fullPath' => $fullPath);
		}

		return self::$instance;
	}

	/**
	 * 
	 * @param str/arr $cfg		- или строка или массив значений
	 * @param type $val
	 * @return boolean
	 * @throws Exception
	 */
	public static function set($cfg, $val = null) {

		self::checkForValidCfg();

		if (!is_array($cfg)) {
			$cfg = array($cfg => $val);
		}

		foreach ($cfg as $name => $value) {
			if ($name == 'fullPath') {
				continue;
			}
			self::$cfg[$name] = $value;
		}
	}

	/**
	 * получить переменную конфигурации
	 * 
	 * изначально доступны <br>
	 * * modManagerName <br>
	 * * fullPath <br>
	 * * webPath <br>
	 * * modsPath<br>
	 * 
	 * @param type $name
	 * @throws Exception
	 * 
	 */
	public static function get($name) {

		self::checkForValidCfg();

		if (isset(self::$cfg[$name])) {
			return self::$cfg[$name];
		} else {
			return null;
		}
	}

	/**
	 * получить весь конфиг
	 * 
	 * @return arr
	 */
	public static function getAllConfig() {

		self::checkForValidCfg();

		return self::$cfg;
	}

	private static function checkForValidCfg() {
		// если не установлена конфигурация при инициализированном классе
		if (!self::$cfg && (self::$instance instanceof self)) {
			throw new Exception('Config not defined');
		}
	}

	private function __construct() {
		
	}

	private function __clone() {
		
	}

	private function __wakeup() {
		
	}

}
