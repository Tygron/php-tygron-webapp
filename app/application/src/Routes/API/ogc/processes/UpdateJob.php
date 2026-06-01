<?php

	namespace Routes\API\OGC\Processes;

	use Routes\API\OGC\Processes\Abstracts\AbstractProcess as AbstractProcess;
	use Routes\API\OGC\Processes\Abstracts\GenericOGCProcessParameter;
	use Routes\API\OGC\Processes\Abstracts\GenericOGCProcessOutput;

	class UpdateJob extends AbstractProcess {

		public function run( array $parameters = [] ) {
			return parent::run($parameters);
		}

		public function getTitle() {
			return 'Update Job';
		}

		public function getDescription() {
			return 'Trigger an update in a running job';
		}

		public function isDocumented( $parameters = [] ) {
			return true;
		}

		public function getJobControlOptions() {
			return [self::CONTROL_MODE_SYNC];
		}

		public function getParameterDefinitions() {
			$definitions = [
				GenericOGCProcessParameter::create([
						'name' => 'jobId',
						'title' => 'JobID',
						'description' => 'Job\'s ID to update',
						'type' => 'string',
						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'',
					]),
			];
			return $definitions;
		}

		public function getOutputDefinitions() {
			$outputs = [
				GenericOGCProcessOutput::create([
						'name' => 'message',
						'title' => 'Result message',
						'description' => 'Resulting message from the update of the Job',
						'type' => 'string',
					]),
			];
			return $outputs;
		}

		public function runPost( array $parameters = [] ) {
			global $TASKS_STANDOFF_IN_SECONDS;
			$taskName = $parameters['jobId'] ?? null;
                        try {
                                $task = \Tasks\Task::load($taskName, $this->getRequestContext());
                        } catch (\Throwable $e) {
				return $this->returnError(null, 'Task not found');
                        }

			$result = 'null';

			try {
				$taskRunner = new \Tasks\Runners\SyncModeTaskRunner();
				$taskRunner->setSyncMode(\Tasks\Task::SYNC_MODE_ASYNC, true);
				$taskRunner->setStandOffTimeInSeconds( $TASKS_STANDOFF_IN_SECONDS );
				$taskRunner->setTaskFileName($task->getTaskFileName());
				$result = $taskRunner->run();
				$result = $taskRunner->getLogs();
			} catch ( \Throwable $e ) {
				return $this->returnError(null, $e);
			}

			return $this->returnSuccess([
					'jobId' => $task->getTaskName(),
					'results' => [
							'operated' => $taskRunner->getHasOperated(),
							'completed' => $taskRunner->getHasCompleted(),
						],
					'logs' => [ $result ],
				]);
		}

	}
?>
