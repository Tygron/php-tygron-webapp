<?php

	namespace Actions;

	class CreateTaskForm extends AbstractAction {

		public function run( array $parameters = null ) {

			return \get_html('form.html');
		}
	}

?>
