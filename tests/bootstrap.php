<?php
set_include_path(dirname(__FILE__) . '/../' . PATH_SEPARATOR . get_include_path());

define('PATH_TO_CLIENT', 'src/SmfApi/Client');
define('PATH_TO_SERVER', 'src/SmfApi/Server/api');

function customAutoLoader( $class )
{
	$base = rtrim(dirname(__FILE__), '/');
    $file = $base . '/' . $class . '.php';
    if (!file_exists($file)) {

    	// strip namespace to get class name
    	$components = explode('\\', $class);
		$class = end($components);

    	// src/Server
    	$file = $base . '/../' . PATH_TO_SERVER . '/' . $class . '.php';
    	if (!file_exists($file)) {
    		// src/Client
    		$file = $base . '/../' . PATH_TO_CLIENT . '/' . $class . '.php';
    		if (!file_exists($file)) {
    			return;
    		}
    	}
    }

echo $file . "\n";
    require_once $file;
}
spl_autoload_register('customAutoLoader');
?>