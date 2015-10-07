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
	public static function fetchExec($command) {
		ob_start();
		system($command);
		$res = ob_get_contents();
		ob_end_clean();
		return trim($res);
	}

}
