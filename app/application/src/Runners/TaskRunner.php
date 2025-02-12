<?php

	namespace Runners;

	class TaskRunner {

		private $taskName = null;
		private $hasOperated = false;

		public function __construct( $parameters = null ) {

		}

		public function setTask( $taskName ) {
			$this->taskName = $taskName;
		}

		public function getHasOperated() {
			return $this->hasOperated;
		}

		public function run() {

			$task = $this->getTask( $this->taskName );

			if (!$this->getTaskShouldBeRun( $task )) {
				return false;
			}

			$operationsByName = $this->getOperationsByName( $task );
			$operation = $this->getOperationToRun( $task, $operationsByName );
			$this->processOperationCompleted($task, $operation);

			if (!$this->getTaskShouldBeRun( $task )) {
				return false;
			}
			$operation = $this->getOperationToRun( $task, $operationsByName );
			$this->hasOperated = $this->operate( $task, $operation );
		}

		protected function getTask( $taskName ) {
			try {
				$task = \Tasks\Task::load($taskName);
				return $task;
			} catch (\Throwable $e) {
				throw $e;
			}
		}

		protected function getOperationsByName( $task ) {
			$operations = [];
			$operationNames = $task->getOperations();
			foreach ( $operationNames as $key=>$value ) {
				$className = '\\Operations\\'.$value;
				if ( empty($className) ) {
					continue;
				}
				if ( !class_exists($className) )  {
					throw new \Exception(get_text('Unknown operation: %s',[$value]));
				}
				try {
					$operation = new $className();
					$operations[$value] = $operation;
				} catch ( \Throwable $e ) {
					throw new \Exception( get_text('Could not load operation %s',[$value]), previous:$e);
				}
		        }
			return $operations;
		}

		protected function getTaskShouldBeRun( $task ) {
			return !$task->getCompleted();
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
					if ( $operation::checkOperationComplete($task, false) ) {
						log_message(get_text('The current operation %s has completed, switching to next operation',[$currentOperation]));
						$task->setToNextOperation();
						$task->save();
					}
					if ( $task->getCompleted() ) {
						log_message(get_text('The task has reached completion'));
					}
				} catch (\Throwable $e) {
					$this->handleOperationException($task, $operation, $e);
				}
			}
		}

		protected function operate( $task, $operation ) {
			$currentOperation = $task->getCurrentOperation();
			$startedOperation = $task->getStartedOperation();
			if ($currentOperation == $startedOperation) {
				log_message(get_text('The current operation is %s, and should already be running',[$currentOperation]));
				return false;
			}
			$ready = $operation::checkReadyForOperation($task);
			if ( is_string($ready) ) {
				throw new \Exception( get_text('Task in state to start operation, but not ready: %s. Reason: %s',[$currentOperation, $ready]));
			} else if (!$ready) {
				throw new \Exception( get_text('Task in state to start operation, but not ready: %s',[$currentOperation]));
			}
			$task->setStartedOperation($currentOperation);
			$task->setLastOperationTime( \Utils\Time::getCurrentTimestamp() );
			$task->save();
			log_message(get_text('The current operation is %s, and is now starting',[$currentOperation]));
			try {
				$result = $operation::run($task);
				if ( isset($result) ) {
					$task->setOperationResult($result);
					$task->save();
				}
			} catch (\Throwable $e) {
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
