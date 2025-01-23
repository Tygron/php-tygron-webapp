<?php

	namespace Actions;

	abstract class AbstractAction {

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public function startAction( array $parameters = null ) {
			return $this->run( $parameters );
		}

		public abstract function run( array $parameters = null );

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
	}
