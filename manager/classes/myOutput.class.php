<?php

/**
 * класс вывода
 */
class myOutput {

	private static $includes = array();

	/**
	 * отдает JSON
	 * 
	 * @param type $string
	 * @return boolean
	 */
	public static function json($string) {
		echo json_encode($string);
		exit;
	}

	public static function jsonSuccess($data) {
		self::json(array('success' => $data));
	}

	public static function jsonError($message = 'unknown error', $id = -1) {
		self::json(array(
			'error' => array('message' => $message, 'id' => $id)
		));
	}

	/**
	 * echo на экран
	 * 
	 * @param type $string
	 * @return boolean
	 */
	public static function out($html) {
		echo $html;
		return true;
	}

	public static function addCSS($fileName) {
		$webPath = self::getWebPathToModResources() . 'css/';
		$filePath = $webPath . $fileName;
		$item = array('type' => 'css', 'file' => $filePath);
		self::$includes[$filePath] = $item;
	}

	public static function addJS($fileName) {
		$webPath = self::getWebPathToModResources() . 'js/';
		$filePath = $webPath . $fileName;
		$item = array('type' => 'js', 'file' => $filePath);
		self::$includes[$filePath] = $item;
	}

	public static function getAllIncludes() {
		return self::$includes;
	}

	/**
	 * получем путь к ресурсам текущего модуля
	 * 
	 * @param type $modName
	 * @return string
	 */
	public static function getWebPathToModResources($modName = false) {
		$modName = !$modName ? myCore::$currentAction : $modName;
		$webPath = myConfig::get('webPath');
		$path = $webPath . '/mods/' . $modName . '/res/';

		return $path;
	}

	/**
	 * инициализация вывода полной страницы
	 * 
	 * @param type $html
	 * @param type $title
	 * @return boolean
	 */
	public static function outFullHtml($html, $title = false) {
		echo
		self::getHeader($title) .
		$html .
		self::getFooter()
		;
		return true;
	}

	/**
	 * вывод заголовка
	 * 
	 * @param type $title
	 * @return type
	 */
	public static function getHeader($title = false) {

		$title = $title ? $title : 'MyTitle / RomanSh';
		
		if(myCore::$currentAction != ''){
			// подключим некоме меню на внутренних страницах
		}

		return '
			<!DOCTYPE html>
			<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					<title>' . $title . '</title>
					<link type="text/css" rel="stylesheet" href="' . myConfig::get('webPath') . '/res/css/main.css' . '">
				</head>
				<body>
		';
	}

	/**
	 * вывод футера
	 * 
	 * @return string
	 */
	public static function getFooter() {

		$addScripts = '';

		$includes = self::getAllIncludes();

		is_array($includes) ? '' : $includes = array();

		foreach ($includes as $inc) {
			$link = $inc['file'];
			if ($inc['type'] == 'css') {
				$addScripts .= '<link type="text/css" rel="stylesheet" href="' . $link . '">';
			} elseif ($inc['type'] == 'js') {
				$addScripts .= '<script type="text/javascript" src="' . $link . '"></script>';
			}
		}

		return '
					<!-- footer/ -->
					<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
					<script type="text/javascript">/*Global JS Vars*/var G = {}; G.moduleUrl = "' . myRoute::getRoute(myCore::$currentAction) . '";</script>
					' . $addScripts . '
				</body>
			</html>
		';
	}

}
