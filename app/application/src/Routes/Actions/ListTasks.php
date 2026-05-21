<?php

	namespace Routes\Actions;

	class ListTasks extends AbstractAction {

		public function run( array $parameters = [] ) {
			global $_INPUTS;

			$tasks = \Tasks\Task::list($this->getRequestContext());

			foreach ($tasks as $index => $value) {
				log_message($value);
			}
		}
	}

?>
