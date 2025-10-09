<?php

	namespace Install\Tests;

	class InstallTestWorkspaceValid extends AbstractInstallTest {

		public function __construct() {
			parent::__construct(
				'Workspace directory is set up',
				'A workspace directory is defined and is writable',
			);
			$this->setOrder(5);
		}

		public function test() {
			global $WORKSPACE_DIR;
			$this->setResult( is_dir($WORKSPACE_DIR) && is_writable($WORKSPACE_DIR) );
			$this->throwFeedbackOnFail('Workspace directory (defined in $WORKSPACE_DIR) either does not exist or is not writable. Define it in the custom config file.');
		}

	}

?>
