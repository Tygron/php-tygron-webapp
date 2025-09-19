<?php

	try {
		if ( !function_exists('get_clean_user_input') ) {
			throw new \Exception('');
		}

		$runSpecificTask = get_clean_user_input('task');

		if ($runSpecificTask) {
			try {
				$taskRunner = new \Tasks\Runners\TaskRunner();
				$taskRunner->setTask($runSpecificTask);
				$taskRunner->run();
			} catch ( \Throwable $e ) {
                                log_message(get_text('Encountered an error while running single task: %s',[$e->getMessage()]));
                                log_message(get_text('Location: %s, Line: %s',[$e->getFile(),$e->getLine()]));
                                log_message(get_text('Stacktrace: %s',['<pre>'.$e->getTraceAsString().'</pre>']));
			}
		} else {
			try {
				$tasksRunner = new \Tasks\Runners\TasksRunner();
				$tasksRunner->run();
			} catch ( \Throwable $e ) {
				log_message(get_text('Encountered an error while running all tasks: %s',[$e->getMessage()]));
				log_message(get_text('Location: %s, Line: %s',[$e->getFile(),$e->getLine()]));
				log_message(get_text('Stacktrace: %s',['<pre>'.$e->getTraceAsString().'</pre>']));
			}
		}

	} catch (\Throwable $e) {
		echo $e->getMessage();
	}

?>
