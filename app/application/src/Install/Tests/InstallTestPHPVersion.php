<?php

	namespace Install\Tests;

	class InstallTestPHPversion extends AbstractInstallTest {

		public function __construct() {
			global $MIN_VERSION_PHP;
			parent::__construct(
				'PHP Version',
				'Minimum PHP version required is '.$MIN_VERSION_PHP.'.',
			);
			$this->setOrder(0);
		}

		public function test() {
			global $MIN_VERSION_PHP;
			$result = !version_compare(phpversion(), $MIN_VERSION_PHP, 'lt');
			$this->setResult($result);
			$this->throwFeedbackOnFail('PHP version is '.phpversion().', but at least 8.2 is required. Please update to continue.');

		}

	}

?>
