<?php

	namespace Tasks\Operations;

	class CreateNewProject extends AbstractOperation {

		protected static int $MAX_ATTEMPTS = 25;
		protected static int $MAX_LENGTH = 20;
		protected static string $PROJECT_NAME = 'projectName';
		protected $tried = [];

                public function getInputParameters() {
                        return [
                                        SELF::$PROJECT_NAME => null,
                                ];
                }

		public function run( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);

			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/start', ['EDITOR', $task->getTemplateName()])->run();
		        $sessionId = $curlTask->getContent();
			if ( !$curlTask->getResponseIsSuccess() ) {
				$task->log(get_text('Could not start project for reason: %s',[$curlTask->getContent()]));
				throw new \Exception( $curlTask->getContent() );
			}
			$task->log(get_text('Started template: %s',[$task->getTemplateName()]));

		        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/join', [$sessionId, 'EDITOR'])->run();
			$token = $curlTask->getContent()['apiToken'];
			$task->log(get_text('Connected to session with id: %s',[$sessionId]));
			$task->save();

			$task->setApiToken($token);

			$newProjectName = $this->getNewProjectName($task);
			$attemptName = $newProjectName;
			for ( $i = 0 ; $i < self::$MAX_ATTEMPTS ; $i++ ) {
				$attemptName = $this->renameProjectForAttempt($newProjectName);
			        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/save_project_as', [$sessionId, null, $attemptName, false])->run();
				if ( $curlTask->getStatus() == 400 ) {
					$task->log(get_text('Project name %s: %s', [$attemptName, $curlTask->getContent()]));
					continue;
				}
				$newProjectName = $attemptName;
				break;
			}
			if ( $newProjectName != $attemptName ) {
				throw new \Exception( get_text('Project name not available, could not change to free name (%s, %s attempts)',[$newProjectName, self::$MAX_ATTEMPTS]) );
			}
			$this->setNewProjectName($task, $newProjectName);

		        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/session/event/editor/clear_map?token='.$token, [true])->run();

			$task->log(get_text( 'Project created named: %s', [$newProjectName] ));

			$task->save();
		}

		public function checkReady( \Tasks\Task $task ) {
			return empty($task->getApiToken());
		}

		public function checkComplete( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
			$token = $task->getApiToken();

	        	$curlTask = \Curl\TygronCurlTask::get($credentials, $credentials['platform'], 'api/session/location/?token='.$token)->run();
			return ($curlTask->getStatus() == 400) && (str_contains( strtolower($curlTask->getContent()), 'not set' ));
		}

		public function getNewProjectName( \Tasks\Task $task ) {
			$newProjectName = $task->getData( SELF::$PROJECT_NAME ) ?? '';
			if ( empty($newProjectName) ) {
				$newProjectName = 'a-'.preg_replace('/[^0-9]+/', '', $task->getTaskName());
				$this->setNewProjectName( $task, $newProjectName );
			}
			return $newProjectName;

		}
		public function setNewProjectName( \Tasks\Task $task, $newProjectName) {
			$task->setData([ SELF::$PROJECT_NAME => $newProjectName ]);
		}

		public function renameProjectForAttempt( string $originalName )  {
			$attempt = $this->tried[$originalName] ?? 0 ;
			$this->tried[$originalName] = $attempt+1;
			if ( $attempt === 0 ) {
				return $originalName;
			}
			$attemptsString = '-'.strval($attempt);
			$newAttemptName = substr( $originalName,0,self::$MAX_LENGTH - strlen($attemptsString) ) . $attemptsString ;

			return $newAttemptName;
		}
	}


?>
