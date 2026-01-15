<?php

	namespace Routes;

	abstract class AbstractRoute {

		private ?string $routeMethod = null;
		private ?string $routePath = null;
		private ?string $routeSubPath = null;

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public function setRoutingParameters( array $parameters = null ) {
			$this->routeMethod = $parameters['method'] ?? null;
			$this->routePath = $parameters['path'] ?? null;
			$this->routeSubPath = $parameters['subPath'] ?? null;
		}

		public function getMethod() {
			return $this->routeMethod;
		}
		public function getPath() {
			return $this->routePath;
		}
		public function getSubPath() {
			return $this->routeSubPath;
		}

		public function startRoute( array $parameters = null ) {
			$parameters = $this->mergeParametersForRoute( array_merge( $parameters, $this->getMiscInputs() ) );
			return $this->run($parameters);
		}

		public function loadReroute ( $newRoute, bool $preservePath = false ) {
			if (is_string($newRoute) ) {
				$newRoute = explode('/',$newRoute);
				$newRoute = implode('\\', $newRoute);
			}
			$newRoute = new $newRoute();

			$newRoute->setRoutingParameters([
				'method' => $this->getMethod(),
				'path' => $preservePath ? $this->getPath() : null,
				'subPath' => $preservePath ? $this->getSubPath() : null,
			]);
			return $newRoute;
		}

		public abstract function run( array $parameters = null );

		protected function getMiscInputs( ) {
			return [];
		}

                protected function mergeParametersForRoute( array $parameters ) {
                        return []
                                        + $this->getInjectedParameters()
                                        + $this->getFixedParameters()
                                        + $parameters
                                        + $this->getDefaultParameters()
                                ;
                }

		protected function getDefaultParameters() {
			global $ROUTE_PARAMETERS_DEFAULT;
			if ( array_key_exists($this->name(), $ROUTE_PARAMETERS_DEFAULT ?? [] ) ) {
				return $ROUTE_PARAMETERS_DEFAULT[$this->name()];
			}
			return [];
		}
		protected function getFixedParameters() {
			global $ROUTE_PARAMETERS_FIXED;
			if ( array_key_exists($this->name(), $ROUTE_PARAMETERS_FIXED ?? [] ) ) {
				return $ROUTE_PARAMETERS_FIXED[$this->name()];
			}
			return [];
		}
		protected function getInjectedParameters() {
			global $ROUTE_PARAMETERS_INJECTION;
			if ( !array_key_exists($this->name(), $ROUTE_PARAMETERS_INJECTION ?? [] ) ) {
				return [];
			}
			$injectionRules = $ROUTE_PARAMETERS_INJECTION[$this->name()];

			$injections = [];
			foreach ($injectionRules as $key => $rulesForKey) {
				$inputValue = get_clean_user_input($key);
				if ( array_key_exists($inputValue, $rulesForKey) ) {
					$injections = array_merge($injections, $rulesForKey[$inputValue]);
				}
			}
			return $injections;
		}

		protected function getRenderParameters() {
			global $RENDER_PARAMETERS;
			return $RENDER_PARAMETERS;
		}

		protected function getRenderableDefaultNames() {
			return $this->name();
		}

		protected function getRenderableNames( string|array $assetNames = null ) {
			if ( is_null($assetNames) ) {
				$assetNames = $this->getRenderableDefaultNames();
			}
			if ( !is_array($assetNames) ) {
				$assetNames = [$assetNames];
			}
			foreach ($assetNames as $index => $assetName) {
				if ( strlen(\Utils\Files::getFileExtension($assetName)) == 0 ) {
					$assetNames[$index] = $assetName . '.html';
				}
			}
			return $assetNames;
		}

		protected function getRenderable( string $assetName = null, array|null $data = null ) {
			$assetNames = $this->getRenderableNames( $assetName );
			$asset = \Assets\AssetReader::getAsset(
					$assetNames,
					'html'
				);
			$renderableAsset = new \Rendering\RenderableAsset();
			$renderableAsset->setAsset( $asset );
			$renderableAsset->setData( ($data ?? []) + $this->getRenderParameters() );
			return $renderableAsset;
		}
	}
