<?php

	namespace Install\Tests;

	class InstallTestExtentionFileInfo extends AbstractInstallTest {

		public function __construct() {
			parent::__construct(
				'Extention: fileinfo',
				'Fileinfo extention is required for mimetype interpretation of assets.',
			);
			$this->setOrder(1);
		}

		public function test() {
			$result = (!(phpversion('fileinfo') === false));
			$this->setResult($result);
			$this->throwFeedbackOnFail('Extention is either not installed or activated. Install and activate to continue.');

		}

	}

?>
