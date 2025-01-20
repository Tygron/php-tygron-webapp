<?php

	namespace Operations;

	class DeleteTaskFile {

		public function __construct() {

		}

		public static function run($task) {
			global $KEEP_TASKS_WITH_ERROR;

			if ( !empty($task->getError()) ) {
				if ($KEEP_TASKS_WITH_ERROR) {
					return true;
				}
			}
			$task->delete();

			return null;
		}


		public static function checkReadyForOperation($task) {
			return true;
		}

		public static function checkOperationComplete($task, bool $thrown = true) {
			return $task->getOperationResult();
		}
	}


?>
