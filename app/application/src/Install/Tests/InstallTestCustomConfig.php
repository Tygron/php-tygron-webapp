<?php

	namespace Install\Tests;

	class InstallTestCustomConfig extends AbstractInstallTest {

		public function __construct() {
			parent::__construct(
				'Custom config file set up',
				'A custom config file is required for deployment-specific configurations',
			);
			$this->setOrder(5);
		}

		public function test() {
			global $CONFIG_OVERRIDE_FILE;

			$this->setResult( file_exists($CONFIG_OVERRIDE_FILE) && is_readable($CONFIG_OVERRIDE_FILE) );

			$this->throwFeedbackOnFail('Custom config file not found or not readable. Find the "custom/config/sample-config.php" file and create a copy such that "custom/config/config.php" exists.' );
		}

	}

?>
