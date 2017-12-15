<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  Main index file
 */


// Ensure PHP version >= 5.4
if(version_compare(phpversion(), '5.4', '<')){
	die('NamelessMC is not compatible with PHP versions older than 5.4');
}

// Start page load timer
$start = microtime(true);

// Definitions
define('PATH', '/');
define('ROOT_PATH', dirname(__FILE__));
$page = 'Home';

if(!ini_get('upload_tmp_dir')){
	$tmp_dir = sys_get_temp_dir();
} else {
	$tmp_dir = ini_get('upload_tmp_dir');
}

ini_set('open_basedir', ROOT_PATH . PATH_SEPARATOR  . $tmp_dir . PATH_SEPARATOR . '/proc/stat');

// Get the directory the user is trying to access
$directory = $_SERVER['REQUEST_URI'];
$directories = explode("/", $directory);
$lim = count($directories);

try {
	// Start initialising the page
	require(ROOT_PATH . '/core/init.php');
}
catch(Exception $e) {
	die($e->getMessage());
}

if(!isset($GLOBALS['config']['core']) && is_file(ROOT_PATH . '/install.php')) {
	Redirect::to('install.php');
}

// Get page to load from URL
if(!isset($_GET['route']) || $_GET['route'] == '/'){

	if(count($directories) > 1 && (!isset($_GET['route']) || (isset($_GET['route']) && $_GET['route'] != '/')))
		require(ROOT_PATH . '/404.php');
	else
		// Homepage
		require(ROOT_PATH . '/modules/Core/pages/index.php');

} else {
	if(!isset($route)) $route = rtrim($_GET['route'], '/');
	// Check modules

	$modules = $pages->returnPages();


	// Include the page
	if(array_key_exists($route, $modules)){
	    if(!isset($modules[$route]['custom'])){
            $path = join(DIRECTORY_SEPARATOR, array(ROOT_PATH, 'modules', $modules[$route]['module'], $modules[$route]['file']));

            if(!file_exists($path)) require(ROOT_PATH . '/404.php'); else require($path);
            die();
        } else {
	        require(join(DIRECTORY_SEPARATOR, array(ROOT_PATH, 'modules', 'Core', 'pages', 'custom.php')));
	        die();
        }
	} else {

		// Use recursion to check - might have URL parameters in path
		$path_array = explode('/', $route);

//		echo '<pre>', var_dump($path_array).  '</pre>';

		for($i = count($path_array) - 2; $i > 0; $i--){
			$new_path = '/';
			for($n = 1; $n <= $i; $n++){
				$new_path .= $path_array[$n] . '/';
			}
			$new_path = rtrim($new_path, '/');
			if(array_key_exists($new_path, $modules)){
				$path = join(DIRECTORY_SEPARATOR, array(ROOT_PATH, 'modules', $modules[$new_path]['module'], $modules[$new_path]['file']));
				if(file_exists($path)){
					require($path);
					die();
				}
			}
		}

		// 404
		require(ROOT_PATH . '/404.php');
	}

}

