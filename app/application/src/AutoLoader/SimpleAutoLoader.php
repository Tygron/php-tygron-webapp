<?php

    spl_autoload_extensions(".php"); // comma-separated list
    spl_autoload_register();

	function SimpleAutoloaderAddSourceDirectory( $dir, bool $prepend = false, bool $iCasePaths = true, bool $debugPrint = false ) {
		spl_autoload_register(function ($class) use ($dir, $iCasePaths, $debugPrint) {
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

			if ( $iCasePaths ) {
				$casedPath = $dir;
				$nsDirs = explode('\\', $class);

				if ($debugPrint) { echo 'Trying case-insensitive match of '.$dir.' for '.$class.PHP_EOL; }
				foreach( $nsDirs as $index=>$nsDir ) {
					if ( !is_dir($casedPath) ) {
						break;
					}
					$found = false;
					$subDirs = scandir($casedPath);
					foreach( $subDirs as $subIndex => $subDir ) {
						if ( strtolower($subDir) == strtolower($nsDir).'.php' ) {
							$found = $subDir;
							continue;
						}
						if ( strtolower($subDir) == strtolower($nsDir) ) {
							$found = $subDir;
							continue;
						}
					}
					if (!$found) {
						break;
					}
					if ($debugPrint) { echo 'Adding to path: '.$found.PHP_EOL; }
					$casedPath .= DIRECTORY_SEPARATOR . $found;
					if ($debugPrint) { echo 'Current path: '.$casedPath.PHP_EOL; }
				}
				if ( $found && file_exists($casedPath) ) {
					if ($debugPrint) { echo 'Including '.$casedPath.PHP_EOL; }
					include_once $casedPath;
					if ($debugPrint) { echo 'Included '.$casedPath.PHP_EOL; }
					return;
				} else {
					if ($debugPrint) { echo 'sad '.$casedPath.PHP_EOL; }
				}
			}

			if ($debugPrint) { echo 'Failed to search '.$dir.', to find '.$class.PHP_EOL; }

		}, true, $prepend);
	}
	
?>
