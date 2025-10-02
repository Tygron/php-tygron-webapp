<?php

	include_once implode(DIRECTORY_SEPARATOR,[__DIR__,'..','src','includes.php']);


	if ( $RUNNING_FROM_CLI ) {
	} else if ( is_null($CLI_TOKEN) ) {
	} else if (  (!empty($CLI_TOKEN)) && (get_clean_user_input('cli_token') === $CLI_TOKEN) ) {
	} else {
		return;
	}

	if ( !empty($CRON_LAST_RUN_FILE) ) {
		try {
			\Utils\Files::writeFile(
				[$WORKSPACE_DIR, $CRON_LAST_RUN_FILE],
				\Utils\Time::GetCurrentTimestamp()
			);
			$registeredTime = \Utils\Files::readFile( [$WORKSPACE_DIR, $CRON_LAST_RUN_FILE] );
			echo get_text ( 'Registered new cron run time: %s', [$registeredTime] );
		} catch ( \Throwable $e ) {
			echo get_text( 'Could not register latest cron run time. Reason: %s', [$e->getMessage()] );
		}
		echo '<hr>';
	}

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
