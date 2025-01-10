<?php

	namespace Actions;

	class ListTasks extends AbstractAction {

		public function run( array $parameters = null ) {
			global $_INPUTS;

			$tasks = \Tasks\Task::list();

			foreach ($tasks as $index => $value) {
				log_message($value);
			}
		}
	}

?>
