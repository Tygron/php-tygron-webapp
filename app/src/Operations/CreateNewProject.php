<?php

	namespace Operations;

	class CreateNewProject {

		public function __construct() {

		}

		public static function run($task) {
			global $WORKSPACE_CREDENTIALS_DIR;
			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);

			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/start', ['EDITOR', $task->getTemplateName()])->run();
		        $sessionId = $curlTask->getContent();
			$task->log(get_text('Started template: %s',[$task->getTemplateName()]));

		        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/join', [$sessionId, 'EDITOR'])->run();
		        $token = $curlTask->getContent()['apiToken'];
			$task->log(get_text('Connected to session with id: %s',[$sessionId]));

			$task->setApiToken($token);

			$newProjectName = \Utils\Strings::dateTimeString();
			$task->setData(['projectName'=>$newProjectName]);

		        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/save_project_as', [$sessionId, null, $newProjectName, false])->run();
		        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/session/event/editor/clear_map?token='.$token, [true])->run();

			$task->log(get_text('Project created'));

			$task->save();
		}

		public static function checkReadyForOperation($task) {
			return empty($task->getApiToken());
		}

		public static function checkOperationComplete($task, bool $thrown = true) {
			global $WORKSPACE_CREDENTIALS_DIR;

			try {
				$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
				$token = $task->getApiToken();

		        	$curlTask = \Curl\TygronCurlTask::get($credentials, $credentials['platform'], 'api/session/location/?token='.$token)->run();
				return ($curlTask->getStatus() == 400) && (str_contains( strtolower($curlTask->getContent()), 'not set' ));
			} catch (\Throwable $e) {
				if ($thrown) {
					$task->log(get_text('An exception occurred',[$e->getMessage()]));
					throw $e;
				}
				return false;
			}
		}
	}


?>
