<?php

    spl_autoload_extensions(".php"); // comma-separated list
    spl_autoload_register();

	function SimpleAutoloaderAddSourceDirectory( $dir, bool $prepend = false, bool $iCasePaths = true, bool $debugPrint = false ) {
		spl_autoload_register(function ($class) use ($dir, $iCasePaths, $debugPrint) {

			$classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			$paths = [
					$dir . DIRECTORY_SEPARATOR . $classPath . '.php',
					$dir . DIRECTORY_SEPARATOR . strtolower($classPath . '.php'),
					$dir . DIRECTORY_SEPARATOR . strtolower(dirname($classPath)) . DIRECTORY_SEPARATOR. basename($classPath) . '.php',
				];

			foreach ( $paths as $index => $path) {
				if (file_exists($path)) {
					include_once $path;
					if ($debugPrint) { echo 'Included '.$path.PHP_EOL; }
					return;
				} else {
					if ($debugPrint) { echo 'Not including '.$path.PHP_EOL; }
				}
			}


			if ( $iCasePaths ) {
				$foundPath = $dir;
				$needleDirs = explode(DIRECTORY_SEPARATOR, $classPath.'.php');

				if ($debugPrint) { echo 'Trying case-insensitive match of '.$dir.' for '.$class.PHP_EOL; }
				foreach( $needleDirs as $index=>$needleDir ) {
					if ( !is_dir($foundPath) ) {
						break;
					}
					$found = false;
					$haystackDirs = scandir($foundPath);
					foreach( $haystackDirs as $haystackDirIndex => $haystackDir ) {
						if ( strtolower($haystackDir) == strtolower($needleDir) ) {
							$found = $haystackDir;
							continue;
						}
					}
					if (!$found) {
						break;
					}
					if ($debugPrint) { echo 'Adding to path: '.$found.PHP_EOL; }
					$foundPath .= DIRECTORY_SEPARATOR . $found;
					if ($debugPrint) { echo 'Current path: '.$foundPath.PHP_EOL; }
				}
				if ( $found && file_exists($foundPath) ) {
					if ($debugPrint) { echo 'Including '.$foundPath.PHP_EOL; }
					include_once $foundPath;
					if ($debugPrint) { echo 'Included '.$foundPath.PHP_EOL; }
					return;
				} else {
					if ($debugPrint) { echo 'Could not find. Search ended at '.$foundPath.PHP_EOL; }
				}
			}

			if ($debugPrint) { echo 'Failed to search '.$dir.', to find '.$class.PHP_EOL; }

		}, true, $prepend);
	}

?>
