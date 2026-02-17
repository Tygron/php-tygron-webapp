<?php

	namespace Tasks;

	class TaskCredentials {

		public static string $CREDENTIALSFILE_POSTFIX = '-credentials.json';

		//* Used/relied on *//

		public static function credentialsToFile( string $fileName, string|bool $useDefaultCredentials = false, string $platform = null, string $username = null, string $password = null, string $mfa = null ) {
			self::validateCredentials( $useDefaultCredentials, $platform, $username, $password, $mfa );

			if ( !empty($username) && !empty($password) && !empty($platform) ) {
				return self::createCredentialsFile( $fileName, $platform, $username, $password, $mfa );
			} else if ( $useDefaultCredentials ) {
				$defaultCredentialsFileName = self::getDefaultCredentialsFileName($useDefaultCredentials);
				return self::generateCredentialsFileNameWithDir($defaultCredentialsFileName);
			} else {
				throw new \Exception('No default credentials or provided credentials');
			}
		}

		public static function validateCredentials( string|bool $useDefaultCredentials = false, string $platform = null, string $username = null, string $password = null, string $mfa = null ) {
			if ( $useDefaultCredentials ) {
				$defaultCredentialsFileName = self::getDefaultCredentialsFileName($useDefaultCredentials);
				if ( is_null($defaultCredentialsFileName) ) {
					throw new \Exception('No default credentials available');
				}
				return true;
			} else if ( !$useDefaultCredentials ) {
				if ( empty($platform) || empty($username) || empty($password) ) {
					throw new \Exception('Credentials required: platform, username, password');
				}
				return true;
			}
		}

		public static function loadCredentialsFile( string $fileName ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			if ( !is_string($fileName) || empty($fileName) ) {
				return null;
			}

			try {
				$fileName = self::generateCredentialsFileNameWithDir($fileName);
				$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $fileName]);
				if (!empty($credentials)) {
					return $credentials;
				}
				return null;
			} catch (\Throwable $e) {
				throw $e;
			}
		}

		public static function deleteCredentialsFile( string $fileName ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			if ( self::isDefaultCredentialsFile($fileName) ) {
				throw new \Exception('Cannot delete default credentials file');
			}
			try {
				\Utils\Files::deleteFile($fileName, $WORKSPACE_CREDENTIALS_DIR);
			} catch (\Throwable $e) {
				throw new \Exception('Could not delete credentials file');
			}
		}

		public static function createCredentialsFile( string $fileName, string $platform, string $username, string $password, string $mfa = null ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			if ( self::isDefaultCredentialsFile($fileName) ) {
				throw new \Exception('Cannot create or modify default credentials file');
			}
			$fileName = self::generateCredentialsFileNameWithDir($fileName);
			\Utils\Files::WriteJsonFile([$WORKSPACE_CREDENTIALS_DIR, $fileName], [
				'platform' 	=> $platform,
				'username' 	=> $username,
				'password' 	=> $password,
				'mfa'		=> $mfa,
				'created' 	=> \Utils\Time::getCurrentTimestamp(),
			]);
			return $fileName;
		}

		public static function generateCredentialsFileName( string $fileName ) {
			if (str_ends_with($fileName, self::$CREDENTIALSFILE_POSTFIX)) {
				return $fileName;
			}
			return $fileName.self::$CREDENTIALSFILE_POSTFIX;
		}
		public static function generateCredentialsFileNameWithDir( string $fileName ) {
			global $CREDENTIALS_TRANSIENT_FOLDER, $CREDENTIALS_DEFAULTS_FOLDER;
			$prefix = $CREDENTIALS_TRANSIENT_FOLDER;

			if ( self::isDefaultCredentialsFile($fileName) ) {
				$prefix = $CREDENTIALS_DEFAULTS_FOLDER;
			}
			if (!self::isDefaultCredentialsFile($fileName)) {
				$fileName = self::generateCredentialsFileName($fileName);
			}
			if ( str_starts_with($fileName, $prefix) ) {
				return $fileName;
			}
			return \Utils\Files::makePath([$prefix, $fileName]);
		}


		//* Rewritten *//

		public static function isDefaultCredentialsFile( string $fileName ) {
			global $WORKSPACE_CREDENTIALS_DIR, $CREDENTIALS_DEFAULTS_FOLDER, $CREDENTIALS_FILE_DEFAULT;

			if ( $fileName == self::generateCredentialsFileName($CREDENTIALS_FILE_DEFAULT) ) {
				return true;
			}
			if ( !empty($CREDENTIALS_DEFAULTS_FOLDER) ) {
				if ( str_starts_with($fileName, $CREDENTIALS_DEFAULTS_FOLDER) ) {
					return true;
				}

				$namePath = [$WORKSPACE_CREDENTIALS_DIR, $CREDENTIALS_DEFAULTS_FOLDER, $fileName];
				if (\Utils\Files::fileExists($namePath) ) {
					return true;
				}
			}
			return false;
		}


		public static function getDefaultCredentialsFileName( bool|string $fileName = null ) {
			global $CREDENTIALS_FILE_DEFAULT;
			if ( is_bool($fileName) ) {
				$fileName = $CREDENTIALS_FILE_DEFAULT;
			}
			if ( empty($fileName) ) {
				return null;
			}
			if ( self::isDefaultCredentialsFile($fileName) ) {
				return $fileName;
			}
			return null;
		}




		//* Notes //

		public static function __generateCredentialsFileName( string $fileName ) {
			global $CREDENTIALS_DEFAULTS_FOLDER;
			$path = [$CREDENTIALS_DEFAULTS_FOLDER, $fileName];
			if (\Utils\Files::fileExists($path) ) {
				return \Utils\Files::makePath($path);
			}
			if (str_starts_with($fileName, $CREDENTIALS_) ) {

			}

			if (str_ends_with($fileName, self::$CREDENTIALSFILE_POSTFIX)) {
				return $fileName;
				}
			return $fileName.self::$CREDENTIALSFILE_POSTFIX;
		}

		public static function __getDefaultCredentialsFile( string $requestedFileName ) {
			//
		}

		public static function __getDefaultCredentialsFileName() {
                        global $CREDENTIALS_FILE_DEFAULT;
                        return $CREDENTIALS_FILE_DEFAULT;
		}

		public static function __isDefaultCredentialsFile( string|null $fileName ) {
			if ( empty(self::getDefaultCredentialsFileName()) ) {
				return false;
			}
			if ( empty($fileName) ) {
				return false;
			}
			return $fileName == self::getDefaultCredentialsFileName();
		}

		//*/
	}

?>
