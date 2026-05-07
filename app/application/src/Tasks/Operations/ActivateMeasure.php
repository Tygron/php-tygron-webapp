<?php

	namespace Tasks\Operations;

	class ActivateMeasure extends AbstractOperation {

		protected static string $STATE_NOTHING = 'NOTHING';
		protected static int $DEFAULT_STAKEHOLDER = -1;

		public function getInputParameters() {
			return [
					'measure' => null
				];
		}

		public function run( \Tasks\Task $task ) {
			$token = $task->getApiToken();

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

			//$task->log(get_text('Measures left to activate: ', [$measuresToActivate]));
			//$task->save();

			return ( $measuresToActivate === 0 );
		}




		protected function getMeasuresToActivate( $task ) {
			$measureIds = $task->getData('measure') ?? null;
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
