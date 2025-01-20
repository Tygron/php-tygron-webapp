<?php

	namespace Actions;

	class CreateTask extends AbstractAction {

		public function run( array $parameters = null ) {
			global $_INPUTS;

			$cleanedParameters = $parameters ?? \Tasks\Task::cleanParameters($_INPUTS);

			$taskData = \Tasks\Task::$DEFAULT_DATA;
			$taskData = array_merge($taskData, $cleanedParameters);

			//TODO: Add config parameters

			try {
				$task = \Tasks\Task::generateTask($taskData);
			} catch ( \Throwable $e ) {
				$message  = '<p>'.get_text('Something went wrong').':</p>';
				$message .= '<p>'.get_text($e->getMessage()).'</p>';
				return $message;
			}

			return get_text('<meta http-equiv="refresh" content="0; url=/?action=OutputFromTask&task=%s" />',[$task->getTaskName()]);
		}
	}

?>
