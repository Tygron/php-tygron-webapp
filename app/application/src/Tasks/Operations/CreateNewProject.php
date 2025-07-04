<?php

	namespace Tasks\Operations;

	class CreateNewProject extends AbstractOperation {

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

			$task->setApiToken($token);

			$newProjectName = 'a-'.preg_replace('/[^0-9]+/', '', $task->getTaskName());
			$task->setData(['projectName'=>$newProjectName]);

		        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/save_project_as', [$sessionId, null, $newProjectName, false])->run();
		        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/session/event/editor/clear_map?token='.$token, [true])->run();

			$task->log(get_text('Project created named: %s',[$newProjectName]));

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
	}


?>
