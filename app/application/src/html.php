<?php

	function get_html( string $fileName = null ) {
		global $APP_HTML_DIR, $CUSTOM_HTML_DIR;

		if (file_exists(\Utils\Files::makePath([$CUSTOM_HTML_DIR, $fileName]))) {
			return \Utils\Files::readFile([$CUSTOM_HTML_DIR, $fileName]);
		} else if (file_exists(\Utils\Files::makePath([$APP_HTML_DIR, $fileName]))) {
 			return \Utils\Files::readFile([$APP_HTML_DIR, $fileName]);
		} else {
			throw new \Exception(get_text('Could not find html file: ',[$fileName]));
		}
	}

?>
