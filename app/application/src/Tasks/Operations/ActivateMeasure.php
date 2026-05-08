<?php

	namespace Tasks\Operations;

	class ActivateMeasure extends AbstractOperation {

		protected static string $MEASURE = 'measure';
		protected static string $STOP_TESTRUN = 'stopTestRun';
		protected static string $STOPPED_TESTRUN = 'stoppedTestRun';
		protected static string $STATE_TESTRUN = 'TESTRUN';

		protected static string $STATE_NOTHING = 'NOTHING';
		protected static int $DEFAULT_STAKEHOLDER = -1;

		public function getInputParameters() {
			return [
					self::$STOP_TESTRUN => false,
					self::$MEASURE => null,
				];
		}

		public function run( \Tasks\Task $task ) {
			$token = $task->getApiToken();

			$this->stopTestRun($task);

			$measureIds = $this->getMeasuresToActivate( $task );
			if ( is_null($measureIds) || count($measureIds) === 0 ) {
				return;
			}

			foreach ( $measureIds as $key => $measureId ) {

				$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
						'api/session/event/participant/measure_plan_construction',
						[self::$DEFAULT_STAKEHOLDER, $measureId]
					)->run();
				$task->log(get_text('Measure with ID %s activated', [$measureId]));
			}
			$task->save();
		}

		public function checkReady( \Tasks\Task $task ) {
			return !empty($task->getApiToken( $task ));
		}

		public function checkComplete( \Tasks\Task $task ) {

			//TODO: Check whether project still running
			$measuresToActivate = count( $this->getMeasuresToActivate( $task ) );

			$stopTestRun = $task->getData(self::$STOP_TESTRUN);
			$stopTestRun = is_string($stopTestRun) ? strtolower($stopTestRun) == 'true' : $stopTestRun;

			$stoppedTestRun = $task->getData(self::$STOPPED_TESTRUN);
			$stoppedTestRun = is_string($stoppedTestRun) ? strtolower($stoppedTestRun) == 'true' : $stoppedTestRun;

			//$task->log(get_text('Measures left to activate: ', [$measuresToActivate]));
			//$task->save();

			if ( $measuresToActivate > 0 ) {
				return false;
			}

			if ( $stopTestRun && (!$stoppedTestRun) ) {
				return false;
			}

			return true;
		}


		protected function stopTestRun( \Tasks\Task $task ) {

			$stopTestRun = $task->getData(self::$STOP_TESTRUN);
			$stopTestRun = is_string($stopTestRun) ? strtolower($stopTestRun) == 'true' : $stopTestRun;

			if ( !$stopTestRun ) {
				return;
			}

			$token = $task->getApiToken();
			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/info')->run();
			$sessionState = $curlTask->getContent()['state'];

			if ( $sessionState == self::$STATE_TESTRUN ) {
				$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(), 'api/session/event/editor/activate_testrun', [false])->run();
			}
			$task->setData([ self::$STOPPED_TESTRUN => true ]);
			$task->save();
			return;
		}

		protected function getMeasuresToActivate( \Tasks\Task $task ) {
			$measureIds = $task->getData(self::$MEASURE) ?? null;
			if ( is_null($measureIds) ) {
				return [];
			}
			if ( !is_array($measureIds) ) {
				$measureIds = [$measureIds];
			}
			$foundMeasureIds = [];

			$token = $task->getApiToken();
			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/measures')->run();

			foreach ( $curlTask->getContent() as $index => $measure ) {
				$measureId = $measure['id'];
				$measureName = $measure['name'];

				if ( $measure['state'] != self::$STATE_NOTHING ) {
					continue;
				}

				if ( in_array($measureId, $measureIds) ) {
					$foundMeasureIds[] = $measureId;
					continue;
				}
				if ( in_array($measureName, $measureIds) || in_array(strtolower($measureName), $measureIds) ) {
					$foundMeasureIds[] = $measureId;
					continue;
				}
			}
			return $foundMeasureIds;
		}
	}


?>
