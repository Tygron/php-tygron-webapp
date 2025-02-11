<?php

	namespace Actions;

	class WaitForTask extends AbstractAction {

		public function run( array $parameters = null ) {
			global $_INPUTS;

			$taskName = get_clean_user_input('task');
			try {
				$task = \Tasks\Task::load($taskName);
			} catch (\Throwable $e) {
				$renderable = $this->getRenderable( 'TaskNotFound', ['taskName' => $taskName] );
				return $renderable->getRendered();

			}

			if ( $task->getTaskCompleted() ) {

				$renderable = $this->getRenderable( 'Redirect', ['action' => 'OutputFromTask&task='.$taskName] );
				return $renderable->getRendered();

			}

			$renderable = $this->getRenderable( null, $task->getData() );
			return $renderable->getRendered();
		}
	}

?>
