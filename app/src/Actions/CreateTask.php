<?php

	namespace Actions;

	class CreateTask extends AbstractAction {

		public function run( array $parameters = null ) {

			$cleanedParameters = \Tasks\Task::cleanParameters($parameters);
			//operations are not in the inputs, so parameters are not cleaned

			$taskData = \Tasks\Task::$DEFAULT_DATA;
			$taskData = array_merge(
					$taskData,
					$cleanedParameters,
				);

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
