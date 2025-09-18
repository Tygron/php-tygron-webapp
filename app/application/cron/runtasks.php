<?php

	(function() {
		global $AUTORUN_TOKEN;
		$runAllTasks = false;
		$runSpecificTask = get_clean_user_input('task');

		if ( is_null($AUTORUN_TOKEN)) {
		} else if ( !empty($AUTORUN_TOKEN) && ($AUTORUN_TOKEN !== get_clean_user_input('autorun_token')) ) {
		} else {
			$runAllTasks = true;
		}

		if ($runSpecificTask) {
			try {
				$taskRunner = new \Tasks\Runners\TaskRunner();
				$taskRunner->setTask($runSpecificTask);
				$taskRunner->run();
			} catch ( \Throwable $e ) {
                                log_message(get_text('Encountered an error while running task: %s',[$e->getMessage()]));
                                log_message(get_text('Location: %s, Line: %s',[$e->getFile(),$e->getLine()]));
                                log_message(get_text('Stacktrace: %s',['<pre>'.$e->getTraceAsString().'</pre>']));
			}
		} else if ($runAllTasks) {
			$tasksRunner = new \Tasks\Runners\TasksRunner();
			$tasksRunner->run();
		}

	})();

?>
