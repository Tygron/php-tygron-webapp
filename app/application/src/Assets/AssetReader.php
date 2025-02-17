<?php

	namespace Assets;

	class AssetReader {

		private static string|array $DEFAULT_ASSET_DIR = [];

		public static function addSource( array|string $sources) {
			self::_addAssetSourceDir($sources);
		}

		public static function getAsset( string $assetName, string $assetType = null) {
			$sources = self::getAssetSources($assetType);
			foreach ( $sources as $index => $source ) {
				$fileContent = null;
				try {
					$fileContent = \Utils\Files::readFile([$source, $assetName]);
				} catch ( \Throwable $e ) {
					continue;
				}

				$asset = new \Assets\Asset(
						$fileContent,
						$assetType,

						$assetName,
						$source
					);
				return $asset;
			}
			throw new \Exception(get_text('Asset not found in %s location(s): %s', [count($sources), $assetName]));
		}

		public static function getAssetSources( string $assetType = null, string|array $assetDir = null) {
			$results = [];
			if ( is_null($assetDir) ) {
				$assetDir = self::$DEFAULT_ASSET_DIR;
			}
			if ( is_string($assetDir) ) {
				$assetDir = [$assetDir];
			}
			foreach ( $assetDir as $key => $value ) {
				$path = \Utils\Files::makePath($value);
				if ( !is_null($assetType) ) {
					$path = \Utils\Files::makePath([$path, $assetType]);
				}
				if ( is_dir($path) ) {
					array_push( $results, $path );
				}
			}
			return $results;
		}

		protected static function _addAssetSourceDir( array| string $sources ) {
			if ( !is_array($sources) ) {
				$sources = [$sources];
			}
			foreach ( $sources as $index => $source ) {
				if ( is_dir($source) ) {
					array_push( self::$DEFAULT_ASSET_DIR, $source );
				}
			}
		}

	}

?>
