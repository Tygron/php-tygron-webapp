<?php

	namespace Tasks\Operations;

	class Recalculate extends AbstractOperation {

		protected static int $WAIT_TIME = 5;

		public function run( \Tasks\Task $task ) {
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorindicator/reset_indicators',
					[true]
				)->run();

			$task->save();
		}


		public function checkReady( \Tasks\Task $task ) {
			try {
				$token = $task->getApiToken();

				//TODO: Check whether project still running

				$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/info')->run();
				$state = $curlTask->getContent()['state'];
				if ( $state=='NORMAL' ) {
					return true;
				} else {
					$task->log(get_text('Session in unexpected state: %s',[$state]));
					$task->save();
					return false;
				}

			} catch (\Throwable $e) {
				return $e->getMessage();
			}
		}

		public function checkComplete( \Tasks\Task $task ) {

                        $startTime = $task->getLastOperationTime();
                        $currentTime = \Utils\Time::getCurrentTimestamp();

			$waitTime = $task->getData()['recalculateWaitTime'] ?? self::$WAIT_TIME;

			$period = \Utils\Time::getTimePeriod($startTime, $currentTime);
			if ( $period<$waitTime ) {
				return false;
			}

			//TODO: Check whether project still running

			$token = $task->getApiToken();
			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/gpu/jobs')->run();
			if ( !(is_null($curlTask) || $curlTask->getContent() == []) ) {
				return false;
			}
			return true;
		}
	}


?>
