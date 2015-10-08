<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

// файловый путь. для инклудов и пр.
define('FLS_DIR', dirname(__FILE__));

function my_autoload($pClassName) {
	$filename = FLS_DIR . "/classes/" . $pClassName . ".class.php";
	if(!file_exists($filename)){
		// exit('Class <b>'.$pClassName.'</b> not found. Halted.');
		exit('File <b>'.$filename.'</b> not found. Halted.');
	}
	require_once($filename);
}

spl_autoload_register("my_autoload");

$cfg = array(
	// fullPath set in getInstance
	'webPath' => '/manager',
	'modsPath' => FLS_DIR . '/mods'
);

// init myConfig
try {
	// set fullPath
	myConfig::getInstance(FLS_DIR);
	myConfig::set($cfg);
	// set "action", init core
	myCore::getInstance();
} catch (Exception $e) {
	exit('Config is broken. Halted');
}
