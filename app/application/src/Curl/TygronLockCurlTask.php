<?php
	namespace Curl;

	class TygronLockCurlTask extends TygronCurlTask {

		private $cooldown = false;

		private static $cooldownRetries = 5;
		private static $cooldownTimeInSeconds = 10;
		private static $cooldownLockFile = null;
		private static $throwExceptionOnCooldown = true;

		public static function setCooldownRetries( int $cooldownRetries ) {
			self::$cooldownRetries = $cooldownRetries;
		}
		public static function setCooldownTimeInSeconds( int $cooldownTimeInSeconds ) {
			self::$cooldownTimeInSeconds = $cooldownTimeInSeconds;
		}
		public static function setCooldownLockfile( string $cooldownLockFile ) {
			self::$cooldownLockFile = $cooldownLockFile;
		}

		/*
		public static function get( array|string $credentials, string $platform, string $path, string|array $data = null, bool $json = true ) {

		}
                public static function post( array|string $credentials, string $platform, string $path, string|array $data = null, bool $json = true ) {

		}
		*/
		public function isOnCooldown() {
			return $this->cooldown;
		}

		private function setIsOnCooldown( bool $cooldown ) {
			$this->cooldown = $cooldown;
		}

		public function runLocked() {
			if ( empty(self::$cooldownLockFile) ) {
				throw new \Exception('Cannot run locked. No lock-file defined.');
			}
			$file = self::$cooldownLockFile;
			$lockFileHandle = fopen($file, 'c+');
			if ( !$lockFileHandle ) {
				throw new \Exception('Failed to obtain handle for file: '.$file.' error: '.error_get_last());
			}

			$this->setIsOnCooldown(true);
			for( $retry = self::$cooldownRetries; $retry>0;$retry-- ) {
				if ( flock($lockFileHandle, LOCK_EX) ) {
					if ( @file_get_contents($file) < time() ) {
						$this->setIsOnCooldown(false);
						try {
							parent::run();
						} catch (\Throwable $e) {

						}
						if ( in_array($this->getStatus(),[401,403]) ) {
							rewind($lockFileHandle);
							ftruncate($lockFileHandle, 0);
							$newTime = time()+self::$cooldownTimeInSeconds;
							fwrite($lockFileHandle, $newTime );
						}
						break;
					}
					flock($lockFileHandle, LOCK_UN);
				} else {
					$this->setIsOnCooldown(true);
					sleep(1);
				}
			}
			fclose($lockFileHandle);
			if ($this->isOnCooldown() && self::$throwExceptionOnCooldown) {
				throw new \Exception('TYGRON_COOLDOWN');
			}
			return $this;
		}

		public function runUnlocked() {
			return parent::run();
		}

		public function run() {
			if ( empty(self::$cooldownLockFile) ) {
				$this->runUnlocked();
			} else {
				$this->runLocked();
			}
		}

	}
?>
