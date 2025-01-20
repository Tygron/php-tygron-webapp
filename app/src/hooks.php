<?php

	$authenticationToken = $_INPUTS_CLEANED['authenticationToken'] ?? null;

	if ( !empty($authenticationToken) ) {
		setcookie('authenticationToken', $authenticationToken);
	}

	if ( !empty($AUTHENTICATION_TOKEN) && $authenticationToken != $AUTHENTICATION_TOKEN ) {
		echo get_html('login.html');
		exit();
	}

?>
