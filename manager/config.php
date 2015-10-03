<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

// файловый путь. для инклудов и пр.
define('FLS_DIR', dirname(__FILE__));

function my_autoload($pClassName) {
	include(FLS_DIR . "/classes/" . $pClassName . ".class.php");
}

spl_autoload_register("my_autoload");

$cfg = [
	// fullPath set in getInstance
	'webPath' => '/manager',
	'modsPath' => FLS_DIR . '/mods'
];

// init myConfig
try {
	myConfig::getInstance(FLS_DIR); // set fullPath
	myConfig::set($cfg);
	// var_dump(myConfig::getAllConfig());
} catch (Exception $e) {
	exit('Config is broken. Halted');
}
