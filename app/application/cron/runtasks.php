<?php

	try {
		if ( !function_exists('get_clean_user_input') ) {
			throw new \Exception('');
		}

		//$runSpecificTask = get_clean_user_input('task');
		$runSpecificTask = get_clean_user_input('task', '[a-zA-Z0-9\/\-\_]');

		if ($runSpecificTask) {
			$taskRunner = new \Tasks\Runners\SyncModeTaskRunner();
			try {

				$taskRunner->setSkipIncompatibleSync(true);
				$taskRunner->setSyncMode(\Tasks\Task::SYNC_MODE_ASYNC, true);
				$taskRunner->setTaskFileName($runSpecificTask);
				$taskRunner->run();
				foreach ($taskRunner->getLogs() as $key => $value) {
					log_message($value);
				}

			} catch ( \Throwable $e ) {
				foreach ($taskRunner->getLogs() as $key => $value) {
					log_message($value);
				}
                                log_message(get_text('Encountered an error while running single task: %s',[$e->getMessage()]));
                                log_message(get_text('Location: %s, Line: %s',[$e->getFile(),$e->getLine()]));
                                log_message(get_text('Stacktrace: %s',['<pre>'.$e->getTraceAsString().'</pre>']));
			}
		} else {
			$tasksRunner = new \Tasks\Runners\TasksRunner();
			try {
				$tasksRunner->setStandOffTimeInSeconds( $TASKS_STANDOFF_IN_SECONDS );
				$tasksRunner->run();
				foreach ($tasksRunner->getLogs() as $key => $value) {
					log_message($value);
				}
			} catch ( \Throwable $e ) {
				foreach ($tasksRunner->getLogs() as $key => $value) {
					log_message($value);
				}
				log_message(get_text('Encountered an error while running all tasks: %s',[$e->getMessage()]));
				log_message(get_text('Location: %s, Line: %s',[$e->getFile(),$e->getLine()]));
				log_message(get_text('Stacktrace: %s',['<pre>'.$e->getTraceAsString().'</pre>']));
			}
		}

	} catch (\Throwable $e) {
		echo $e->getMessage();
	}

?>
