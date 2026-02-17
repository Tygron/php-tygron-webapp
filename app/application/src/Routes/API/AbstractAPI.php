<?php

	namespace Routes\API;
	use Routes\AbstractRoute;

	abstract class AbstractAPI extends AbstractRoute {

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public function startRoute( array $parameters = [] ) {
			$parameters = $this->mergeParametersForRoute( array_merge( $parameters, $this->getMiscInputs() ) );
			return $this->run($parameters);
		}

		public abstract function run( array $parameters = [] );

		public function isDocumented( array $parameters = [] ) {
			return true;
		}
		public function isAllowed( array $parameters = [] ) {
			return true;
		}

		protected function getRenderableDefaultNames() {
			return [$this->name(), 'api'];
		}
	}
