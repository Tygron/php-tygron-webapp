<?php

	$_TEXTS = [];

	function get_text(string $text = null, array $params = [], string $lang = null) {
		global $LANGUAGE_DEFAULT;

		$lang = $lang ?? $LANGUAGE_DEFAULT;
		$text = $text.' ('.$lang.')';
		$text = vsprintf($text, $params);
		return $text;
	}

?>
