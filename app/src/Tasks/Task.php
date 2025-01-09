<?php

	namespace Tasks;

	class Task {

		public static string $TASKFILE_POSTFIX = '-task.json';
		public static string $CREDENTIALSFILE_POSTFIX = '-credentials.json';

		private array $data = [];

		public function __construct( $parameters ) {
			$this->data = [
					'taskName'=>'',
					'credentialsFile'=>'',
					'templateName'=>'',
					'platform'=>'engine',
					'location'=>[0,0],
					'size'=>[500,500],

					'apiToken'=>'',
					'operations'=>[],
					'currentOperation'=>'',
					'startedOperation'=>'',
					'completed'=>false,

					'output'=>[]
				];
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

					case 'operations':
						$this->setOperations($value);
						break;
					case 'currentOperation':
						$this->setCurrentOperation($value);
						break;
					case 'startedOperation':
						$this->setStartedOperation($value);
						break;
					case 'completed':
						$this->setCompleted($value);

					case 'output':
						$this->setOutput($value);

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

		public function setOperations( array $operations ) {
			$this->data['operations'] = $operations;
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
			return $this->data['operations'];
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



		public function save() {
			global $WORKSPACE_TASK_DIR;

			\Utils\Files::writeJsonFile([$WORKSPACE_TASK_DIR, $this->getTaskFileName()], $this->getData());
		}

		public static function generateTask($parameters) {
			$task = new Task([]);
			$task->setTaskName( 		self::generateTaskNameFromParameters($parameters) );
			$task->setTemplateName( 	self::generateTemplateNameFromParameters($parameters) );
			$task->setPlatform(		$parameters['platform'] ?? 'engine');
			$task->setLocation(		self::generateLocationFromParameters($parameters) );
			$task->setSize( 		self::generateSizeFromParameters($parameters) );

			$task->setOperations(		self::generateOperationsFromParameters($parameters) );

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
						$cleanedParameters[$key] = get_clean_user_input($key, '[\[\]\,\.\-"_a-zA-Z0-9]');
						break;
					default:
						$cleanedParameters[$key] = clean_user_input($value);
				}
			}
			return $cleanedParameters;
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
			global $CREDENTIALS_FILE_DEFAULT;

			$useDefaultCredentials = true;
			if ( array_key_exists('platform', $parameters) && array_key_exists('username', $parameters) && array_key_exists('password', $parameters) ) {
				$useDefaultCredentials=false;
			}
			if ( array_key_exists('useDefaultCredentials', $parameters) && !$useDefaultCredentials ) {
				$useDefaultCredentials = ($parameters['useDefaultCredentials'] != false && $parameters['useDefaultCredentials'] != 'false');
			}

			if ( $useDefaultCredentials ) {
				return $CREDENTIALS_FILE_DEFAULT;
			}
			return self::generateCredentialsFile($taskName, $platform['platform'],$parameters['username'],$parameters['password']);
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

		private static function generateOperationsFromParameters($parameters) {
			return self::generateOperations($parameters['operations']);
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
