<?php

	namespace Operations;

	class Recalculate {

		protected static int $WAIT_TIME = 5;

		public function __construct() {

		}

		public static function run($task) {
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorindicator/reset_indicators',
					[true]
				)->run();

			$task->save();
		}


		public static function checkReadyForOperation($task) {
			try {
				$token = $task->getApiToken();

				//TODO: Check whether project still running

				$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/info')->run();
				if ($curlTask->getContent()['state']=='NORMAL') {
					return true;
				}

			} catch (\Throwable $e) {
				return $e->getMessage();
			}
		}

		public static function checkOperationComplete($task, bool $thrown = true) {

			try {
                                $startTime = $task->getLastOperationTime();
                                $currentTime = \Utils\Time::getCurrentTimestamp();

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
			} catch (\Throwable $e) {
				if ($thrown) {
					throw $e;
				}
				return false;
			}
		}
	}


?>
