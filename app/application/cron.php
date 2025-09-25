<?php

	include_once "src/includes.php";

	if ($RUNNING_FROM_CLI || get_clean_user_input('cli_token') === $CLI_TOKEN) {

		if ( !empty($CRON_LAST_RUN_FILE) ) {
			\Utils\Files::writeFile(
					[$WORKSPACE_DIR, $CRON_LAST_RUN_FILE],
					\Utils\Time::GetCurrentTimestamp()
				);
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

	}

?>
