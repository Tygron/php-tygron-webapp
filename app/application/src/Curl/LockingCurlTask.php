<?php
	namespace Curl;

	class LockingCurlTask extends CurlTask {

		public static $lockLocation = null;

		public static function setLockLocation( string $lockLocation ) {
			if ( !is_dir($lockLocation) ) {
				throw \Exception('Not a directory ('.$lockLocation.')');
			}
			self::$lockLocation = $lockLocation;
		}

		protected $lockName = 'LockingCurlTask';
		protected $retries = 5;
		protected $cooldownTimeInSeconds = 10;

		private $thrownException = null;

		public function setLockSettings( string $lockName, int $retries, int $cooldownTimeInSeconds ) {
			$this->lockName = $lockName;
			$this->retries = $retries;
			$this->cooldownTimeInSeconds = $cooldownTimeInSeconds;
		}

		public function getLockLocation() {
			if ( is_null(self::$lockLocation) ) {
				throw \Exception('No lock location defined');
			}
			return self::$lockLocation;
		}

		public function getLockName() {
			return $this->lockName;
		}

		public function getLockFile() {
			return implode( DIRECTORY_SEPARATOR , [$this->getLockLocation(), $this->getLockName().'.lock' ]);
		}

		public function mustCooldown() {
			return ($this->getStatus() > 0) && ($this->getStatus() >= 400);
		}

		public function run() {
			$runDone = true;
			for ( $retry = $this->retries; $retry>0; $retry--) {
				$runDone = $this->runLocked();
				if ( (!$runDone) && ($retry>1) ) {
					sleep(1);
				} else {
					break;
				}
			}
			if ( !$runDone ) {
				throw new \Curl\CooldownException();
			}
			return $this;
		}

		private function runLocked() {
			$lockFile = $this->getLockFile();

			$lockFileHandle = fopen($lockFile, 'c+');
			if ( !$lockFileHandle ) {
				throw new \Exception('Failed to obtain handle for file: '.$file);
			}
			if ( !flock($lockFileHandle, LOCK_EX) ) {
				return false;
			}

			if ( !(@file_get_contents($lockFile) < time()) ) {
				flock($lockFileHandle, LOCK_UN);
				return false;
			}

			try {
				parent::run();
			} catch ( \Throwable $e ) {
				$this->thrownException = $e;
			}

			if ( $this->mustCooldown() ) {
				rewind($lockFileHandle);
				ftruncate($lockFileHandle, 0);
				$newTime = time()+$this->cooldownTimeInSeconds;
				fwrite($lockFileHandle, $newTime );
			}

			flock($lockFileHandle, LOCK_UN);
			fclose($lockFileHandle);

			if ( !is_null($this->thrownException) ) {
				throw $this->thrownException;
			}
			return true;
		}
	}
?>
