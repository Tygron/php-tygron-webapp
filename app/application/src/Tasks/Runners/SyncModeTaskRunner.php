<?php

	namespace Tasks\Runners;

	class SyncModeTaskRunner {

		private $taskFileName = null;
		private $hasOperated = false;
		private $hasCompleted = false;
		private $standOffTimeInSeconds = 0;

		public const RUN_MODE_ASYNC = \Tasks\Task::SYNC_MODE_ASYNC;
		public const RUN_MODE_SYNC = \Tasks\Task::SYNC_MODE_SYNC;

		private $syncMode = self::RUN_MODE_ASYNC;
		private $mixedSyncModeAllowed = false;
		private $skipIncompatibleSync = false;

		private $logs = [];

		public function __construct( $parameters = null ) {
		}

		public function setSyncMode( string $mode, bool $mixed = false ) {
			if ( !in_array($mode, \Tasks\Task::SYNC_MODES)) {
				throw new \Exception( get_text('Invalid mode: %s', $mode) );
			}
			$this->syncMode = $mode;
			$this->mixedSyncModeAllowed = $mixed;
		}
		public function setSkipIncompatibleSync( bool $skip = true ) {
			$this->skipIncompatibleSync = $skip;
		}
		public function getSyncMode() {
			return $this->syncMode;
		}
		public function getSyncModeIsMixed() {
			return $this->mixedSyncModeAllowed;
		}
		public function getSyncModeIsSync() {
			return $this->getSyncMode() == self::RUN_MODE_SYNC;
		}
		public function getSyncModeIsAsync() {
			return $this->getSyncMode() == self::RUN_MODE_ASYNC;
		}
		public function getSyncModeIsMixedAsync() {
			return $this->getSyncModeIsAsync() && $this->getSyncModeIsMixed();
		}
		public function getSkipIncompatibleSync() {
			return $this->skipIncompatibleSync;
		}

		public function setStandOffTimeInSeconds( int $standOffTimeInSeconds ) {
			$this->standOffTimeInSeconds = $standOffTimeInSeconds;
		}

		public function setTaskFileName( $taskFileName ) {
			$this->taskFileName = $taskFileName;
		}
		public function getTaskFileName() {
			return $this->taskFileName;
		}


		public function setHasOperated( bool $operated = true) {
			$this->hasOperated = $operated;
		}
		public function getHasOperated() {
			return $this->hasOperated;
		}
		public function addHasOperated ( bool $operated = true ) {
			$this->setHasOperated( $operated || $this->getHasOperated() );
		}
		public function setHasCompleted( bool $completed = true ) {
			$this->hasCompleted = $completed;
		}
		public function getHasCompleted() {
			return $this->hasCompleted;
		}



		public function getLogs() {
			return $this->logs;
		}
		public function logMessage( \Tasks\Task $task = null, string|array $message) {
			if ( !is_null($task) ) {
				$this->logMessageTask($task, $message);
			}
			$this->logMessageRunner($message);
		}
		public function logMessageRunner(string|array $message) {
			array_push($this->logs, $message);
		}
		public function logMessageTask(\Tasks\Task $task, string|array $message ) {
			$task->logMessage($message);
		}





		protected function getTask() {
			try {
				$task = \Tasks\Task::load( $this->taskFileName );
				return $task;
			} catch (\Throwable $e) {
				throw $e;
			}
		}







		public function run() {

			$task = $this->getTask();

			if ( $this->getSyncModeIsSync() ) {
				return $this->runAsLoop( $task);
			} else if ( $this->getSyncModeIsMixedAsync() ) {
				return $this->runAsLoop( $task );
			} else {
				return $this->runAsSingle( $task );
			}
		}

		protected function runAsSingle( \Tasks\Task $task ) {

			$async = $this->getSyncModeIsAsync();
			$mixed = $this->getSyncModeIsMixedAsync();

			if ( !$this->getTaskInCompatibleSyncMode($task) ) {
				return false;
			}

			if ( $async && (!$mixed) ) {
				if ( !$this->getTaskNotInStandoff($task) ) {
					return false;
				}
			}

			if ( $this->getTaskCleanupCompleted($task) ) {
				$this->setHasCompleted();
				return false;
			}

			$operationsByName = $this->getOperationsByName( $task );
			$operation = $this->getOperationToRun( $task, $operationsByName );
			if ( !$this->processOperationCompleted( $task, $operation ) ) {
				if ( $async ) {
					return false;
				} else {
					throw new \Exception(get_text( 'Sync-mode operation did not complete') );
				}
			}

			if ( $this->getTaskCleanupCompleted($task) ) {
				$this->setHasCompleted();
				return false;
			}

			$operation = $this->getOperationToRun( $task, $operationsByName );
			$hasOperated = $this->operate( $task, $operation );
			$this->addHasOperated($hasOperated);

			return $hasOperated;
		}

		protected function runAsLoop( \Tasks\Task $task) {

			$running = true;
			$result = null;
			while ( $running ) {
				$result = $this->runAsSingle( $task );

				if ( $this->getOperationsOfTaskCompleted( $task ) ) {
					$this->setHasCompleted();
					return $task->getOutput();
				}

				if ( (!$result) && $this->getSyncModeIsSync() ) {
					throw new Exception( get_text('Job blocked during sync-mode') );
				}

				$running = $result;
			}

			return $result;
		}



		protected function getOperationsByName( $task ) {
			$operations = [];
			$operationNames = $task->getOperations();
			foreach ( $operationNames as $key=>$value ) {
				$className = '\\Tasks\\Operations\\'.$value;
				if ( empty($className) ) {
					continue;
				}
				if ( !class_exists($className) )  {
					throw new \Exception(get_text('Unknown operation: %s',[$value]));
				}
				try {
					$operation = new $className($task);
					$operations[$value] = $operation;
				} catch ( \Throwable $e ) {
					throw new \Exception( get_text('Could not load operation %s',[$value]), previous:$e);
				}
		        }
			return $operations;
		}

		protected function getTaskNotInStandoff( $task ) {
			$lastTime = $task->getLastOperationTime();
			$currentTime = \Utils\Time::getCurrentTimestamp();

			if ( ($lastTime + $this->standOffTimeInSeconds) > $currentTime) {
				throw new \Exception( get_text('Standoff period in effect') );
			}
			return true;
		}

		protected function getTaskInCompatibleSyncMode( $task ) {
			if ( $task->getSyncMode() !== $this->getSyncMode() ) {
				if ( $this->getSkipIncompatibleSync() ) {
					$this->logMessageRunner(get_text('Cannot run task in mode %s, with runner in mode %s',[$task->getSyncMode(), $this->getSyncMode()]));
					return false;
				}
				throw new \Exception(get_text('Cannot run task in mode %s, with runner in mode %s',[$task->getSyncMode(), $this->getSyncMode()]));
			}
			return true;
		}

		protected function getOperationsOfTaskCompleted($task) {
			return $task->getTaskCompleted();
		}

		protected function getTaskCleanupCompleted($task) {
			return $task->getCompleted();
		}

		protected function getOperationToRun( $task, $operationsByName ) {
			$currentOperation = $task->getCurrentOperation();
			if (!array_key_exists($currentOperation, $operationsByName)) {
				throw new \Exception( get_text('Task operation not found: %s',[$currentOperation]));
			}
			return $operationsByName[$currentOperation];
		}

		protected function processOperationCompleted( $task, $operation ) {
			$currentOperation = $task->getCurrentOperation();
			$startedOperation = $task->getStartedOperation();
			if ($currentOperation == $startedOperation) {
				try {
					if ( $operation->checkOperationComplete() ) {
						$this->logMessageRunner(get_text('The current operation %s has completed, switching to next operation',[$currentOperation]));
						$task->setToNextOperation();
						$task->save();
					} else if ( $this->getSyncModeIsSync() && $operation->skipWhenNotAsync() ) {
						$this->logMessage( $task, get_text('The operation %s has been skipped in sync-mode, switching to next operation',[$currentOperation]));
						$task->setToNextOperation();
						$task->save();
					} else {
						return false;
					}
					if ( $task->getCompleted() ) {
						$this->logMessageRunner(get_text('The task has reached completion'));
					}
					return true;

				} catch (\Curl\CooldownException $e) {
					$this->logMessageRunner(get_text('Cannot check completion, because connection is on cooldown') );
					return false;
				} catch(\Throwable $e) {
					$this->handleOperationException($task, $operation, $e);
					return true;
				}
			}
			return true;
		}

		protected function operate( $task, $operation ) {
			$currentOperation = $task->getCurrentOperation();
			$startedOperation = $task->getStartedOperation();
			if ($currentOperation == $startedOperation) {
				$this->logMessageRunner(get_text('The current operation is %s, and should already be running',[$currentOperation]));
				return false;
			}
			$ready = null;
			try {
				$ready = $operation->checkReadyForOperation();
			} catch ( \Curl\CooldownException $e ) {
				$this->logMessageRunner(get_text('Check aborted, because connection is on cooldown'));
				return false;
			}
			if ( is_string($ready) ) {
				throw new \Exception( get_text('Task in state to start operation, but not ready: %s. Reason: %s',[$currentOperation, $ready]));
			} else if (!$ready) {
				throw new \Exception( get_text('Task in state to start operation, but not ready: %s',[$currentOperation]));
			}
			$task->setStartedOperation($currentOperation);
			$task->setLastOperationTime( \Utils\Time::getCurrentTimestamp() );
			$task->save();
			$this->logMessageRunner(get_text('The current operation is %s, and is now starting',[$currentOperation]));
			try {
				$result = $operation->startOperation();
				if ( isset($result) ) {
					$task->setOperationResult($result);
					$task->save();
				}
			} catch (\Curl\CooldownException $e) {
				$this->logMessageRunner(get_text('Operation aborted, because connection is on cooldown') );
				$task->setStartedOperation($currentOperation.' (on cooldown)');
				$task->save();
				return false;
			} catch(\Throwable $e) {
				$this->handleOperationException($task, $operation, $e);
			}
			return true;
		}

		protected function handleOperationException( $task, $operation, $exception ) {
			$task->setError($exception);
			$task->startCleanup();
			$task->save();
		}
	}

?>
