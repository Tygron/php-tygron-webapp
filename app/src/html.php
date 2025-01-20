<?php

	function get_html( string $fileName = null ) {
		global $HTML_DIR;

		if (file_exists(\Utils\Files::makePath([$HTML_DIR, $fileName]))) {
			return \Utils\Files::readFile([$HTML_DIR, $fileName]);
		} else {
 			return \Utils\Files::readFile([__DIR__,'html', $fileName]);
		}
	}

?>
