<?php

	namespace Install\Tests;

	class InstallTestWorkspaceSubdirsValid extends AbstractInstallTest {

		public function __construct() {
			parent::__construct(
				'Workspace subdirectories writable',
				'The directories in the workspace must be writable',
			);
			$this->setOrder(6);
		}

		public function test() {
			global $WORKSPACE_DIR;

			$subdirs = scandir($WORKSPACE_DIR);
			$unwritable = [];
			foreach ($subdirs as $index=>$dirName) {
				$dir = $WORKSPACE_DIR.DIRECTORY_SEPARATOR.$dirName;
				if ( !in_array($dirName,['.','..']) && is_dir($dir) && !is_writable($dir) ) {
					$unwritable[] = $dirName;
				}
			}

			$this->setResult(count($unwritable)===0);

			$this->throwFeedbackOnFail('The following workspace subdirectories are unwritable: '.implode(', ',$unwritable));
		}

	}

?>
