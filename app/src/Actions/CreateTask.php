<?php

	namespace Actions;

	class CreateTask extends AbstractAction {

		public function run( array $parameters = null ) {
			global $ACTION_PARAMETERS_DEFAULT, $ACTION_PARAMETERS_FIXED;
			echo '<pre>';

			$parameters = array_merge($parameters, $this->getInputsWithSpecialCharacters() );
			var_dump($parameters);

			$parameters = $this->mergeParametersForTask( $parameters );


			try {
				$task = \Tasks\TaskGenerator::generate($parameters);
			} catch ( \Throwable $e ) {
				$message  = '<p>'.get_text('Something went wrong').':</p>';
				$message .= '<p>'.get_text($e->getMessage()).'</p>';
				throw $e;
				return $message;
			}
			var_dump($task);

			$task->save();

			var_dump($task);

			return $task->getTaskName();
			return get_text('<meta http-equiv="refresh" content="0; url=/?action=OutputFromTask&task=%s" />',[$task->getTaskName()]);
		}

		protected function getInputsWithSpecialCharacters() {
			global $_INPUTS;
			$inputs = [];
			if ( array_key_exists('username', $_INPUTS) ) {
				$inputs['username'] = get_clean_user_input('username', '[\.\-_@+a-zA-Z0-9]');
			}
			return $inputs;
		}

		protected function mergeParametersForTask( array $parameters ) {
			$flatMerge = []
					+ \Tasks\TaskGenerator::normalizeParameters($this->getFixedParameters())
					+ \Tasks\TaskGenerator::normalizeParameters($parameters)
					+ \Tasks\TaskGenerator::normalizeParameters($this->getDefaultParameters())
				;
			return $flatMerge;
		}

	}
?>
