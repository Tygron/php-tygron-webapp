<?php

	namespace Actions;

	class CreateTaskForm extends AbstractAction {

		public function run( array $parameters = null ) {

			return \Utils\Files::readFile([__DIR__,'..','..','form.html']);
		}
	}

?>
