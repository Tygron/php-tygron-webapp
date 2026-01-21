<?php

	namespace Routes\Pages;

	class OutputFromTask extends AbstractPage {

		public function run( array $parameters = [] ) {
			global $_INPUTS;

			$taskName = get_clean_user_input('taskName');

			try {
				$task = \Tasks\Task::load($taskName);
			} catch (\Throwable $e) {

				$renderable = $this->getRenderable( 'TaskNotFound', ['taskName' => $taskName] );
				return $renderable;

			}

			if ( empty($task->getOutput()) ) {

				$renderable = $this->getRenderable( 'OutputFromTaskError', array_merge($task->getData(),[
						'redirectLink' => 'Pages/CreateTaskForm',
						'taskErrorMessage' => $task->getError()['message'] ?? '',
					] ) );

				return $renderable;

			}

			$renderable = $this->getRenderable( null, array_merge(
					$task->getData(),
					$task->getOutput()
				) );
			return $renderable;

		}
	}

?>
