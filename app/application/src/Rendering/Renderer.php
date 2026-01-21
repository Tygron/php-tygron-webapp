<?php

	namespace Rendering;

	class Renderer {

		public static function renderFromFile( string $file, array $data = null ) {
			$asset = \Assets\AssetReader::getAsset( $file, null );
			return self::renderFromTemplate($asset, $data);
		}

		public static function renderFromTemplate( string $template, array $data = null) {
			$asset = self::populate( $template, $data );
			$asset = self::clean( $asset );
			return $asset;
		}

		public static function populate( string $asset = null, array $data = null ) {
			if ( is_null($data) ) {
				return $asset;
			}
			foreach ( $data as $key => $value ) {

				$asset = self::computeReplaceAsArray($asset, $key, $value);

				$value = self::implodeDataIfPossible($value);
				$asset = self::computeReplaceSimple($asset, $key, $value);
			}
			return $asset;
		}

		public static function clean( string $asset ) {
			$asset = preg_replace('/{{(\s)*(.*)(\s)*}}/','',$asset);
			return $asset;
		}

		private static function implodeDataIfPossible($value, string $default = 'multiple values') {
			if (is_array($value)) {
				$can_be_imploded = true;
				foreach ($value as $subKey => $subValue) {
					$can_be_imploded = false;
					break;
				}
				$value = $can_be_imploded ? '['.implode(',',$value).']' : '('.get_text($default).')';
			}
			return $value;
		}

		private static function computeReplaceAsArray( $asset, $key, $value ) {
			//{% for item in items %}{% include 'template_per_element.html' with { item: item } only %}{% endfor %}
			return $asset;
		}

		private static function computeReplaceSimple( $asset, $key, $value ) {
			$asset = preg_replace('/{{(\s)*'.$key.'(\s)*}}/', $value ?? '', $asset);
			return $asset;
		}

	}

?>
