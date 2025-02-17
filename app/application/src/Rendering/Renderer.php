<?php

	namespace Rendering;

	class Renderer {

		public static function getRendered( string $asset = null, array $data = null ) {
			$asset = \Assets\AssetReader::getAsset( $asset, 'html' );
			$asset = self::populate( $asset, $data );
			$asset = self::clean( $asset );
			return $asset;
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

	}

?>
