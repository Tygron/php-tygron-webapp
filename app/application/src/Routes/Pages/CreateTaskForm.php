<?php

	namespace Routes\Pages;

	class CreateTaskForm extends AbstractPage {

		public function run( array $parameters = null ) {
			return $this->getRenderable( null, array_merge( $parameters, [
					'actionToRun' => 'Actions/CreateTask',
				] ) );
		}
	}

?>
