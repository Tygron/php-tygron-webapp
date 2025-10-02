<?php

	namespace Utils;

	class Files {

		private static $VERIFICATION_RETRIES = 5;

		public static function writeFile( string|array $path, $content ) {
			$path = self::makePath($path);

			$unwritableCause = self::isWritable($path, true, true);
			if ( !($unwritableCause === true) ) {
				if ( empty($unwritableCause) ) {
					$unwritableCause = 'The reason is unknown.';
				}
				throw new \Exception($unwritableCause);
			}

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

		public static function readFileMimeType( string|array $path ) {
			$path = self::makePath($path);
			$mime = mime_content_type($path);
			if (!$mime) {
				throw new \Exception('Could not read file at \''.$path.'\'');
			}
			return $mime;
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

		public static function getContentsOfDirectory( string|array $path, bool $hidden = false ) {
			$path = self::makePath($path);
			if (!is_dir($path)) {
				throw new \Exception('Path is not a directory: '.strval($path));
			}
			$files = array_diff(scandir($path),['.','..']);

			if (!$hidden) {
				$hideFilter = function($value) {
					return !( substr($value,0,1)==='.' );
				};
				$files = array_filter($files, $hideFilter);
			}
			return $files;
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

		public static function getFileFromPath( string|array $path ) {
			return basename(self::makePath($path));
		}

		public static function getFileExtension( string|array $path ) {
			return pathinfo(self::getFileFromPath($path), PATHINFO_EXTENSION);
		}

		public static function ensureDirectory( string $path, bool $isFile = true ) {
			$path = self::makePath($path);
			$pathParts = explode(DIRECTORY_SEPARATOR, $path);

			if ($isFile) {
				$file = array_pop($pathParts);
			}
			$dir = '';
			foreach ($pathParts as $part) {
				if ($dir == '' && str_contains($part, ':') ) {
					$dir = $part;
				} else {
					$dir = $dir . DIRECTORY_SEPARATOR . $part;
				}
				if ( is_file($dir) ) {
					throw new \Exception('Could not ensure directory because it is a file: '.$path);
				}
				if ( !is_dir($dir) ) {
					mkdir($dir);
				}
			}
		}

		public static function isWritable( string|array $path, bool $isFile = true, bool $returnCause = true ) {
			$testPath = self::makePath($path);
			if ( is_file($testPath) ) {
				if ( !$isFile ) {
					return !$returnCause ? false : 'Path \''.$testPath.'\' not writable. Is a file, not a directory.';
				} else if ( !is_writable($path) ) {
					return !$returnCause ? false : 'Path \''.$testPath.'\' not writable. File is not writable.';
				}
				return true;
			}

			for ( $pathParts = explode(DIRECTORY_SEPARATOR, $testPath) ; count($pathParts) > 0 ; array_pop($pathParts) ) {
				$testPath = self::makePath($pathParts);
				if ( is_file($testPath) ) {
					return (!$returnCause) ? false : 'Path not writable as subpath \''.$testPath.'\' is a file.';
				} else if ( is_dir($testPath) ) {
					if ( !is_writable($testPath) ) {
						return (!$returnCause) ? false : 'Path not writable as subpath \''.$testpath.'\' is not writable.';
					}
					return true;
				}
			}

			return $returnCause ? false : 'Path \''.$path.'\' has no known origin.';
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
