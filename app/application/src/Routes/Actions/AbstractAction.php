<?php

	namespace Routes\Actions;
	use Routes\AbstractRoute;

	abstract class AbstractAction extends AbstractRoute {

                public function getRenderableDefaultName() {
                        return [ implode(DIRECTORY_SEPARATOR,['Actions', $this->name()]), $this->name() ];
                }

		protected function runApi( string $apiEndpoint = null, array $parameters = [], string $method = 'POST' ) {
			$result = $this->loadReroute( $apiEndpoint ?? $this->getApiEndpoint() );
			$result->setRoutingParameters( ['method'=>$method] );
			$result = $result->startRoute( $parameters );
			if ( $result['success'] ) {
				return $result['content'];
			} else {
				throw new \Exception( $result['error'] );
			}
		}

		protected function getApiEndpoint() {
			throw \Exception('No API endpoint defined for Action');
		}
	}
