<?php

	namespace Routes\Pages;

	class WaitForTask extends AbstractPage {

		public function run( array $parameters = [] ) {
			global $_INPUTS;

			$taskName = get_clean_user_input('taskName');
			try {
				$task = \Tasks\Task::load($taskName);
			} catch (\Throwable $e) {
				$renderable = $this->getRenderable( 'TaskNotFound', ['taskName' => $taskName, 'message' => $e->getMessage()] );
				return $renderable->getRendered();

			}

			if ( $task->getTaskCompleted() ) {

				$renderable = $this->getRenderable( 'Redirect', array_merge( [], [
						'redirectLink' => 'OutputFromTask?taskName='.$taskName
					] ) );
				return $renderable->getRendered();

			}

			$renderable = $this->getRenderable( null, [
					 'actionToRun' => 'Actions/UpdateTask',
				] + $task->getData() );
			return $renderable->getRendered();
		}
	}

?>
