<?php

	namespace Tasks\Operations;

	class DeleteTaskFile extends AbstractOperation {

		public function run( \Tasks\Task $task ) {
			global $KEEP_TASKS_WITH_ERROR;

			if ( !empty($task->getError()) ) {
				if ($KEEP_TASKS_WITH_ERROR) {
					return true;
				}
			}
			$task->delete();

			return null;
		}


		public function checkReady( \Tasks\Task $task ) {
			return true;
		}

		public function checkComplete( \Tasks\Task $task ) {
			return $task->getOperationResult();
		}
	}


?>
