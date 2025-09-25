<?php

	$MIN_VERSION_PHP = '8.2';

	$RUNNING_FROM_CLI = (php_sapi_name() === 'cli');
	$RUNNING_FROM_CRON = ( !($RUNNING_FROM_CLI && isset($_SERVER['TERM'])) );

?>
