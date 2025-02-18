<?php

	namespace Actions;

	class CreateTaskForm extends AbstractAction {

		public function run( array $parameters = null ) {
			return $this->getRenderable( null, $this->mergeParametersForTask($parameters) );
		}
	}

?>
