<?php

	namespace Assets;

	class AssetReader {

		private static string|array $DEFAULT_PUBLIC_ASSET_DIR = [];
		private static string|array $DEFAULT_PRIVATE_ASSET_DIR = [];

		public static function addSource( array|string $sources, bool $publicSource = true ) {
			self::_addAssetSourceDir($sources, $publicSource);
		}

		public static function getAsset( string $assetName, string $assetType = null ) {
			return self::getPrivateAsset( $assetName, $assetType );
		}

		public static function getPublicAsset( string $assetName, string $assetType = null ) {
			$sources = self::getAssetSources(
					$assetType,
					self::$DEFAULT_PUBLIC_ASSET_DIR
				);
			return self::_getAsset( $assetName, $assetType, $sources );
		}

		public static function getPrivateAsset( string $assetName, string $assetType = null) {
			$sources = self::getAssetSources(
					$assetType,
					array_merge( self::$DEFAULT_PUBLIC_ASSET_DIR, self::$DEFAULT_PRIVATE_ASSET_DIR )
				);
			return self::_getAsset( $assetName, $assetType, $sources );
		}

		public static function getAssetSources( string $assetType = null, string|array $assetDir = null) {
			if ( is_null($assetDir) ) {
				$assetDir = self::$DEFAULT_PUBLIC_ASSET_DIR;
			}

			return self::_getAssetSources( $assetType, $assetDir);
		}

		protected static function _getAsset( string $assetName, string $assetType, array $assetSources ) {
			foreach ( $assetSources as $index => $source ) {
				$fileContent = null;
				$fileFound = null;
				try {
					$fileContent = \Utils\Files::readFile([$source, $assetName]);
					$fileMimeType = \Utils\Files::readFileMimeType([$source, $assetName]);

				} catch ( \Throwable $e ) {
					continue;
				}

				$asset = new \Assets\Asset(
						$fileContent,
						$fileMimeType,

						$assetName,
						$source,

						$assetType,
					);
				return $asset;
			}
			throw new \Exception(get_text('Asset not found in %s location(s): %s', [count($assetSources), $assetName]));

		}

		protected static function _getAssetSources( string $assetType = null, string|array $assetDir ) {
			$results = [];
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

		protected static function _addAssetSourceDir( array| string $sources, bool $publicSource = true ) {
			if ( !is_array($sources) ) {
				$sources = [$sources];
			}
			foreach ( $sources as $index => $source ) {
				if ( is_dir($source) ) {
					if ( $publicSource ) {
						array_push( self::$DEFAULT_PUBLIC_ASSET_DIR, $source );
					} else {
						array_push( self::$DEFAULT_PRIVATE_ASSET_DIR, $source );
					}
				}
			}
		}

	}

?>
