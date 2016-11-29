<?php

/**
 * все, что связано с урлами и переменными
 */
class myRoute {

	/**
	 * составить ссылку на основании параметров
	 *
	 * @param string         $action
	 * @param string|boolean $_do
	 * @param bool|type      $params
	 *
	 * @return string
	 */
	public static function getRoute($action, $_do = false, $params = false) {
		//$ret['action'] = $action;
		$ret = [];

		$_do ? $ret['_do'] = $_do : '';

		$params ? $ret['params'] = $params : '';

		if (count($ret)) {
			$ret = '?' . http_build_query($ret);
		}
		else {
			$ret = '';
		}
		$link = myConfig::get('webPath') . '/' . $action . '/' . $ret;

		return $link;
	}

	/**
	 * взять переменную из $_REQUEST и привести к типу
	 *
	 * @param type        $var
	 * @param string|type $type $type
	 * @param type        $default
	 *
	 * @return type
	 */
	public static function getRequest($var, $type = '', $default = null) {

		$var = isset($_REQUEST[$var]) ? $_REQUEST[$var] : $default;

		return self::varToType($var, $type);
	}

	/**
	 * взять переменную из массива params и привести к типу
	 *
	 * @param type        $var
	 * @param string|type $type $type
	 * @param type        $default
	 *
	 * @return type
	 */
	public static function getRequestParams($var, $type = '', $default = null) {

		$params = myRoute::getRequest('params', 'arr', []);

		$ret = $default;
		if (isset($params[$var])) {
			$ret = self::varToType($params[$var], $type);
		}

		return $ret;
	}

	/**
	 * привести переменную к заданному типу
	 *
	 * @param type $var
	 * @param type $type
	 *
	 * @return type
	 */
	public static function varToType($var, $type) {
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
		$uri      = str_replace(myConfig::get('webPath'), '', $_SERVER['REQUEST_URI']);
		$uri      = trim($uri, '/ ');
		$uriParts = explode('/', $uri);
		// заполним request стандартными переменными
		if (isset($uriParts[0])) {
			$_REQUEST['action'] = $uriParts[0];
		}
		//		if(isset($uriParts[1])){
		//			$_REQUEST['_do'] = $uriParts[1];
		//		}
		// отдаем action
		$action                = myRoute::getRequest('action', 'str', '');
		myCore::$currentAction = $action;

		return $action;
	}

}
