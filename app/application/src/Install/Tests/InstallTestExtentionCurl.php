<?php

	namespace Install\Tests;

	class InstallTestExtentionCurl extends AbstractInstallTest {

		public function __construct() {
			parent::__construct(
				'Extention: curl',
				'Curl extention is required for communication with external services.',
			);
			$this->setOrder(2);
		}

		public function test() {
			$result = (!(phpversion('curl') === false));
			$this->setResult($result);
			$this->throwFeedbackOnFail('Extention is either not installed or activated. Install and activate to continue.');

		}

	}

?>
