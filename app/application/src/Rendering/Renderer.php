<?php

	namespace Rendering;

	class Renderer {

		private static array $assetSources = [];

		public static function addAssetSource( array|string $sources ) {
			self::_addAssetSource($sources);
		}

		public static function getRendered( string $asset = null, array $data = null ) {
			$asset = self::getAsset( $asset );
			$asset = self::populate( $asset, $data );
			$asset = self::clean( $asset );
			return $asset;
		}

		public static function getAsset( string $asset = null ) {
			foreach (self::getAssetSources() as $index => $source ) {
				try {
					$result = '';
					$result.= '<!--'.$asset.' from '.$source.'  -->'.PHP_EOL.PHP_EOL;
					$result.= \Utils\Files::readFile([$source, $asset]);
					return $result;
				} catch ( \Throwable $e ) {
					continue;
				}
			}
			throw new \Exception(get_text('Asset not found in %s location(s): %s', [count(self::getAssetSources()), $asset]));
		}

		public static function populate( string $asset = null, array $data = null ) {
			if ( is_null($data) ) {
				return $asset;
			}
			foreach ( $data as $key=>$value ) {
				if (is_array($value)) {
					$can_be_imploded = true;
					foreach ($value as $subKey => $subValue) {
						$can_be_imploded = false;
						break;
					}
					$value = $can_be_imploded ? '['.implode(',',$value).']' : '('.get_text('multiple values').')';
				}
				$asset = preg_replace('/{{(\s)*'.$key.'(\s)*}}/',$value ?? '', $asset);
			}
			return $asset;
		}

		public static function clean( string $asset ) {
			$asset = preg_replace('/{{(\s)*(.*)(\s)*}}/','',$asset);
			return $asset;
		}

		public static function getAssetSources( ) {
			return self::$assetSources;
		}

		protected static function _addAssetSource( array|string $sources ) {
			if ( !is_array($sources) ) {
				$sources = [$sources];
			}
			foreach ( $sources as $index => $value ) {
				if ( is_dir($value) ) {
					array_push( self::$assetSources, $value );
				}
			}
		}


	}

?>
