<?php

	$_INPUTS = [];
	$_INPUTS_CLEANED = [];

	if ( !function_exists('get_clean_user_input') ) {
		function get_clean_user_input( $key, string $whitelist = null ) {
			global $_INPUTS, $SAFE_CHARACTERS;
			$whitelist = $whitelist ?? $SAFE_CHARACTERS;

			$value = $_INPUTS[$key] ?? null;
			if ( !(is_string($value) || is_array($value)) ) {
				return null;
			}
			return clean_user_input($value, $whitelist);
		}
	}

	if ( !function_exists('clean_user_input') ) {
		function clean_user_input( $value, string $whitelist = null ) {
			global $SAFE_CHARACTERS;
			$whitelist = $whitelist ?? $SAFE_CHARACTERS;

			$matches = [];
			preg_match_all('('.$whitelist.')', $value, $matches);
			if ( count($matches)>0 ) {
				return implode('',$matches[0]);
			}
			return null;
		}
	}

	foreach ( array_merge( [], $_REQUEST ) as $key => $value ) {
		$_INPUTS[$key] = $value;
		$_INPUTS_CLEANED = get_clean_user_input($key);
	}

	foreach ( array_merge( [], $_ENV ) as $key => $value ) {
		$_INPUTS[$key] = $value;
		$_INPUTS_CLEANED = get_clean_user_input($key);
	}
?>
