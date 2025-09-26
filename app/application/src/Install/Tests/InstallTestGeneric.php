<?php

	namespace Install\Tests;

	class InstallTest extends AbstractInstallTest {

		public function __construct() {
			global $MIN_VERSION_PHP;
			parent::__construct(
				'',
				'',
			);
			$this->setOrder(0);
		}

		public function test() {
			global $MIN_VERSION_PHP;
			$result = false;
			$this->setResult($result);
			$this->throwFeedbackOnFail();

		}

	}

?>
