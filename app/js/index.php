<?php

	include_once implode(DIRECTORY_SEPARATOR, ['..','application','src','includes.php']);

	$resourceName = $_GET['file'] ?? '';
	$resourceType = 'js';

	$assetReader = new \Assets\AssetReader();
	$resource = $assetReader->getAsset(
			$resourceName,
			$resourceType
		);

	header('Content-type: application/javascript');
	echo $resource;
?>
