<?php

	namespace Tasks\Operations;

	class AbstractOperation {

		private ?\Tasks\Task $task = null;

		public function __construct( \Tasks\Task $task = null ) {
			if ( !is_null($task) ) {
				$this->setTask($task);
			}
		}

		protected function run( \Tasks\Task $task ) {

		}

		protected function checkComplete( \Tasks\Task $task ) {

		}

		protected function checkReady( \Tasks\Task $task ) {

		}

		public function getInputParameters( ) {
			return [];
		}


		public function setTask( \Tasks\Task $task ) {
			$this->task = $task;
		}
		public function getTask() {
			if ( is_null($this->task) ) {
				throw new \Exception(get_text('Operation %s not initialized with task, cannot run.',[$this->name()]));
			}
			return $this->task;
		}

                public function name() {
                        return substr(strrchr(get_class($this),'\\'),1);
                }

		public function startOperation() {
			return $this->run( $this->getTask() );
		}
		public function checkReadyForOperation() {
			return $this->checkReady( $this->getTask() );
		}
		public function checkOperationComplete() {
			return $this->checkComplete( $this->getTask() );
		}

	}


?>
