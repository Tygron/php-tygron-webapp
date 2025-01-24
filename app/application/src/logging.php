<?php

	function log_message( string $message = null ) {
		if ( is_null($message) ) {
			return;
		}
		echo $message.'<br>'.PHP_EOL.PHP_EOL;
	}

?>
