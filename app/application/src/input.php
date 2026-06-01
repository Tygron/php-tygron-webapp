<?php

	$_INPUTS = [];
	$_INPUTS_CLEANED = [];

	$HEADERS = null;

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

	if ( !function_exists('get_clean_header_input') ) {
		function get_clean_header_input( $key, string $whitelist = null ) {
			global $_HEADERS, $SAFE_CHARACTERS;
			$whitelist = $whitelist ?? $SAFE_CHARACTERS;

			$_HEADERS = $_HEADERS ?? array_change_key_case(getallheaders(), CASE_LOWER);
			$headerValue = $_HEADERS[ strtolower($key) ] ?? null;
			return $headerValue;
		}
	}

	foreach ( array_merge( [], $_COOKIE, $_POST, $_GET ) as $key => $value ) {
		$_INPUTS[$key] = $value;
		$_INPUTS_CLEANED[$key] = get_clean_user_input($key);
	}

	foreach ( array_merge( [], $_ENV ) as $key => $value ) {
		$_INPUTS[$key] = $value;
		$_INPUTS_CLEANED[$key] = get_clean_user_input($key);
	}
?>
