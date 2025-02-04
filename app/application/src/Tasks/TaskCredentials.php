<?php

	namespace Tasks;

	class TaskCredentials {

		public static string $CREDENTIALSFILE_POSTFIX = '-credentials.json';

		public static function isDefaultCredentialsFile( string|null $fileName ) {
			if ( empty(self::getDefaultCredentialsFileName()) ) {
				return false;
			}
			if ( empty($fileName) ) {
				return false;
			}
			return $fileName == self::getDefaultCredentialsFileName();
		}

		public static function getDefaultCredentialsFileName() {
                        global $CREDENTIALS_FILE_DEFAULT;
                        return $CREDENTIALS_FILE_DEFAULT;
		}

		public static function generateCredentialsFileName( string $fileName ) {
			if (str_ends_with($fileName, self::$CREDENTIALSFILE_POSTFIX)) {
				return $fileName;
			}
			return $fileName.self::$CREDENTIALSFILE_POSTFIX;
		}

		public static function credentialsToFile( string $fileName, bool $useDefaultCredentials = false, string $platform = null, string $username = null, string $password = null ) {
			self::validateCredentials( $useDefaultCredentials, $platform, $username, $password );
			if ( $useDefaultCredentials ) {
				return self::getDefaultCredentialsFileName();
			} else {
				return self::createCredentialsFile( $fileName, $platform, $username, $password);
			}
		}


		public static function validateCredentials( bool $useDefaultCredentials = false, string $platform = null, string $username = null, string $password = null ) {
			if ( $useDefaultCredentials ) {
				if (is_null(self::loadCredentialsFile(self::getDefaultCredentialsFileName())) ) {
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

			if ( is_string($fileName) && !empty($fileName) ) {
				try {
					$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $fileName]);
					if (!empty($credentials)) {
						return $credentials;
					}
				} catch (\Throwable $e) {
					throw $e;
				}
			}
			return null;
		}

		public static function deleteCredentialsFile( string $fileName ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			if ($fileName == self::getDefaultCredentialsFileName() ) {
				throw new \Exception('Cannot delete default credentials file');
			}
			try {
				\Utils\Files::deleteFile($fileName, $WORKSPACE_CREDENTIALS_DIR);
			} catch (\Throwable $e) {
				throw new \Exception('Could not delete credentials file');
			}
		}

		public static function createCredentialsFile( string $fileName, string $platform, string $username, string $password) {
			global $WORKSPACE_CREDENTIALS_DIR;

			if ( $fileName == self::getDefaultCredentialsFileName() ) {
				throw new \Exception('Cannot create or modify default credentials file');
			}

			$fileName = self::generateCredentialsFileName($fileName);
			\Utils\Files::WriteJsonFile([$WORKSPACE_CREDENTIALS_DIR, $fileName], [
				'platform' => $platform,
				'username' => $username,
				'password' => $password,
				'created' => \Utils\Strings::dateTimeString(),
			]);
			return $fileName;
		}
	}

?>
