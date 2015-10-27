<?php

// config + init
include_once './config.php';

$action = myCore::$currentAction;

// если главная 
if ($action == '') {
	applyIndexLogic();
}

// подключаем mod 
if (myCore::tryIncludeMod($action)) {
	$className = $action . 'ActionController';
	// добавим переменную в конфиг
	myConfig::set('fsPathToMod', myConfig::get('modsPath') .'/'. $action);
	new $className;
	exit;
}

// если ничего не найдено - отваливаемся с 404
myCore::render404();

/**
 * 
 * 
 * 
 * рендерим все, что относится к главной
 */
function applyIndexLogic() {
	$modList = myCore::getModsList(false, true);

	$out = '<ul>';
	foreach ($modList as $mod) {

		$modName = $mod['title'];
		$modUrl = $mod['isValid'] ? myRoute::getRoute($mod['name']) : false;
		if ($modUrl) {
			$out .= '<li><a href="' . myRoute::getRoute('git') . '">' . $modName . '</a></li>';
		} else {
			$out .= '<li>' . $modName . '</li>';
		}
	}
	$out .='</ul>';

	$includes = array(
			//['type' => 'css', 'link' => myConfig::get('webPath').'/res/css/main.css'],
			//['type' => 'js', 'link' => myRoute::getRoute('git', 'renderJS')],
	);
	myOutput::outFullHtml($out, 'Manager of Mods', $includes);

	exit;
}
