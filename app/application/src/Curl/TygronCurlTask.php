<?php
	namespace Curl;

	class TygronCurlTask extends LockingCurlTask {

		private static $tygronLockName = 'tygronCurl';
		private static $tygronRetries = 5;
		private static $tygronCooldownTimeInSeconds = 10;

		public static function setLockName( string $lockName ) { self::$tygronLockName = $lockName; }
		public static function setRetries( int $retries ) { self::$tygronRetries = $retries; }
		public static function setCooldownTimeInSeconds( int $cooldownTimeInSeconds ) { self::$tygronCooldownTimeInSeconds = $cooldownTimeInSeconds; }

		public function __construct() {
			$this->setLockSettings(self::$tygronLockName, self::$tygronRetries, self::$tygronCooldownTimeInSeconds);
		}

		public function setCredentials( string $username, string $password, string $mfa = null ) {
			$auth = $username.':'.$password;
			if ( !empty($mfa) ) {
				$auth.=':'.$mfa;
			}

			$b64Auth = base64_encode($auth);
			return $this->setAuthHeader( $b64Auth, 'Basic' );
		}

		public function setToken( string $apiToken ) {
			return $this->setParameters( ['token' => $apiToken] );
		}

		public function setUrl( string $platform = 'engine', string $path = '/' ) {
			if ( str_contains($platform, '/') ) {
				parent::setUrl($platform);
			}
			$url = 'https://'.$platform.'.tygron.com';
			if ( !empty($path) ) {
				if ( !str_starts_with($path,'/') ) {
					$url = $url.'/';
				}
				$url = $url.$path;
			}
			return parent::setUrl($url);
		}

		public function mustCooldown() {
			return in_array( $this->getStatus(), [401, 403]);
		}

		public static function get( array|string $credentials, string $platform, string $path, string|array $data = null, bool $json = true ) {
			$curlTask = new static();

			$curlTask
				->setMethod('GET')
				->setUrl($platform, $path)
				->setData($data);

			if ($json) {
				$curlTask->setParameters(['f'=>'JSON']);
			}
			if ( is_string($credentials) ) {
				$curlTask->setParameters(['token'=>$credentials]);
			} else if ( is_array($credentials) ) {
				$curlTask->setCredentials($credentials['username'], $credentials['password'], $credentials['mfa']??null);
			}
			return $curlTask;
		}

                public static function post( array|string $credentials, string $platform, string $path, string|array $data = null, bool $json = true ) {
			$curlTask = new static();

                        $curlTask
				->setMethod('POST')
                        	->setUrl($platform, $path)
                        	->setData($data);

			if ($json) {
                        	$curlTask->setParameters(['f'=>'JSON']);
			}
                        if ( is_string($credentials) ) {
                                $curlTask->setParameters(['token'=>$credentials]);
                        } else if ( is_array($credentials) ) {
                                $curlTask->setCredentials($credentials['username'],$credentials['password'], $credentials['mfa']??null);
                        }
			return $curlTask;
                }

	}
?>
