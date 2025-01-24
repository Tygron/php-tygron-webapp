<?php

	namespace Utils;

	class Files {

		private static $VERIFICATION_RETRIES = 5;

		public static function writeFile( string|array $path, $content ) {
			$path = self::makePath($path);

			self::ensureDirectory($path);

			$stringContent = strval($content);
			for ($i=self::$VERIFICATION_RETRIES;$i>0;$i--) {
				try {
					file_put_contents($path, $stringContent);
					if (self::readFile($path) === $stringContent) {
						return true;
					} else {
						throw new \Exception('Written data does not match read data');
					}
				} catch (\Throwable $e) {
					if ($i>1) {
						throw $e;
					}
				}
			}
			return false;
		}

		public static function readFile( string|array $path ) {
			$path = self::makePath($path);

			$contents = @file_get_contents($path);
			if ( $contents === false ) {
				$lastError = error_get_last()['message'];
				if ( str_contains( $lastError, 'No such file or directory')) {
					throw new \Exception('Could not read file at \''.$path.'\', no such file or directory.');
				}
				throw new \Exception('Could not read file at \''.$path.'\'');
			}
			return $contents;
		}

		public static function writeJsonFile( string|array $path, $content ) {
			$path = self::makePath($path);

			return self::writeFile($path, json_encode( $content, JSON_PRETTY_PRINT));
		}

		public static function readJsonFile( string|array $path ) {
			$path = self::makePath($path);

			$contents = self::readFile($path);
			$decodedContents = json_decode($contents, true);
			if ( ($decodedContents == null) && ($contents !== 'null') ) {
				return $contents;
			}
			return $decodedContents;
		}

		public static function makePath( string|array $path ) {
			$madePath = '';
			if ( is_string($path) ) {
				$madePath =  $path;
			} else if ( is_array($path) ) {
				$madePath = implode(DIRECTORY_SEPARATOR, $path);
			}

			return $madePath;
		}

		public static function ensureDirectory( string $path, bool $isFile = true ) {
			$path = self::makePath($path);
			$pathParts = explode(DIRECTORY_SEPARATOR, $path);

			if ($isFile) {
				$file = array_pop($pathParts);
			}
			$dir = '';
			foreach ($pathParts as $part) {
				$dir = $dir . DIRECTORY_SEPARATOR . $part;
				if ( is_file($dir) ) {
					throw new Exception('Could not ensure directory for \''.$path.'\', because \''.$dir.'\' is a file');
				}
				if ( !is_dir($dir) ) {
					mkdir($dir);
				}
			}
		}

		public static function deleteFile( string|array $files, string $directory = '' ) {
			if ( is_string($files) ) {
				$files = [$files];
			}
			foreach ($files as $index => $file) {
				if (empty($file)) {
					continue;
				}
				$filePath = empty($directory) ? $file : implode(DIRECTORY_SEPARATOR, [$directory,$file]);
				if (!file_exists($filePath)) {
					throw new \Exception('File does not exist, and as such cannot be deleted: '.$file);
				}
				if (!is_file($filePath)) {
					throw new \Exception('Expected a file but is a directory: '.$file);
				}
			}
			foreach ($files as $index => $file) {
				if (empty($file)) {
					continue;
				}
				$filePath = empty($directory) ? $file : implode(DIRECTORY_SEPARATOR, [$directory,$file]);
				unlink($filePath);
			}
		}
	}
?>
