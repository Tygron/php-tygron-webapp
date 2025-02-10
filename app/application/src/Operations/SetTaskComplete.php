<?php

	namespace Operations;

	class SetTaskComplete {

		public function __construct() {

		}

		public static function run($task) {

			$task->setTaskCompleted(true);
			$task->save();
		}

		public static function checkReadyForOperation($task) {
			return true;
		}

		public static function checkOperationComplete($task, bool $thrown = true) {
			try {
				return $task->getTaskCompleted();
			} catch (\Throwable $e) {
				if ($thrown) {
					throw $e;
				}
				return false;
			}
		}
	}


?>
