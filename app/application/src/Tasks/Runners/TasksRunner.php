<?php
	namespace Tasks\Runners;

	class TasksRunner {

		private $standOffTimeInSeconds = 0;
		private $logs = [];

		public function __construct( $parameters = null ) {

		}

		public function setStandOffTimeInSeconds( int $standOffTimeInSeconds) {
			$this->standOffTimeInSeconds = $standOffTimeInSeconds;
		}

		public function getLogs() {
			return $this->logs;
		}

		public function logMessage(string|array $message) {
			array_push($this->logs, $message);
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
				$this->logMessage('<hr>');
				$this->logMessage(get_text('Start processing file %s', [$taskFileName]));
				$operated = $this->runTask($taskFileName);
				if ($operated) {
					$this->logMessage(get_text('Processed file %s, performed operation',[$taskFileName]));
				} else {
					$this->logMessage(get_text('Processed file %s, no operation',[$taskFileName]));
				}
				if ( $operated ) {
					break;
				}
			}
			if ( !$operated) {
				$this->logMessage(get_text('No operation'));
			}
		}

		protected function runTask( $taskName ) {
			$operated = false;
			try {
				$taskRunner = new TaskRunner();
				$taskRunner->setTask($taskName);
				$taskRunner->setStandOffTimeInSeconds($this->standOffTimeInSeconds);
				$taskRunner->run();
				$operated = $taskRunner->getHasOperated();
			} catch( \Throwable $e ) {
				$this->logMessage(get_text('Encountered an error while running task: %s',[$e->getMessage()]));
				$this->logMessage(get_text('Location: %s, Line: %s',[$e->getFile(),$e->getLine()]));
				$this->logMessage(get_text('Stacktrace: %s',['<pre>'.$e->getTraceAsString().'</pre>']));
			}
			return $operated;
		}

	}

?>
