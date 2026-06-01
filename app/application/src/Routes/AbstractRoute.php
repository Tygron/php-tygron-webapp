<?php

	namespace Routes;

	abstract class AbstractRoute {

		//Request Method
		private ?string $routeMethod = null;

		//Route from the application base url to this url
		private ?string $routeRelativeBaseUrl = null;

		//Path to Route class handling the request
		private ?string $routePath = null;

		//For given RouteEndpoint handling the request, what subpath is found
		private ?string $routeSubPath = null;

		//HTML, JSON, etc
		private ?string $routeFormat = null;

		//What data to see/request, or folder, or to use for differentiation.
		private ?string $requestContext = null;

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public function setRoutingParameters( array $parameters = [] ) {
			$this->routeMethod = $parameters['method'] ?? null;
			$this->routeRelativeBaseUrl = $parameters['relativeBaseUrl'] ?? null;
			$this->routePath = $parameters['path'] ?? null;
			$this->routeSubPath = $parameters['subPath'] ?? null;
			$this->routeFormat = $parameters['format'] ?? null;
			$this->requestContext = $parameters['requestContext'] ?? null;
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

		public function getRequestContext() {
			return $this->requestContext;
		}

		public function startRoute( array $parameters = [] ) {
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
				'format' => $this->getRequestedFormat(),

				'requestContext' => $this->getRequestContext(),
			]);
			return $newRoute;
		}

		public abstract function run( array $parameters = [] );

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

			return $this->getRulesSetForParameters($ROUTE_PARAMETERS_DEFAULT);
		}
		protected function getFixedParameters($parameterSetName = null) {
			global $ROUTE_PARAMETERS_FIXED;

			return $this->getRulesSetForParameters($ROUTE_PARAMETERS_FIXED);
		}
		protected function getInjectedParameters($parameterSetName = null) {
			global $ROUTE_PARAMETERS_INJECTION;

			$rulesSet = $this->getRulesSetForParameters($ROUTE_PARAMETERS_INJECTION);

			$injections = [];
			foreach ( $rulesSet as $key => $rules ) {
				$inputValue = $this->getInputForInjection($key);
				if ( array_key_exists($inputValue, $rules) ) {
					$injections = array_merge($injections, $rules[$inputValue]);
				}
			}

			return $injections;
		}

		private function getRulesSetForParameters ( array $parameterSet, string $parameterSetName = null ) {
			$parameterSetName ??= $this->getParameterSetName();
			$reservedParameterKeys = ['*',$parameterSetName];

			$foundParameterRules = [];
			$foundParameterRules['*'] = $parameterSet['*'] ?? null;

			foreach ( $parameterSet as $key => $value ) {
				if ( in_array($key, $reservedParameterKeys) ) {
					continue;
				}

				$pattern = '/'.$key.'/';
				\Utils\Strings::isValidRegex($pattern);
				if ( preg_match($pattern, $parameterSetName) ) {
					$foundParameterRules[$key] = $parameterSet[$key];
				}
			}

			$foundParameterRules[$parameterSetName] = $parameterSet[$parameterSetName] ?? null;

			$foundParameterRules = array_filter( $foundParameterRules, 'is_array' );
			return array_merge( [], ...array_values($foundParameterRules) );
		}

		private function getInputForInjection( string $key ) {
			switch ( $key ) {
				case 'requestContext':
					return $this->getRequestContext();
				default:
					return get_clean_user_input( $key );
			}
		}

		protected function getRequestedFormat(string $format = null) {
			if ( is_null($format) ) {
				return $this->routeFormat;
			}
			if ( is_null($this->routeFormat) ) {
				return false;
			}
			return strtolower($this->routeFormat) == strtolower($format);
		}

		protected function getRenderParameters() {
			global $RENDER_PARAMETERS;
			return $RENDER_PARAMETERS;
		}

		protected function getRouteRenderValues() {
			$relativeParentUrl = explode('/', $this->getRelativeBaseUrl() ?? '');
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
