<?php

/**
 * все, что связано с консольными запросами
 */
class myConsole {

	/**
	 * читаем последние строчки из файла
	 * 
	 * @param string $file		-	файл для чтения последний строчек
	 * @param int $linesCnt		-	сколько линий "откусывать" с конца
	 * @return array/false		-	или массив или false, если прочесть не удалось
	 */
	public static function readLastXLinesFromFile($file, $linesCnt = 10) {

		// проверим доступность чтения из файла
		// Переменная \$? содержит статус с которым завершилась последняя команда. 
		// В нашем случае код отличный от 0 обозначает, что произошла ошибка.

		$cmd = "test -r {$file}; echo $?";
		$haveErrors = intval(myConsole::execCommand($cmd, true));
		if ($haveErrors) {
			return false;
		}

		// читаем файл
		$cmd = 'tail -n ' . intval($linesCnt) . ' ' . $file . ';';
		return myConsole::execCommand($cmd);
	}

	/**
	 * получить результат выполнения команды в консоли
	 * 
	 * @param type $command
	 * @return string
	 */
	public static function execCommand($command, $toString = false) {
		return self::fetchShellExec($command, $toString);
	}

	/**
	 * метод через exec для execCommand
	 * 
	 * @param type $command
	 * @return type
	 */
	public static function fetchShellExec($command, $toString = false) {

		$res = rtrim(shell_exec($command));
		if (!$res) {
			$res = '';
		}
		// по умолчанию у нас строчка. 
		if ($toString) {
			return $res;
		}
		// если хотели массив - перегоняем: по сути - делаем вывод такой же, как в обычном exec
		// если пустая строка - пустой массив
		if ($res == '') {
			$res = array();
		}
		// иначе - полноценный массив
		else {
			$res = explode("\n", $res);
		}
		
		return $res;
	}

	/**
	 * запасные варианты чтения из консоли
	 */

	/**
	 * метод через exec для execCommand
	 * 
	 * @param type $command
	 * @return type
	 */
	public static function fetchExec($command, $toString = false) {
		$res = array();
		exec($command, $res);
		if ($toString) {
			$res = implode("\n", $res);
		}
		myDebug::iout($command, 'TO:: ', $res);
		return $res;
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
	 * метод через popen для execCommand
	 * 
	 * @param type $command
	 * @return type
	 */
	public static function fetchPOpen($command) {
		$res = '';
		$fp = popen($command, "r");
		while (!feof($fp)) {
			$res .= fgets($fp, 4096);
		}
		pclose($fp);
		return $res;
	}

}
