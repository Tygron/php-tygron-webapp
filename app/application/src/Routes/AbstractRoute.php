<?php

	namespace Routes;

	abstract class AbstractRoute {

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public function startRoute( array $parameters = null ) {
			$parameters = $this->mergeParametersForRoute( array_merge( $parameters, $this->getMiscInputs() ) );
			return $this->run($parameters);
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
