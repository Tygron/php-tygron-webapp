<?php

	namespace Install\Tests;

	abstract class AbstractInstallTest {

		private $order = 9999;

		private $name = '';
		private $description = '';
		private $success = null;
		private $result = '';
		private $feedback = '';

		public function __construct($name, $description) {
			$this->setName($name);
			$this->setDescription($description);
		}

		public function setOrder($order) {
			$this->order = $order;
		}
		public function setName($name) {
			$this->name = $name;
		}
		public function setDescription($description) {
			$this->description = $description;
		}
		public function setResult($result) {
			if ($result === true) {
				$this->result = 'Success';
				$this->setSuccess(true);
			} else if ($result === false) {
				$this->result = 'Fail';
				$this->setSuccess(false);
			} else {
				$this->result = $result;
				$this->setSuccess($result);
			}
			$this->setSuccess($result);
		}
		public function setSuccess($success) {
			$this->success = $success;
		}
		public function setFeedback($feedback) {
			$this->feedback = $feedback;
		}
		public function throwFeedbackOnFail($feedback) {
			if ($this->getSuccess() === false) {
				throw new \Exception($feedback);
			}
		}
		public function throwFeedbackOnNotSuccess($feedback) {
			if ( !($this->getSuccess() === true) ) {
				throw new \Exception($feedback);
			}
		}



		public function getOrder() {
			return $this->order;
		}
		public function getName() {
			return $this->name;
		}
		public function getDescription() {
			return $this->description;
		}
		public function getResult() {
			return $this->result;
		}
		public function getSuccess() {
			return $this->success;
		}
		public function getFeedback() {
			return $this->feedback;
		}
		public function getOutputAsArray() {
			return [
					'title' => $this->getName(),
					'description' => $this->getDescription(),
					'result' => $this->getResult(),
					'message' => $this->getFeedback(),
				];
		}



		public static function runStatic() {
			$test = new static();
			$test->run();
			return $test;
		}
		public function run() {
			try {
				$this->test();
			} catch ( \Throwable $e ) {
				$this->setFeedback( $e->getMessage() );
			}
			return $this;
		}



		public abstract function test();


	}
?>
