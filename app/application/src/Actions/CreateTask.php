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
			if ( array_key_exists('areaOfInterest', $_INPUTS) ) {
				$inputs['areaOfInterest'] = get_clean_user_input('areaOfInterest', '[\{\}\[\]\s\-\_\,\.\'\"\:a-zA-Z0-9]');
				$inputs['areaOfInterest'] = json_decode($inputs['areaOfInterest'], true);
			}
			return $inputs;
		}

		protected function getAllowedExtraParameters() {
			//TODO: Obtain dynamically from (potential) operations
			return [
					'areaOfInterest'		=>	null,
					'areaOfInterestAttributes'	=>	[],
				];
		}

		protected function mergeParametersForTask( array $parameters ) {
			$allowed = $this->getAllowedExtraParameters();
			$flatMerge = []
					+ \Tasks\TaskGenerator::normalizeParameters($this->getInjectedParameters(), $allowed)
					+ \Tasks\TaskGenerator::normalizeParameters($this->getFixedParameters(), $allowed)
					+ \Tasks\TaskGenerator::normalizeParameters($parameters, $allowed)
					+ \Tasks\TaskGenerator::normalizeParameters($this->getDefaultParameters(), $allowed)
				;
			return $flatMerge;
		}

	}
?>
