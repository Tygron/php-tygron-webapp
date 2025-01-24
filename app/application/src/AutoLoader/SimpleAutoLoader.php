<?php

    spl_autoload_extensions(".php"); // comma-separated list
    spl_autoload_register();
	
	function SimpleAutoloaderAddSourceDirectory( $dir, bool $debugPrint = false ) {
		spl_autoload_register(function ($class) use ($dir, $debugPrint) {
			$path = $dir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
			if (file_exists($path)) {
				include_once $path;
				if ($debugPrint) { echo 'Included '.$path.PHP_EOL; }
				return;
			}
			$path = $dir . DIRECTORY_SEPARATOR . strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
			if (file_exists($path)) {
				include_once $path;
				if ($debugPrint) { echo 'Included '.$path.PHP_EOL; }
				return;
			}
			if ($debugPrint) { echo 'Failed to find '.$path.PHP_EOL; }
		});
	}
	
?>
