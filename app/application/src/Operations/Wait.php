<?php

	namespace Operations;

	class Wait {

		protected static int $WAIT_TIME = 3600;

		public function __construct() {

		}

		public static function run($task) {
			$waitTime = $task->getData()['waitTime'] ?? self::$WAIT_TIME;

			$currentTime = \Utils\Time::getCurrentTimestamp();
			$readableStartTime = \Utils\Time::getReadableDateTime($currentTime);
			$readableEndTime = \Utils\Time::getReadableDateTime($waitTime);
			$task->log(get_text( 'Started wait at: %s', [$readableStartTime] ));
			$task->log(get_text( 'Wait until: %s',[$readableEndTime] ));
			$task->save();
			return;
		}

		public static function checkReadyForOperation($task) {
			return true;
		}

		public static function checkOperationComplete($task, bool $thrown = true) {
			try {
				$waitTime = $task->getData()['waitTime'] ?? self::$WAIT_TIME;

				$startTime = $task->getLastOperationTime();
				$currentTime = \Utils\Time::getCurrentTimestamp();

				$period = \Utils\Time::getTimePeriod($startTime, $currentTime);

				return $period >= $waitTime;
			} catch (\Throwable $e) {
				if ($thrown) {
					throw $e;
				}
				return false;
			}
		}
	}


?>
