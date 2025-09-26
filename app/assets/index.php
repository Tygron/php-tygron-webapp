<?php

	include_once implode(DIRECTORY_SEPARATOR, ['..','application','src','includes.php']);

	$assetName = $_GET['file'] ?? '';
	$assetType = null;

	if ( str_contains($assetName, '/') ) {
		$assetName = explode('/', $assetName);
		$assetType = array_shift($assetName);
		$assetName = implode(DIRECTORY_SEPARATOR, $assetName);
	}

	$asset = null;
	try {
		$asset = \Assets\AssetReader::getPublicAsset(
				$assetName,
				$assetType ?? ''
			);
	} catch (\Exception $e) {
		http_response_code(404);
		exit('Asset not found');
	}
	$renderableAsset = new \Rendering\RenderableAsset();
	$renderableAsset->setAsset($asset);
	$renderableAsset->output();
?>
