<?php

	namespace Tasks;

	class TaskGenerator {

		protected static int $RANDOM_CHARACTERS = 4;

		public static array $DEFAULT_PARAMETERS = [

			'taskName'=>'',
			'credentials'=>[],
			'templateName'=>'',
			'platform'=>'engine',
			'location'=>[0,0],
			'size'=>[500,500],

			'taskOperations'=>[],
			'cleanupOperations'=>[],

		];

		public static function generate( array $parameters ) {
			$parameters = self::generateMissingParameters($parameters);
			$task = new \Tasks\Task( $parameters );
			return $task;
		}

		public static function normalizeParameters( array $parameters, array $allowed = [] ) {
			$result = [];
			$defaults = array_merge($allowed, self::$DEFAULT_PARAMETERS);
			foreach ( $defaults as $key=>$value ) {
				switch($key) {
					case 'taskName':
						$result[$key] = self::generateTaskName( $parameters['name']??null, self::$RANDOM_CHARACTERS );
						break;

					case 'credentials':
						try {
							$credentials = $parameters['credentials']		??	null;
							$credentials ??= self::normalizeCredentials(
									$parameters['useDefaultCredentials']	??	null,
									$parameters['username']			??	null,
									$parameters['password']			??	null,
									$parameters['mfa']			??	null,
									$parameters['platform']			??	null,
								);
							$result[$key] = $credentials;
						} catch ( \Throwable $e ) {
							$result[$key] = null;
						}
						break;

					case 'platform':
						$result[$key] = self::generatePlatformName( $parameters[$key]??null );
						break;

					case 'templateName':
						$result[$key] = self::generateTemplateName( $parameters['template']??null );
						break;

					case 'location':
						try {
							$result[$key] = self::generateLocationParameter( $parameters['location']??null,$parameters['locationX']??null,$parameters['locationY']??null );
						} catch ( \Throwable $e ) {
							$result[$key] = null;
						}
						break;

					case 'size':
						try {
							$result[$key] = self::generateSizeParameter(
									$parameters['size']??null,
									$parameters['sizeX']??null,
									$parameters['sizeY']??null
								);
						} catch ( \Throwable $e ) {
						}
						break;

					case 'taskOperations':
					case 'cleanupOperations':
						$result[$key] = self::generateOperationsList( $parameters[$key]??null );
						break;

					default:
						if ( array_key_exists($key, $parameters) ) {
							$result[$key] = $parameters[$key];
						}
						break;
				}
				if ( array_key_exists($key, $result) && is_null($result[$key]) ) {
					unset($result[$key]);
				}
			}
			return $result;
		}

		public static function generateMissingParameters( array $parameters ) {
			$parameters['taskName'] ??= self::generateTaskName( '' );
			return $parameters;
		}

		public static function generateTaskName( string $providedName = null, int $randomCharacters = 0 ) {
			if ( is_null($providedName) ) {
				return null;
			}

			$time = \Utils\Time::getReadableDateTime();
			$providedName = empty($providedName) ? '' : ('-'.$providedName);
			$random = ($randomCharacters <=0) ? '' : ('-'.   substr(bin2hex(random_bytes($randomCharacters)), 0, $randomCharacters)  );

			return $time.$providedName.$random;
		}


		public static function normalizeCredentials( string|bool $useDefaultCredentials = null, string $username = null, string $password = null, string $mfa = null, string $platform = null ) {
			if ( (!empty($platform)) &&(!empty($username)) && (!empty($password)) ) {
				return [
						'useDefaultCredentials'	=>	false,
						'platform'		=>	$platform,
						'username'		=>	$username,
						'password'		=>	$password,
						'mfa'			=>	$mfa,
					];
			} else if ( $useDefaultCredentials === 'true' || $useDefaultCredentials === true ) {
				return [
						'useDefaultCredentials'	=>	true,
					];
			} else {
				throw new \Exception('Credentials required: username, password, platform');
			}
		}

		public static function generatePlatformName( string $platformName = null) {
			if ( empty($platformName) ) {
				return null;
			}
			return $platformName;
		}

		public static function generateTemplateName( string $templateName = null ) {
			if ( empty($templateName) ) {
				return null;
			}
			return $templateName;
		}


		public static function generateLocationParameter( array $location=null, string|int $locationX=null, string|int $locationY=null ) {
			if ( is_array($location) ) {
				if ( (count($location)==2) && is_numeric($location[0]) && is_numeric($location[1]) ) {
					return [(int)$location[0],(int)$location[1]];
				}
				throw new \Exception('Location must be an array with 2 numeric coordinates');
			}
			if ( (!is_numeric($locationX)) || (!is_numeric($locationY)) ) {
				throw new \Exception('LocationX must be numeric, and LocationY must be numeric');
			}

			return [(int)$locationX, (int)$locationY];
		}

		public static function generateSizeParameter( array|string|int $size=null, string|int $sizeX=null, string|int $sizeY=null ) {
			if ( is_array($size) ) {
				if ( (count($size)==2) && is_numeric($size[0]) && is_numeric($size[1]) ) {
					return $size;
				}
				throw new \Exception('Size must be an array with 2 numeric values');
			} else if ( is_numeric($sizeX) && is_numeric($sizeY) ) {
				return [(int)$sizeX, (int)$sizeY];
			} else if ( is_numeric($size) ) {
				return [(int)$size, (int)$size];
			}

			if ( !is_numeric($sizeX) || !is_numeric($sizeY) ) {
				throw new \Exception('Size must be numeric, or SizeX and SizeY must be numeric');
			}
			return [(int)$sizeX, (int)$sizeY];
		}

		protected static function generateOperationsList( string|array $operations = null ) {
			if (is_string($operations) && str_starts_with($operations, '[') ) {
				$operations = json_decode($operations);
				return $operations;
			}
			if (is_array($operations)) {
				return $operations;
			}
			if (empty($operations)) {
				return null;
			}
			return [$operations];
		}
	}

?>
