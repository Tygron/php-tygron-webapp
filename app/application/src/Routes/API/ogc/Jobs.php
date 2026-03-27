<?php

	namespace Routes\API\OGC;

	class Jobs extends \Routes\API\AbstractAPIEndpoint {

		public function run( array $parameters = [] ) {
			try {
				return $this->runIntendedOperation();
			} catch ( \Throwable $e) {
				return $this->returnError(null, $e);
			}
		}

		protected function runIntendedOperation() {
			$subPath = $this->getSubPath() ?? '';
			$subPath = empty($subPath) ? [] : explode('/', $this->getSubPath());

			switch( count($subPath) ) {
				case 0:
					return $this->getListOfJobs();
				case 1:
					return $this->getJobStatus($subPath[0]);
				case 2:
				default:
					return $this->getJobSpecifics($subPath[0],$subPath[1]);
			}
		}


		protected function getListOfJobs() {
			return \Tasks\Task::list();
		}

		protected function getJobStatus( string $jobId = null ) {
			return $this->getJobSpecifics($jobId, 'status');
		}

		protected function getJobSpecifics( string $jobId = null, string $action = null ) {
			$task = $this->getJobData($jobId);

			switch( $action ) {
				case 'results':
					return $this->getTaskAsJobOutput($task);
				case 'status':
				default:
					return [
						'type' => 'process',
						'processID' => 'task',
						'jobID' => $task->getTaskName(),
						'status' => $this->getTaskAsJobStatus($task),
					];
			}
		}

		protected function getTaskAsJobStatus(\Tasks\Task $task) {
			if ( !empty($task->getError()) ) {
				return 'failed';
			}
			if ( $task->getTaskCompleted() ) {
				return 'successful';
			}
			if ( empty($task->getStartedOperation()) ) {
				return 'accepted';
			}
			return 'running';
		}

		protected function getTaskAsJobOutput(\Tasks\Task $task) {
			$error = null;
			if ( $this->getTaskAsJobStatus($task) == 'failed' ) {
				try {
					$error = $task->getError()['message'];
				} catch (\Throwable $e) {
					$error = $task->getError();
				}
			}
			if ( !is_null($error) ) {
				return ['error' => $error];
			}
			return $task->getOutput();
		}

		protected function getJobData(string $taskId) {
			try {
				return \Tasks\Task::load($taskId);
			} catch( \Throwable $e) {
				throw new \Exception('Job not found');
			}
		}

	}

?>
