<?php

	namespace Utils;

	class Classes {

		public static function getClassesInFolder( string|array $path) {
			$classes = [];
			$path = \Utils\Files::makePath($path);
			$files = [];
			try {
				$files = \Utils\Files::getContentsOfDirectory( $path );
			} catch ( \Throwable $e ) {
				return $classes;
			}
			$namespacePattern =  '/namespace[\s]*([^;\s]+)[\s]*[^;]*;/';
			$classPattern =  '/class[\s]*([^;\s]+)[\s]*/';
			foreach ( $files as $key => $value ) {
				$namespaceMatches = [];
				$classMatches = [];
				$fileContent = \Utils\Files::readFile([$path, $value]);
				$resultNamespace = preg_match( $namespacePattern, $fileContent, $namespaceMatches );
				$resultClass = preg_match( $classPattern, $fileContent, $classMatches );
				if ( ($resultNamespace === 1) && ($resultClass === 1) ) {
					array_push( $classes, $namespaceMatches[1].'\\'.$classMatches[1] );
				}
			}
			return $classes;
		}

		public static function getClassesInOverrideFolders( string|array $path, array $roots = [] ) {
			$classes = [];

			$paths = \Utils\Files::makeMultiRootPaths( $path, $roots );
			foreach ( $paths as $key => $rootedPath ) {
				$classes = array_merge( $classes, self::getClassesInFolder( $rootedPath ) );
			}

			$classes = array_unique($classes);
			return $classes;
		}

	}



?>
