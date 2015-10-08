<?php

/**
 * все, что связано с консольными запросами
 */
class myConsole {

	/**
	 * получить результат выполнения команды в консоли
	 * 
	 * @param type $command
	 * @return string
	 */
	public static function execCommand($command) {
		return self::fetchExec($command);
	}
	/**
	 * метод через system для execCommand
	 * 
	 * @param type $command
	 * @return type
	 */
	public static function fetchSystem($command) {
		ob_start();
		system($command);
		$res = ob_get_contents();
		ob_end_clean();
		return trim($res);
	}
	/**
	 * метод через exec для execCommand
	 * 
	 * @param type $command
	 * @return type
	 */
	public static function fetchExec($command) {
		$res = array();
		exec($command, $res);
		return $res;
	}
	/**
	 * метод через popen для execCommand
	 * 
	 * @param type $command
	 * @return type
	 */
	public static function fetchPOpen($command) {
		$res = '';
		$fp=popen($command,"r"); 
		while (!feof($fp)) { 
			$res .= fgets($fp, 4096);
		} 
		pclose($fp); 
		return $res;
	}
	
	

}


// $command="asd"; ob_start(); system($command); $res = ob_get_contents(); ob_end_clean(); echo $res;
