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

				$asset = self::computeReplaceAsArray($asset, $key, $value, $data);

				$value = self::implodeDataIfPossible($value);
				$asset = self::computeReplaceSimple($asset, $key, $value, $data);
			}
			return $asset;
		}

		public static function clean( string $asset ) {
			$asset = preg_replace('/{{(\s)*(.+?)(\s)*}}/','',$asset);
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

		private static function computeReplaceAsArray( $asset, $key, $value, array $context = null ) {
			//{% for item in items %}{% include 'template_per_element.html' with { item: item } only %}{% endfor %}

			// Pattern to capture:
			// - for loop over $key
			// - include template (dynamic)
			// - "with item"
			// - optional "only"
			$pattern = '/\{\%\s*for\s+item\s+in\s+' . preg_quote($key, '/') . '\s*\%\}\s*' .
				'\{\%\s*include\s+[\'"]([^\'"]+)[\'"]\s+' .
				'with\s+item' .
				'(\s+only)?\s*\%\}\s*' .
				'\{\%\s*endfor\s*\%\}/s';

			$matches = [];

			if (!preg_match($pattern, $asset, $matches)) {
				return $asset;
			}

			$detectionResult = [
				'fullMatch' => $matches[0],           // entire matsching string
				'template'   => $matches[1],           // template filename
				'only'       => isset($matches[2]) && trim($matches[2]) === 'only', // Full context or only the indicated object/entry
			];
			$replacementString = '';
			foreach ($value as $key => $entry) {
				$data = $entry + ($detectionResult['only'] ? [] : $context ?? []);
				$replacementString .=self::renderFromFile($detectionResult['template'],$data);
			}
			$asset = str_replace($detectionResult['fullMatch'], $replacementString, $asset);
			return $asset;

		}

		private static function computeReplaceSimple( $asset, $key, $value, array $context = null ) {
			$asset = preg_replace('/{{(\s)*'.$key.'(\s)*}}/', $value ?? '', $asset);
			return $asset;
		}

	}

?>
