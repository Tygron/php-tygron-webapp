<?php

	include_once implode(DIRECTORY_SEPARATOR, ['..','application','src','includes.php']);

	$assetName = $_GET['file'] ?? '';
	$resourceType = null;

	if ( str_contains($assetName, DIRECTORY_SEPARATOR) ) {
		$assetName = explode(DIRECTORY_SEPARATOR, $assetName);
		$assetType = array_shift($assetName);
		$assetName = implode(DIRECTORY_SEPARATOR, $assetName);
	}

	$asset = null;
	try {
		$asset = \Assets\AssetReader::getPublicAsset(
				$assetName,
				$assetType
			);
	} catch (\Exception $e) {
		exit('Asset not found');
	}
	$renderableAsset = new \Rendering\RenderableAsset();
	$renderableAsset->setAsset($asset);
	$renderableAsset->output();
?>
