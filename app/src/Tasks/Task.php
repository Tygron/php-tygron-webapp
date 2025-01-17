<?php

	namespace Tasks;

	class Task {

		public static string $TASKFILE_POSTFIX = '-task.json';
		public static string $CREDENTIALSFILE_POSTFIX = '-credentials.json';

		private array $data = [];

		public static array $DEFAULT_DATA = [
				'taskName'=>'',
				'credentialsFile'=>'',
				'templateName'=>'',
				'platform'=>'engine',
				'location'=>[0,0],
				'size'=>[500,500],

				'apiToken'=>'',
				'taskOperations'=>[],
				'cleanupOperations'=>[],
				'currentOperation'=>'',
				'startedOperation'=>'',
				'taskCompleted'=>false,
				'completed'=>false,

				'output'=>[],
				'log'=>[],
			];


		public function __construct( $parameters ) {
			$this->data = self::$DEFAULT_DATA;
			$this->setData($parameters);
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

					case 'currentOperation':
						$this->setCurrentOperation($value);
						break;
					case 'startedOperation':
						$this->setStartedOperation($value);
						break;

					case 'completed':
						$this->setCompleted($value);
						break;

					case 'output':
						$this->setOutput($value);
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

		public function setTaskOperations( array $operations ) {
			$this->data['taskOperations'] = $operations;
		}
		public function setCleanupOperations( array $operations ) {
			$this->data['cleanupOperations'] = $operations;
		}
		public function setToNextOperation() {
			$operationIndex = array_search($this->getCurrentOperation(), $this->getOperations());
			if ( ++$operationIndex >= count($this->getOperations()) ) {
				$this->setCompleted(true);
				return;
			}
			$this->setCurrentOperation($this->getOperations()[$operationIndex]);
		}
		public function setCurrentOperation( string $operation ) {
			$this->data['currentOperation'] = $operation;
		}
		public function setStartedOperation( string $operation ) {
			$this->data['startedOperation'] = $operation;
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
		public function setLog( string|array $value ) {
			if ( !is_array($value) ) {
				$value = [$value];
			}
			$this->data['log'] = $value;
		}



		public function log( string|array $value ) {
			if (!is_array($value) ) {
				$value = [$value];
			}
			$this->data['log'] = array_merge($this->data['log'], $value);
		}



		public function getData() {
			return $this->data;
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

		public function getOperations() {
			return array_merge($this->getTaskOperations(), $this->getCleanupOperations());
		}
		public function getTaskOperations() {
			return $this->data['taskOperations'];
		}
		public function getcleanupOperations() {
			return $this->data['cleanupOperations'];
		}
		public function getFirstOperation() {
			return (count($this->getOperations())>0) ? $this->getOperations()[0] : null;
		}
		public function getCurrentOperation() {
			if ( $this->getCompleted() ) {
				return null;
			}
			$found = $this->data['currentOperation'];
			if ( empty($found) ) {
				$found = $this->getFirstOperation();
			}
			return $found;
		}
		public function getStartedOperation() {
			return $this->data['startedOperation'];
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
		public function getLog() {
			return $this->data['log'];
		}



		public function save() {
			global $WORKSPACE_TASK_DIR;

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

		public function deleteCredentials() {
			global $WORKSPACE_CREDENTIALS_DIR;

			if ( $this->getCredentialsFileName() == $this->getDefaultCredentialsFile() ) {
				$this->log('Will not delete default credentials file. Only remove reference.');
			} else {
				$file = $this->getCredentialsFileName();
				try {
					\Utils\Files::deleteFile($file, $WORKSPACE_CREDENTIALS_DIR);
				} catch (\Throwable $e) {
					$this->log('Could not delete '.$file.': '.$e->getMessage());
				}
			}
			$this->setCredentialsFile('');
		}

		public static function generateTask($parameters) {
			$task = new Task([]);
			$task->setTaskName( 		self::generateTaskNameFromParameters($parameters) );
			$task->setTemplateName( 	self::generateTemplateNameFromParameters($parameters) );
			$task->setPlatform(		$parameters['platform'] ?? 'engine');
			$task->setLocation(		self::generateLocationFromParameters($parameters) );
			$task->setSize( 		self::generateSizeFromParameters($parameters) );

			$task->setTaskOperations(	self::generateTaskOperationsFromParameters($parameters) );
			$task->setCleanupOperations(	self::generateCleanupOperationsFromParameters($parameters) );

			$task->setCredentialsFile(	self::generateCredentialsFileFromParameters($task->getTaskName(), $parameters) );

			$task->save();
			return $task;
		}


		public static function load( string $taskName ) {
			global $WORKSPACE_TASK_DIR;

			$fileName = self::generateTaskFileName($taskName);
			$taskData = \Utils\Files::readJsonFile([$WORKSPACE_TASK_DIR,$fileName]);
			$task = new Task($taskData);
			return $task;
		}

		public static function list() {
			global $WORKSPACE_TASK_DIR;
			$tasksDir = $WORKSPACE_TASK_DIR;

			$fileNamesFull = glob($tasksDir . DIRECTORY_SEPARATOR .'*'.self::$TASKFILE_POSTFIX);
			$fileNames = str_replace($tasksDir . DIRECTORY_SEPARATOR, '', $fileNamesFull);
			$taskNames = str_replace(self::$TASKFILE_POSTFIX, '', $fileNames);

			return $taskNames;
		}



		public static function cleanParameters( array $parameters ) {
			$cleanedParameters = [];
			foreach( $parameters as $key=> $value ) {
				switch($key) {
					case 'username':
						$cleanedParameters[$key] = clean_user_input($value, '[\.\-_@+a-zA-Z0-9]');
						break;
					case 'locationX':
					case 'locationY':
						$cleanedParameters[$key] = (float) clean_user_input($value, '[0-9\-\.]');
						break;
					case 'sizeX':
					case 'sizeY':
						$cleanedParameters[$key] = (int) clean_user_input($value, '[0-9]');
						break;
					case 'operations':
					case 'taskOperations':
					case 'cleanupOperations':
						$cleanedParameters[$key] = get_clean_user_input($key, '[\[\]\,\.\-"_a-zA-Z0-9]');
						break;
					default:
						$cleanedParameters[$key] = clean_user_input($value);
				}
			}
			return $cleanedParameters;
		}



		public static function getDefaultCredentialsFile() {
			global $CREDENTIALS_FILE_DEFAULT;
			return $CREDENTIALS_FILE_DEFAULT;
		}



		private static function generateTaskFileName(string $taskName) {
			if (str_ends_with($taskName, self::$TASKFILE_POSTFIX)) {
				return $taskName;
			}
			return $taskName.self::$TASKFILE_POSTFIX;
		}
		private static function generateCredentialsFileName(string $taskName) {
			if (str_ends_with($taskName, self::$CREDENTIALSFILE_POSTFIX)) {
				return $taskName;
			}
			return $taskName.self::$CREDENTIALSFILE_POSTFIX;
		}



		private static function generateTaskNameFromParameters($parameters) {
			return self::generateTaskName($parameters['name']);
		}
		private static function generateTaskName(string $providedName = null) {
			$name = \Utils\Strings::dateTimeString();
			if ( !is_null($providedName) ) {
				return $name.'-'.$providedName;
			}
			return $name;
		}

		private static function generateTemplateNameFromParameters($parameters) {
			return self::generateTemplateName($parameters['template']);
		}
		private static function generateTemplateName(string $template) {
			return $template;
		}

		private static function generatePlatformFromParameters($parameters) {
			return $parameters['platform'] ?? 'engine';
		}
		private static function generateLocationFromParameters($parameters) {
			return self::generateLocation( $parameters['locationX'], $parameters['locationY'] );
		}
		private static function generateLocation($x, $y) {
			return [$x, $y];
		}

		private static function generateSizeFromParameters($parameters) {
			return self::generateSize( $parameters['sizeX'], $parameters['sizeY'] );
		}
		private static function generateSize(int $width, int $height) {
			return [$width, $height];
		}

		private static function generateCredentialsFileFromParameters($taskName, $parameters) {
			$useDefaultCredentials = true;
			if ( array_key_exists('platform', $parameters) && array_key_exists('username', $parameters) && array_key_exists('password', $parameters) ) {
				$useDefaultCredentials=false;
			}
			if ( array_key_exists('useDefaultCredentials', $parameters) && !$useDefaultCredentials ) {
				$useDefaultCredentials = ($parameters['useDefaultCredentials'] != false && $parameters['useDefaultCredentials'] != 'false');
			}

			if ( $useDefaultCredentials ) {
				return self::getDefaultCredentialsFile();
			}
			return self::generateCredentialsFile($taskName, $parameters['platform'],$parameters['username'],$parameters['password']);
		}
		private static function generateCredentialsFile(string $taskName, string $platform, string $username, string $password) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$fileName = self::generateCredentialsFileName($taskName);
			\Utils\Files::WriteJsonFile([$WORKSPACE_CREDENTIALS_DIR, self::generateCredentialsFileName($taskName)], [
					'platform' => $platform,
					'username' => $username,
					'password' => $password,
					'created' => \Utils\Strings::dateTimeString(),
				]);
			return $fileName;
		}

		private static function generateTaskOperationsFromParameters($parameters) {
			return self::generateOperations($parameters['taskOperations']);
		}
		private static function generatecleanupOperationsFromParameters($parameters) {
			return self::generateOperations($parameters['cleanupOperations']);
		}

		private static function generateOperations(string|array $operations) {
			if (is_string($operations) && str_starts_with($operations, '[') ) {
				$operations = json_decode($operations);
				return $operations;
			}
			if (is_array($operations)) {
				return $operations;
			}
			return [$operations];
		}
	}
?>
