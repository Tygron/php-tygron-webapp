<?php

	namespace Actions;

	abstract class AbstractAction {

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public function startAction( array $parameters = null ) {
			$parameters = $this->mergeParametersForTask( array_merge( $parameters,$this->getInputsWithSpecialCharacters() ) );
			return $this->run($parameters);
		}

		public abstract function run( array $parameters = null );

		protected function getInputsWithSpecialCharacters( ) {
			return [];
		}

                protected function mergeParametersForTask( array $parameters ) {
                        return []
                                        + $this->getInjectedParameters()
                                        + $this->getFixedParameters()
                                        + $parameters
                                        + $this->getDefaultParameters()
                                ;
                }

		protected function getDefaultParameters() {
			global $ACTION_PARAMETERS_DEFAULT;
			if ( array_key_exists($this->name(), $ACTION_PARAMETERS_DEFAULT ) ) {
				return $ACTION_PARAMETERS_DEFAULT[$this->name()];
			}
			return [];
		}
		protected function getFixedParameters() {
			global $ACTION_PARAMETERS_FIXED;
			if ( array_key_exists($this->name(), $ACTION_PARAMETERS_FIXED ) ) {
				return $ACTION_PARAMETERS_FIXED[$this->name()];
			}
			return [];
		}
		protected function getInjectedParameters() {
			global $ACTION_PARAMETERS_INJECTION;
			if ( !array_key_exists($this->name(), $ACTION_PARAMETERS_INJECTION ) ) {
				return [];
			}
			$injectionRules = $ACTION_PARAMETERS_INJECTION[$this->name()];

			$injections = [];
			foreach ($injectionRules as $key => $rulesForKey) {
				$inputValue = get_clean_user_input($key);
				if ( array_key_exists($inputValue, $rulesForKey) ) {
					$injections = array_merge($injections, $rulesForKey[$inputValue]);
				}
			}
			return $injections;
		}
	}
