<?php

	$authenticationToken = $_INPUTS_CLEANED['authenticationToken'] ?? null;

	if ( !empty($authenticationToken) ) {
		setcookie('authenticationToken', $authenticationToken);
	}

	if ( !empty($AUTHENTICATION_TOKEN) && $authenticationToken != $AUTHENTICATION_TOKEN ) {
		echo \Utils\Files::readFile([__DIR__,'..','login.html']);
		exit();
	}

?>
