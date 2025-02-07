<?php

	namespace Actions;

	class CreateTask extends AbstractAction {

		public function run( array $parameters = null ) {
			try {
				$task = \Tasks\TaskGenerator::generate($parameters);
				$task->validate();
			} catch ( \Throwable $e ) {

				return $this->getRenderable( 'CreateTaskError', ['message'=>$e->getMessage()] );
			}

			$task->save();

			return $this->getRenderable( null, [ 'taskName' => $task->getTaskName() ]);
		}

		protected function getInputsWithSpecialCharacters() {
			global $_INPUTS;
			$inputs = [];
			if ( array_key_exists('username', $_INPUTS) ) {
				$inputs['username'] = get_clean_user_input('username', '[\.\-_@+a-zA-Z0-9]');
			}
			if ( array_key_exists('locationX', $_INPUTS) ) {
				$inputs['locationX'] = get_clean_user_input('locationX', '[\.\-0-9]');
			}
			if ( array_key_exists('locationY', $_INPUTS) ) {
				$inputs['locationY'] = get_clean_user_input('locationY', '[\.\-0-9]');
			}
			return $inputs;
		}

		protected function mergeParametersForTask( array $parameters ) {
			$flatMerge = []
					+ \Tasks\TaskGenerator::normalizeParameters($this->getInjectedParameters())
					+ \Tasks\TaskGenerator::normalizeParameters($this->getFixedParameters())
					+ \Tasks\TaskGenerator::normalizeParameters($parameters)
					+ \Tasks\TaskGenerator::normalizeParameters($this->getDefaultParameters())
				;
			return $flatMerge;
		}

	}
?>
