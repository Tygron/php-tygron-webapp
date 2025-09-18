<?php
	namespace Curl;

	class TygronCurlTask extends CurlTask {

		public function setCredentials( string $username, string $password ) {
			$auth = $username.':'.$password;

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

		public static function get( array|string $credentials, string $platform, string $path, string|array $data = null, bool $json = true ) {
			$curlTask = new \Curl\TygronCurlTask();
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
				$curlTask->setCredentials($credentials['username'], $credentials['password']);
			}
			return $curlTask;
		}

                public static function post( array|string $credentials, string $platform, string $path, string|array $data = null, bool $json = true ) {
                        $curlTask = new \Curl\TygronCurlTask();
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
                                $curlTask->setCredentials($credentials['username'],$credentials['password']);
                        }
			return $curlTask;
                }

	}
?>
