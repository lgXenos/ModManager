<?php

include_once './config.php';

$action = myRoute::getActionAndParseCurrentURI();

//print_r(  );
// если главная 
if ($action == '') {
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

// подключаем mod 
if (myCore::tryIncludeMod($action)) {
	$className = $action . 'ActionController';
	new $className;
	exit;
}

myCore::render404();