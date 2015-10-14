<?php

/**
 * все, что связано с урлами и переменными
 */
class myRoute {

	/**
	 * составить ссылку на основании параметров
	 * 
	 * @param type $action
	 * @param type $_do
	 * @param type $params
	 * @return string
	 */
	public static function getRoute($action, $_do = false, $params = false) {
		//$ret['action'] = $action;
		$ret = array();

		$_do ? $ret['_do'] = $_do : '';

		$params ? $ret['params'] = $params : '';

		if (count($ret)) {
			$ret = '?' . http_build_query($ret);
		} else {
			$ret = '';
		}
		$link = myConfig::get('webPath') . '/' . $action . '/' . $ret;

		return $link;
	}

	/**
	 * взять переменную из $_REQUEST
	 * 
	 * @param type $var
	 * @param type $type
	 * @param type $default
	 * @return type
	 */
	public static function getRequest($var, $type = '', $default = null) {
		
		$var = isset($_REQUEST[$var]) ? $_REQUEST[$var] : $default;

		switch ($type) {
			case 'int': {
					$var = intval($var);
					break;
				}
			case 'str': {
					is_string($var) ? $var = trim($var) : '';
					break;
				}
		}

		return $var;
	}

	/**
	 * получить переменную action из урла + перезаписать переменные в урле
	 * 
	 * @return type
	 */
	public static function getActionAndParseCurrentURI() {
		// удалим игнорируемую часть из урла
		$uri = str_replace(myConfig::get('webPath'), '', $_SERVER['REQUEST_URI']);
		$uri = trim($uri, '/ ');
		$uriParts = explode('/', $uri);
		// заполним request стандартными переменными
		if (isset($uriParts[0])) {
			$_REQUEST['action'] = $uriParts[0];
		}
//		if(isset($uriParts[1])){
//			$_REQUEST['_do'] = $uriParts[1];
//		}
		// отдаем action
		$action = myRoute::getRequest('action', 'str', '');
		myCore::$currentAction = $action;

		return $action;
	}

}
