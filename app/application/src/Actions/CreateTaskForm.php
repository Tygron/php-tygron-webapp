<?php

	namespace Actions;

	class CreateTaskForm extends AbstractAction {

		public function run( array $parameters = null ) {
			return \Rendering\Renderer::getRendered('form.html');
		}
	}

?>
