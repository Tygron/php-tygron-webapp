<?php

	namespace Tasks\Operations;

	class StartProject extends AbstractOperation {

		protected static string $PROJECT_NAME = 'projectName';

                public function getInputParameters() {
                        return [
                                        SELF::$PROJECT_NAME => null,
                                ];
                }

		public function run( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$credentials = $this->getCredentials();

			$projectName = $task->getData(self::$PROJECT_NAME);

			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/start', ['EDITOR', $projectName] )->run();
		        $sessionId = $curlTask->getContent();
			if ( !$curlTask->getResponseIsSuccess() ) {
				$task->log(get_text('Could not start project for reason: %s',[$curlTask->getContent()]));
				throw new \Exception( $curlTask->getContent() );
			}
			$task->log(get_text('Started Project: %s',[$projectName]));

		        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/join', [$sessionId, 'EDITOR'])->run();
			$token = $curlTask->getContent()['apiToken'];
			$task->log(get_text('Connected to session with id: %s',[$sessionId]));
			$task->setApiToken($token);
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
			return $curlTask->getResponseIsSuccess();
		}
	}


?>
