<?php

	namespace Routes;

	abstract class AbstractRoute {

		private ?string $routeMethod = null;
		private ?string $routeRelativeBaseUrl = null;
		private ?string $routePath = null;
		private ?string $routeSubPath = null;

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public function setRoutingParameters( array $parameters = null ) {
			$this->routeMethod = $parameters['method'] ?? null;
			$this->routeRelativeBaseUrl = $parameters['relativeBaseUrl'] ?? null;
			$this->routePath = $parameters['path'] ?? null;
			$this->routeSubPath = $parameters['subPath'] ?? null;
		}

		public function getMethod() {
			return $this->routeMethod;
		}
		public function getRelativeBaseUrl() {
			return $this->routeRelativeBaseUrl;
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
				'relativeBaseUrl' => $this->getRelativeBaseUrl(),
				'path' => $preservePath ? $this->getPath() : null,
				'subPath' => $preservePath ? $this->getSubPath() : null,
			]);
			return $newRoute;
		}

		public abstract function run( array $parameters = null );

		protected function getMiscInputs( ) {
			return [];
		}

		protected function getParameterSetName() {
			return $this->name();
		}

                protected function mergeParametersForRoute( array $parameters ) {
			$parameterSetName = $this->getParameterSetName();
                        return []
                                        + $this->getInjectedParameters($parameterSetName)
                                        + $this->getFixedParameters($parameterSetName)
                                        + $parameters
                                        + $this->getDefaultParameters($parameterSetName)
                                ;
                }

		protected function getDefaultParameters($parameterSetName = null) {
			global $ROUTE_PARAMETERS_DEFAULT;
			$parameterSetName ??= $this->getParameterSetName();
			if ( array_key_exists( $parameterSetName, $ROUTE_PARAMETERS_DEFAULT ?? [] ) ) {
				return $ROUTE_PARAMETERS_DEFAULT[$parameterSetName];
			}
			return [];
		}
		protected function getFixedParameters($parameterSetName = null) {
			global $ROUTE_PARAMETERS_FIXED;
			$parameterSetName ??= $this->getParameterSetName();
			if ( array_key_exists( $parameterSetName, $ROUTE_PARAMETERS_FIXED ?? [] ) ) {
				return $ROUTE_PARAMETERS_FIXED[$parameterSetName];
			}
			return [];
		}
		protected function getInjectedParameters($parameterSetName = null) {
			global $ROUTE_PARAMETERS_INJECTION;
			$parameterSetName ??= $this->getParameterSetName();
			if ( !array_key_exists($parameterSetName, $ROUTE_PARAMETERS_INJECTION ?? [] ) ) {
				return [];
			}
			$injectionRules = $ROUTE_PARAMETERS_INJECTION[$parameterSetName];

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

		protected function getRouteRenderValues() {
			$relativeParentUrl = explode('/', $this->getRelativeBaseUrl());
			if ( count($relativeParentUrl) > 1) {
				array_pop($relativeParentUrl);
			}
			$relativeParentUrl = implode('/', $relativeParentUrl);
			return [
					'relativeBaseUrl' => $this->getRelativeBaseUrl(),
					'prelativeParentUrl' => $relativeParentUrl,
					'subPath' => $this->getSubPath(),
				];
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
			$renderableAsset->setData( ($data ?? []) + $this->getRenderParameters() + $this->getRouteRenderValues() );
			return $renderableAsset;
		}
	}
