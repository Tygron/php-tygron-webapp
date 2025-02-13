<?php

	namespace Tasks\Operations;

	class DeleteCredentialsFile extends AbstractOperation {

		public function run( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$task->deleteCredentials();
			$task->save();

			return;
		}


		public function checkReady( \Tasks\Task $task ) {
			return true;
		}

		public function checkComplete( \Tasks\Task $task ) {
			if (!empty($task->getCredentialsFileName())) {
				return false;
			}
			return true;
		}
	}


?>
