<?php

	namespace Operations;

	class DeleteCredentialsFile {

		public function __construct() {

		}

		public static function run($task) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$task->deleteCredentials();
			$task->save();

			return;
		}


		public static function checkReadyForOperation($task) {
			return true;
		}

		public static function checkOperationComplete($task, bool $thrown = true) {
			if (!empty($task->getCredentialsFileName())) {
				return false;
			}
			return true;
		}
	}


?>
