<?php
	namespace Tasks\Runners;

	include_once "src/includes.php";

	class TasksRunner {

		public function __construct( $parameters = null ) {

		}

		public function run() {
			global $WORKSPACE_TASK_DIR;
			$taskFiles = $this->getTaskFilesFromDir($WORKSPACE_TASK_DIR);

			$this->runTasks( $taskFiles );
		}

		protected function getTaskFilesFromDir( $tasksDir ) {
			$fileNamesFull = glob($tasksDir . DIRECTORY_SEPARATOR .'*'.\Tasks\Task::$TASKFILE_POSTFIX);
			$fileNames = str_replace($tasksDir . DIRECTORY_SEPARATOR, '', $fileNamesFull);
			return $fileNames;
		}

		protected function runTasks( $taskFileNames ) {
			$operated = false;
			log_message(get_text('Output:'));
			foreach ( $taskFileNames as $key => $taskFileName) {
				log_message('<hr>');
				log_message(get_text('Start processing file %s', [$taskFileName]));
				$operated = $this->runTask($taskFileName);
				if ($operated) {
					log_message(get_text('Processed file %s, performed operation',[$taskFileName]));
				} else {
					log_message(get_text('Processed file %s, no operation',[$taskFileName]));
				}
				if ( $operated ) {
					break;
				}
			}
			if ( !$operated) {
				log_message(get_text('No operation'));
			}
		}

		protected function runTask( $taskName ) {
			$operated = false;
			try {
				$taskRunner = new TaskRunner();
				$taskRunner->setTask($taskName);
				$taskRunner->run();
				$operated = $taskRunner->getHasOperated();
			} catch( \Throwable $e ) {
				log_message(get_text('Encountered an error while running task: %s',[$e->getMessage()]));
				log_message(get_text('Location: %s, Line: %s',[$e->getFile(),$e->getLine()]));
				log_message(get_text('Stacktrace: %s',['<pre>'.$e->getTraceAsString().'</pre>']));
			}
			return $operated;
		}

	}

?>
