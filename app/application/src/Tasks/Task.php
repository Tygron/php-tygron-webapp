<?php

	namespace Tasks;

	class Task {

		public static string $TASKFILE_POSTFIX = '-task.json';
		public static string $CREDENTIALSFILE_POSTFIX = '-credentials.json';

		private array $data = [];

		public static array $DEFAULT_DATA = [
				'taskName'		=>	'',
				'credentialsFile'	=>	'',
				'templateName'		=>	'',
				'platform'		=>	'engine',

				'apiToken'		=>	'',

				'location'		=>	null,
				'size'			=>	[500,500],

				'creationTime'		=>	null,
				'lastOperationTime'	=>	null,
				'completionTime'	=>	null,

				'taskOperations'	=>	[],
				'cleanupOperations'	=>	[],
				'currentOperationIndex'	=>	0,
				'currentOperation'	=>	'',
				'startedOperation'	=>	'',
				'operationFeedback'	=>	null,
				'operationResult'	=>	null,

				'taskCompleted'		=>	false,
				'completed'		=>	false,

				'initialized'		=>	false,
				'output'		=>	[],
				'error'			=>	null,
				'log'			=>	[],
			];

		public static ?array $ALLOWED_PARAMETERS = null;


		public function __construct( $parameters ) {
			$this->data = self::$DEFAULT_DATA;
			$this->setData($parameters);
			$this->initialize();
		}

		public function setData( array $parameters ) {
			foreach( $parameters as $key => $value ) {
				switch ($key) {
					case 'taskName':
						$this->setTaskName($value);
						break;
					case 'credentialsFile':
						$this->setCredentialsFile($value);
						break;
					case 'templateName':
						$this->setTemplateName($value);
						break;
					case 'platform':
						$this->setPlatform($value);
						break;
					case 'location':
						$this->setLocation($value);
						break;
					case 'size':
						$this->setSize($value);
						break;

					case 'apiToken':
						$this->setApiToken($value);
						break;

					case 'taskOperations':
						$this->setTaskOperations($value);
						break;
					case 'cleanupOperations':
						$this->setCleanupOperations($value);
						break;

					case 'currentOperationIndex':
						$this->setCurrentOperationIndex($value);
						break;
					case 'currentOperation':
						break;
					case 'startedOperation':
						$this->setStartedOperation($value);
						break;
					case 'operationFeedback':
						$this->setOperationFeedback($value);
						break;
					case 'operationResult':
						$this->setOperationResult($value);
						break;

					case 'completed':
						$this->setCompleted($value);
						break;

					case 'output':
						$this->setOutput($value);
						break;
					case 'error':
						$this->setError($value);
						break;

					case 'log':
						$this->setLog($value);
						break;

					default:
						$this->data[$key] = $value;
						break;
				}
			}
		}

		public function setTaskName( string $taskName ) {
			$this->data['taskName'] = $taskName;
		}
		public function setCredentialsFile( string $credentialsFile ) {
			$this->data['credentialsFile'] = $credentialsFile;
		}
		public function setCredentials( array|null $credentials ) {
			$this->data['credentials'] = $credentials;
		}
		public function setTemplateName( string $templateName ) {
			$this->data['templateName'] = $templateName;
		}
		public function setPlatform( string $platform ) {
			$this->data['platform'] = $platform;
		}
		public function setLocation( array $location ) {
			$this->data['location'] = $location;
		}
		public function setSize( array $size ) {
			$this->data['size'] = $size;
		}
		public function setApiToken( string $token ) {
			$this->data['apiToken'] = $token;
		}

		public function setCreationTime( int $creationTime) {
			$this->data['creationTime'] = $creationTime;
		}
		public function setLastOperationTime( int $operationTime ) {
			$this->data['lastOperationTime'] = $operationTime;
		}
		public function setCompletionTime( int $completionTime) {
			$this->data['completionTime'] = $completionTime;
		}

		public function setTaskOperations( array $operations ) {
			$this->data['taskOperations'] = $operations;
		}
		public function setCleanupOperations( array $operations ) {
			$this->data['cleanupOperations'] = $operations;
		}
		public function setToNextOperation() {
			$operationIndex = $this->getCurrentOperationIndex();
			$operationIndex+=1;
			if ( $operationIndex >= count($this->getTaskOperations()) ) {
				$this->setTaskCompleted(true);
			}
			if ( $operationIndex >= count($this->getOperations()) ) {
				$this->setCompleted(true);
				return;
			}
			$this->setCurrentOperationIndex($operationIndex);
		}
		public function retryCurrentOperation(string $cause = 'retry') {
			$this->setStartedOperation( $this->getCurrentOperation() . '(retry:  ' . $cause.')');
		}
		public function startCleanup() {
			if ( count($this->getCleanupOperations()) === 0 ) {
				throw new \Exception('No cleanup operations defined');
			}
			$taskOperationsCount = count($this->getTaskOperations());
			if ( $this->getCurrentOperationIndex() < $taskOperationsCount ) {
				$this->setCurrentOperationIndex($taskOperationsCount);
			}
		}
		public function setCurrentOperationIndex( int $operationIndex ) {
			$operation = $this->getOperationByIndex( $operationIndex );
			if ( is_null($operation) ) {
				throw new \Exception('Operation index exceeds list of operations');
			}
			$this->data['currentOperationIndex'] = $operationIndex;
			$this->data['currentOperation'] = $operation;
			$this->setOperationFeedback(null);
			$this->setOperationResult(null);
		}
		public function setCurrentOperation( string $operation ) {
			throw new \Exception('Cannot set current operation by name');
		}
		public function setStartedOperation( string $operation ) {
			$this->data['startedOperation'] = $operation;
		}
		public function setOperationFeedback( $feedback ) {
			$this->data['operationFeedback'] = $feedback;
		}
		public function setOperationResult( $result ) {
			$this->data['operationResult'] = $result;
		}

		public function setTaskCompleted( string|bool $completed ) {
			if ( is_string($completed) ) {
				$completed = $completed==='false' ? false : $completed=='true';
			}
			$this->data['taskCompleted'] = $completed;
			if ($completed) {
				$this->setCompletionTime( \Utils\Time::getCurrentTimestamp() );
			}
		}
		public function setCompleted( string|bool $completed ) {
			if ( is_string($completed) ) {
				$completed = $completed==='false' ? false : $completed=='true';
			}
			$this->data['completed'] = $completed;
		}
		public function setOutput( string|array $key, $value = null ) {
			if ( is_array($key) ) {
				$this->data['output'] = array_merge($this->data['output'], $key);
			} else if ( is_string($key) ) {
				$this->data['output'][$key] = $value;
			}
		}
		public function setError( \Throwable|array|string|null $e ) {
			if ( is_null($e) ) {
				$this->data['error'] = null;
			}else if ( is_array($e) ) {
				$this->data['error'] = $e;
			} else if ( is_string($e) ) {
				$this->data['error'] = [
					'message'=>$e,
					'file'=>'',
					'line'=>''
				];
			} else {
				$this->data['error'] = [
					'message'=>$e->getMessage(),
					'file'=>$e->getFile(),
					'line'=>$e->getLine()
				];
			}
		}
		public function setLog( string|array $value ) {
			if ( !is_array($value) ) {
				$value = [$value];
			}
			$this->data['log'] = $value;
		}

		public function initialize() {
			if ($this->data['initialized']) {
				return;
			}
			if ( empty($this->getCurrentOperation()) && count($this->getOperations())>0 ) {
				$this->setCurrentOperationIndex(0);
			}
			$this->data['initialized'] = true;
			$this->setCreationTime(\Utils\Time::getCurrentTimestamp());
		}




		public function log( string|array $value ) {
			if (!is_array($value) ) {
				$value = [$value];
			}
			$this->data['log'] = array_merge($this->data['log'], $value);
		}



		public function getData(string $key = null, $defaultReturn = null) {
			if ( is_null($key) ) {
				return $this->data;
			}
			if ( array_key_exists($key,$this->data) ) {
				return $this->data[$key];
			}
			return $defaultReturn;
		}

		public function getTaskName() {
			return $this->data['taskName'];
		}
		public function getTaskFileName() {
			return self::generateTaskFileName($this->getTaskName());
		}
		public function getPlatform() {
			return $this->data['platform'];
		}
		public function getCredentialsFileName() {
			return $this->data['credentialsFile'];
		}
		public function getCredentials() {
			$taskDataCredentials = $this->data['credentials'] ?? null;
			if (!empty($taskDataCredentials)) {
				return $taskDataCredentials;
			}
			return \Tasks\TaskCredentials::loadCredentialsFile($this->getCredentialsFileName());
		}
		public function getTemplateName() {
			return $this->data['templateName'];
		}
		public function getLocation() {
			return $this->data['location'];
		}
		public function getSize() {
			return $this->data['size'];
		}

		public function getApiToken() {
			return $this->data['apiToken'];
		}

		public function getCreationTime() {
			return $this->data['creationTime'];
		}
		public function getLastOperationTime() {
			return $this->data['creationTime'];
		}
		public function getCompletionTime() {
			return $this->data['creationTime'];
		}
		public function getOperations() {
			return array_merge($this->getTaskOperations(), $this->getCleanupOperations());
		}
		public function getTaskOperations() {
			return $this->data['taskOperations'];
		}
		public function getCleanupOperations() {
			return $this->data['cleanupOperations'];
		}
		public function getOperationByIndex( int $index = 0 ) {
			return $this->getOperations()[$index] ?? null;
		}
		public function getFirstOperation() {
			return $this->getOperationByIndex(0);
		}
		public function getCurrentOperationIndex() {
			return $this->data['currentOperationIndex'];
		}
		public function getCurrentOperation() {
			if ( $this->getCompleted() ) {
				return null;
			}
			return $this->data['currentOperation'];
		}
		public function getStartedOperation() {
			return $this->data['startedOperation'];
		}
		public function getOperationFeedback() {
			return $this->data['operationFeedback'];
		}
		public function getOperationResult() {
			return $this->data['operationResult'];
		}

		public function getTaskCompleted() {
			return $this->data['taskCompleted'];
		}
		public function getCompleted() {
			return $this->data['completed'];
		}
		public function getOutput( string $key = null, $defaultValue = null ) {
			if ( $key === null ) {
				return $this->data['output'];
			}
			if ( !array_key_exists($key, $this->data['output']) ) {
				return $defaultValue;
			}
			return $this->data['output'][$key];
		}
		public function getError() {
			return $this->data['error'];
		}
		public function getLog() {
			return $this->data['log'];
		}

		public function validate() {
			if ( is_null($this->getCredentials()) ) {
				throw new \Exception('No credentials provided');
			}
			if ( empty($this->getTemplateName()) ) {
				throw new \Exception('No template provided');
			}
			if ( empty($this->getLocation()) ) {
				throw new \Exception('No location provided');
			}
		}


		public static function getAllowedParameters() {
			if ( is_null(self::$ALLOWED_PARAMETERS) ) {
				$operationClasses = \Utils\Classes::getClassesInFolder([__DIR__,'Operations']);
				$allowedParameters = [];
				foreach ( $operationClasses as $key=>$className) {
					try {
						$allowedParameters = array_merge($allowedParameters, (new $className())->getInputParameters());
					} catch( \Throwable $e ) {
					}
				}
				self::$ALLOWED_PARAMETERS = $allowedParameters;
			}
			return self::$ALLOWED_PARAMETERS;
		}



		private static function generateTaskFileName(string $taskName) {
			if (str_ends_with($taskName, self::$TASKFILE_POSTFIX)) {
				return $taskName;
			}
			return $taskName.self::$TASKFILE_POSTFIX;
		}


		public static function load( string $taskName ) {
			global $WORKSPACE_TASK_DIR;

			$fileName = self::generateTaskFileName($taskName);
			$taskData = \Utils\Files::readJsonFile([$WORKSPACE_TASK_DIR,$fileName]);
			$task = new Task($taskData);
			return $task;
		}
		public function save() {
			global $WORKSPACE_TASK_DIR;

			$this->saveCredentials();

			\Utils\Files::writeJsonFile([$WORKSPACE_TASK_DIR, $this->getTaskFileName()], $this->getData());
		}
		public function delete() {
			global $WORKSPACE_TASK_DIR;

			try {
				$this->deleteCredentials();
			} catch (\Throwable $e) {
				return false;
			}
			try {
				$file = $this->getTaskFileName();
				\Utils\Files::deleteFile($file, $WORKSPACE_TASK_DIR);
			} catch (\Throwable $e) {
				return false;
			}
			return true;
		}
		public static function list() {
			global $WORKSPACE_TASK_DIR;
			$tasksDir = $WORKSPACE_TASK_DIR;

			$fileNamesFull = glob($tasksDir . DIRECTORY_SEPARATOR .'*'.self::$TASKFILE_POSTFIX);
			$fileNames = str_replace($tasksDir . DIRECTORY_SEPARATOR, '', $fileNamesFull);
			$taskNames = str_replace(self::$TASKFILE_POSTFIX, '', $fileNames);

			return $taskNames;
		}

		public function saveCredentials() {
			if ( empty($this->getCredentialsFileName()) && (!empty($this->getCredentials())) ) {
				$credentials = $this->getCredentials();
				$credentialsFile = \Tasks\TaskCredentials::credentialsToFile(
						$this->getTaskName(),
						$this->getCredentials()['useDefaultCredentials']??false,
						$this->getCredentials()['platform']??null,
						$this->getCredentials()['username']??null,
						$this->getCredentials()['password']??null,
						$this->getCredentials()['mfa']??null,
					);

				$this->setCredentialsFile($credentialsFile);
				$this->setCredentials(null);
			}
		}

		public function deleteCredentials() {
			global $WORKSPACE_CREDENTIALS_DIR;


			if ( \Tasks\TaskCredentials::isDefaultCredentialsFile($this->getCredentialsFileName()) ) {
				$this->log('Will not delete default credentials file. Only remove reference.');
			} else {

				try {
					$fileName = $this->getCredentialsFileName();
					\Tasks\TaskCredentials::deleteCredentialsFile( $fileName );
				} catch ( \Throwable $e ) {
					$this->log('Could not delete '.$fileName);
					throw $e;
				}

			}
			$this->setCredentialsFile('');
		}


	}
?>
