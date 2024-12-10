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
					'location'=>[0,0],
					'size'=>[500,500],

					'apiToken'=>'',
					'operations'=>[],
					'currentOperation'=>''
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

					default:
						$this->data[$key] = $value;
						break;
				}
			}
		}

		public function setCredentialsFile( string $credentialsFile ) {
			$this->data['credentialsFile'] = $credentialsFile;
		}

		public function setTaskName( string $taskName ) {
			$this->data['taskName'] = $taskName;
		}
		public function setTemplateName( string $templateName ) {
			$this->data['templateName'] = $templateName;
		}
		public function setLocation( array $location ) {
			$this->data['location'] = $location;
		}
		public function setSize( array $size ) {
			$this->data['size'] = $size;
		}

		public function setOperation( array $operations ) {
			$this->data['operations'] = $operations;
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



		public function save() {
			global $WORKSPACE_TASK_DIR;

			\Utils\Files::writeJsonFile([$WORKSPACE_TASK_DIR, $this->getTaskFileName()], $this->getData());
		}

		public static function generateTask($parameters) {

			$task = new Task([]);
			$task->setTaskName( 		self::generateTaskNameFromParameters($parameters) );
			$task->setTemplateName( 	self::generateTemplateNameFromParameters($parameters) );
			$task->setLocation(		self::generateLocationFromParameters($parameters) );
			$task->setSize( 		self::generateSizeFromParameters($parameters) );

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
					default:
						$cleanedParameters[$key] = clean_user_input($value);
				}
			}
			return $cleanedParameters;
		}



		private static function generateTaskFileName(string $taskName) {
			return $taskName.self::$TASKFILE_POSTFIX;
		}
		private static function generateCredentialsFileName(string $taskName) {
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
			if ( array_key_exists('username', $parameters) && array_key_exists('password', $parameters) ) {
				$useDefaultCredentials=false;
			}
			if ( array_key_exists('useDefaultCredentials', $parameters) ) {
				$useDefaultCredentials = ($parameters['useDefaultCredentials'] != false && $parameters['useDefaultCredentials'] != 'false');
			}

			if ( $useDefaultCredentials ) {
				return $CREDENTIALS_FILE_DEFAULT;
			}
			return self::generateCredentialsFile($taskName, $parameters['username'],$parameters['password']);
		}
		private static function generateCredentialsFile(string $taskName, string $username, string $password) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$fileName = self::generateCredentialsFileName($taskName);
			\Utils\Files::WriteJsonFile([$WORKSPACE_CREDENTIALS_DIR, self::generateCredentialsFileName($taskName)], [
					'username' => $username,
					'password' => $password,
					'created' => \Utils\Strings::dateTimeString(),
				]);
			return $fileName;
		}
	}
?>
