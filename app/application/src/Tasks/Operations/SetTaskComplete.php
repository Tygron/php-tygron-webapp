<?php

	namespace Tasks\Operations;

	class SetTaskComplete extends AbstractOperation {

		public function run( \Tasks\Task $task ) {

			$task->setTaskCompleted(true);
			$task->save();
		}

		public function checkReady( \Tasks\Task $task ) {
			return true;
		}

		public function checkComplete( \Tasks\Task $task ) {
			return $task->getTaskCompleted();
		}
	}


?>
