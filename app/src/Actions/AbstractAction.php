<?php

	namespace Actions;

	abstract class AbstractAction {

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public function startAction( array $parameters = null ) {
			$parameters = $this->loadParameters($parameters);
			return $this->run( $parameters );
		}

		public abstract function run( array $parameters = null );

		protected function loadParameters( array $parameters = null ) {
			global $ACTION_PARAMETERS_FIXED;
			if ( is_null($parameters) ) {
				$parameters = [];
			}
			if ( array_key_exists($this->name(), $ACTION_PARAMETERS_FIXED ) ) {
				return array_merge(
						$parameters,
						$ACTION_PARAMETERS_FIXED[$this->name()]
					);
			}

			return $parameters;
		}
	}
