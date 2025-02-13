<?php

	namespace Tasks\Operations;

	class Wait extends AbstractOperation{

		protected static int $WAIT_TIME = 3600;

		public function run( \Tasks\Task $task ) {
			$waitTime = $task->getData()['waitTime'] ?? self::$WAIT_TIME;

			$currentTime = \Utils\Time::getCurrentTimestamp();
			$readableStartTime = \Utils\Time::getReadableDateTime($currentTime);
			$readableEndTime = \Utils\Time::getReadableDateTime($waitTime);
			$task->log(get_text( 'Started wait at: %s', [$readableStartTime] ));
			$task->log(get_text( 'Wait until: %s',[$readableEndTime] ));
			$task->save();
			return;
		}

		public function checkReady( \Tasks\Task $task ) {
			return true;
		}

		public function checkComplete( \Tasks\Task $task ) {
			$waitTime = $task->getData()['waitTime'] ?? self::$WAIT_TIME;

			$startTime = $task->getLastOperationTime();
			$currentTime = \Utils\Time::getCurrentTimestamp();

			$period = \Utils\Time::getTimePeriod($startTime, $currentTime);

			return $period >= $waitTime;
		}
	}


?>
