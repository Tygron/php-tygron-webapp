<?php

	namespace Actions;

	class CreateTask extends AbstractAction {

		public function run( array $parameters = null ) {
			global $_INPUTS;

			$cleanedParameters = $parameters ?? \Tasks\Task::cleanParameters($_INPUTS);

			$task = \Tasks\Task::generateTask($cleanedParameters);
			return get_text('<meta http-equiv="refresh" content="0; url=/?action=OutputFromTask&task=%s" />',[$task->getTaskName()]);
		}
	}

?>
