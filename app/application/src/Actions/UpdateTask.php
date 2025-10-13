<?php

	namespace Actions;

	class UpdateTask extends AbstractAction {

		public function run( array $parameters = null ) {
			global $_INPUTS;

			$taskName = get_clean_user_input('taskName');
			try {
				$task = \Tasks\Task::load($taskName);
			} catch (\Throwable $e) {
				return 'Task not found';
			}

			$result = get_text('No output (possible error?)');
                        try {
                                $taskRunner = new \Tasks\Runners\TaskRunner();
                                $taskRunner->setTask($taskName);
                                $result = $taskRunner->run();
                        } catch ( \Throwable $e) {
				$result = $e->getMessage();
			}
			echo 'Output: '.$result;
		}
	}

?>
