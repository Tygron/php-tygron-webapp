<?php

	namespace Utils;

	class Classes {

		public static function getClassesInFolder( string|array $path) {
			$path = \Utils\Files::makePath($path);
			$files = \Utils\Files::getContentsOfDirectory( $path );
			$classes = [];
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

	}



?>
