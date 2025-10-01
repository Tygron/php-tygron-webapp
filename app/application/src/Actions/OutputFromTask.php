<?php

	namespace Actions;

	class OutputFromTask extends AbstractAction {

		public function run( array $parameters = null ) {
			global $_INPUTS;

			$taskName = get_clean_user_input('task');

			try {
				$task = \Tasks\Task::load($taskName);
			} catch (\Throwable $e) {

				$renderable = $this->getRenderable( 'TaskNotFound', ['taskName' => $taskName] );
				return $renderable;

			}

			if ( empty($task->getOutput()) ) {

				$renderable = $this->getRenderable( 'OutputFromTaskError', array_merge($task->getData(),[
						'taskErrorMessage' => $task->getError()['message'] ?? ''
					] )
				);
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
