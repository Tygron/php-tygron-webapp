<?php

	include_once "src/includes.php";

	foreach ( $CRON_TASKS as $index => $jobName) {
		$filePath = $CUSTOM_CRON_DIR.DIRECTORY_SEPARATOR.$jobName;
		if ( !file_exists($filePath) ) {
			$filePath = $APP_CRON_DIR.DIRECTORY_SEPARATOR.$jobName;
		}
		if ( !file_exists($filePath) ) {
			echo 'not found: '.$filePath;
		}

		echo get_text( 'Running crontask %s',[$jobName] );
		echo '<hr>';
		include_once($filePath);
	}

?>
