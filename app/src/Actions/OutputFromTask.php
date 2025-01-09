<?php

	namespace Actions;

	class OutputFromTask extends AbstractAction {

		public function run( array $parameters = null ) {
			global $_INPUTS;

			$task = \Tasks\Task::load(get_clean_user_input('task'));

			if (!$task->getCompleted()) {
				echo '<html><head><meta http-equiv="refresh" content="10"></head><body>';
				echo get_text('Task currently waiting for: %s',[$task->getCurrentOperation()]);
				echo '</body></html>';
			} else {
				echo '<html><head><style>body{height:100%;width:100%;padding:0px;margin:0px;} iframe {position:absolute;width:100%;height:100%;}</style></head><body>';
				echo get_text('<iframe src="%s">',[$task->getOutput('webViewer3DHtml')]);
				echo '</body></html>';
			}
		}
	}

?>
