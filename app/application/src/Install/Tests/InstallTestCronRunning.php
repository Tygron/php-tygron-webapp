<?php

	namespace Install\Tests;

	class InstallTestCronRunning extends AbstractInstallTest {

		public function __construct() {
			parent::__construct(
				'Cronjobs are running',
				'Cron, or alternative periodic task scheduler, should run approximately once per minute',
			);
			$this->setOrder(8);
		}

		public function test() {
			global $WORKSPACE_DIR, $CRON_LAST_RUN_FILE;

			if ( empty($CRON_LAST_RUN_FILE) ) {
				$this->setResult('Could not test');
				$this->throwFeedbackOnNotSuccess('No flag file is defined (in CRON_LAST_RUN_FILE) indicating last cron/scheduled run.');
			}

			$lastUpdate = @file_get_contents( $WORKSPACE_DIR.DIRECTORY_SEPARATOR.$CRON_LAST_RUN_FILE );
			$lastUpdateTimeDifference = \Utils\Time::getCurrentTimestamp() - $lastUpdate;
			$success=$lastUpdateTimeDifference < (60 * 1.5);

			$this->setResult($lastUpdateTimeDifference < 60 * 1.5);

			$message = $lastUpdate ? 'Last update was '.$lastUpdateTimeDifference. 'seconds ago. ' : 'Cron/scheduled has never run. ';
			$this->throwFeedbackOnFail($message.$this->getFixes());

		}

		public function getFixes() {
			global $APPLICATION_WEB_FULL_URL;
			$message = '';

			$message = 'Set up a cronjob or scheduled task to automatically run operations<br>';

			$instructions = [];
			$instructions['Cron via CLI'] = [
				'Based on cron/sample-cron.php, create a cron.php file.',
				'Optionally, based on cron/sample-cron.sh, create a cron.sh file',
				'Run "crontab -e"',
				'Add a command to run the .php (or .sh) file with the appropriate interpreter, changing the directory as appropriate.',
				'E.g. "* * * * * bash /var/cron/cron.sh"',
			];
			$instructions['Cron via Wget'] = [
				'Ensure a CLI_TOKEN is configured in custom/config/config.php',
				'Run "crontab -e"',
				'Add a command to make a web request to the cron endpoint, providing the CLI_TOKEN as query parameter.',
				'E.g. " * * * * * wget -O '.$APPLICATION_WEB_FULL_URL.'/application/cron.php?cli_token=[CLI_TOKEN HERE] >/dev/null 2>&1"'
			];

			foreach ( $instructions as $type => $instruction) {
				$message.= '<p>'.$type.'<ul><li>';
				$message.= implode('</li><li>',$instruction);
				$message.='</li></ul>';
			}

			return $message;


		}

	}

?>
