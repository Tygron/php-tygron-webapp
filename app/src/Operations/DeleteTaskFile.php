<?php

	namespace Operations;

	class DeleteTaskFile {

		public function __construct() {

		}

		public static function run($task) {

			$task->delete();

			return;
		}


		public static function checkReadyForOperation($task) {
			return true;
		}

		public static function checkOperationComplete($task, bool $thrown = true) {
			return false;
		}
	}


?>
