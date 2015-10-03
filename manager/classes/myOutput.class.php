<?php

/**
 * класс вывода
 */
class myOutput {

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
		self::json(['success' => $data]);
	}

	public static function jsonError($message = 'unknown error', $id = -1) {
		self::json([
			'error' => ['message' => $message, 'id' => $id]
		]);
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

	public static function outFullHtml($html, $title = false, $includes = false) {
		echo
		self::getHeader($title, $includes) .
		$html .
		self::getFooter($includes)
		;
		return true;
	}

	public static function getHeader($title = false) {

		$title = $title ? $title : 'MyTitle / RomanSh';

		return '
			<!DOCTYPE html>
			<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					<title>' . $title . '</title>
					<link type="text/css" rel="stylesheet" href="' . myConfig::get('webPath').'/res/css/main.css' . '">
				</head>
				<body>
		';
	}

	public static function getFooter($includes = false) {

		$addScripts = '';
		is_array($includes) ? '' : $includes = [];

		foreach ($includes as $inc) {
			$link = $inc['link'];
			if ($inc['type'] == 'css') {
				$addScripts .= '<link type="text/css" rel="stylesheet" href="' . $link . '">';
			} elseif ($inc['type'] == 'js') {
				$addScripts .= '<script type="text/javascript" src="' . $link . '"></script>';
			}
		}

		return '
					<!-- footer/ -->
					<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
					' . $addScripts . '
				</body>
			</html>
		';
	}


}
