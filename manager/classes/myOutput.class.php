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
	 * @param type $html
	 * @return boolean
	 */
	public static function out($html) {
		echo $html;
		return true;
	}

	/**
	 * добавляет к подключению файл CSS для текущего модуля
	 * 
	 * @param type $fileName
	 * @param type $isRelativeToCurrentMod	- при true отменяет привязку к модулю
	 */
	public static function addCSS($fileName, $isRelativeToCurrentMod = true) {
		if($isRelativeToCurrentMod){
			$webPath = self::getWebPathToModResources() . 'css/';
			$filePath = $webPath . $fileName;
		}
		$item = array('type' => 'css', 'file' => $filePath);
		self::$includes[$filePath] = $item;
	}

	/**
	 * добавляет к подключению файл JS для текущего модуля
	 * 
	 * @param type $fileName
	 * @param type $isRelativeToCurrentMod	- при true отменяет привязку к модулю
	 */
	public static function addJS($fileName, $isRelativeToCurrentMod = true) {
		if($isRelativeToCurrentMod){
			$webPath = self::getWebPathToModResources() . 'js/';
			$filePath = $webPath . $fileName;
		}
		$item = array('type' => 'js', 'file' => $filePath);
		self::$includes[$filePath] = $item;
	}

	/**
	 * синоним прямому обращению к внутренней переменной с массивом подключений
	 * 
	 * @return type
	 */
	public static function getAllIncludes() {
		return self::$includes;
	}

	/**
	 * получаем путь к ресурсам текущего модуля
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
	 * вывод заголовка страницы
	 * 
	 * @param type $title
	 * @return type
	 */
	public static function getHeader($title = false) {

		$title = $title ? $title : 'MyTitle / RomanSh';
		$currentAction = myCore::$currentAction;

		$topMenu = array();
		$topMenu[] = array('link' => myConfig::get('webPath'), 'text' => myConfig::get('modManagerName'));		
		if ( $currentAction != '') {
			$modInfo = array();
			myCore::getModInfo($modInfo, $currentAction, true);
			$topMenu[] = array('link' => myRoute::getRoute($currentAction), 'text' => $modInfo[$currentAction]['title']);
		}
		
		$topMenuHtml = '';
		foreach($topMenu as $li){
			$topMenuHtml .= $topMenuHtml=='' ? '' : '<span>/</span>';
			$topMenuHtml .= '<li><a href="'.$li['link'].'">'.$li['text'].'</a></li>';
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
				<div id="siteHead">
					<ul class="topMenu">' . $topMenuHtml . '</ul>
				</div>
		';
	}

	/**
	 * вывод футера страницы
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
