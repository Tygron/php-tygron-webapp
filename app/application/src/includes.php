<?php

	include_once __DIR__.DIRECTORY_SEPARATOR.'AutoLoader/SimpleAutoLoader.php';
	SimpleAutoloaderAddSourceDirectory( __DIR__ );

	include_once 'config.php';
	include_once 'input.php';
	include_once 'texts.php';
	include_once 'html.php';
	include_once 'logging.php';

	\Rendering\Renderer::addAssetSource( $CUSTOM_HTML_DIR );
	\Rendering\Renderer::addAssetSource( $APP_HTML_DIR );
?>
