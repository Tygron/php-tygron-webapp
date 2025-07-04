<?php

	include_once __DIR__.DIRECTORY_SEPARATOR.'AutoLoader/SimpleAutoLoader.php';
	SimpleAutoloaderAddSourceDirectory( __DIR__ );

	include_once 'config.php';
	include_once 'input.php';
	include_once 'texts.php';
	include_once 'html.php';
	include_once 'logging.php';

	SimpleAutoloaderAddSourceDirectory( $CUSTOM_SRC_DIR, true );

	\Assets\AssetReader::addSource($CUSTOM_ASSETS_DIR);
	\Assets\AssetReader::addSource($APP_ASSETS_DIR);

	\Assets\AssetReader::addSource($CUSTOM_RESOURCES_DIR, false);
	\Assets\AssetReader::addSource($APP_RESOURCES_DIR, false);
?>
