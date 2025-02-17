<?php

	include_once implode(DIRECTORY_SEPARATOR, ['..','application','src','includes.php']);

	$resourceName = $_GET['file'] ?? '';
	$resourceType = 'css';

	$assetReader = new \Assets\AssetReader();
	$resource = $assetReader->getAsset(
			$resourceName,
			$resourceType
		);

	header('Content-type: text/css');
	echo $resource;
?>
