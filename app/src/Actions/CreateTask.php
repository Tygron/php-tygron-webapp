<?php

	namespace Actions;

	class CreateTask extends AbstractAction {

		public function run( array $parameters = null ) {
			global $_INPUTS;

			$cleanedParameters = $parameters ?? \Tasks\Task::cleanParameters($_INPUTS);

			$task = \Tasks\Task::generateTask($cleanedParameters);
			return $task->getTaskName();
		}
	}

?>
