<?php

	namespace Routes\API;
	use Routes\API\AbstractAPI;

	class OGC extends AbstractAPI {

		public function run( array $parameters = [] ) {
			return $this->getRenderable( 'api/ogc/ogc-index.html', $parameters );
		}
	}


?>
