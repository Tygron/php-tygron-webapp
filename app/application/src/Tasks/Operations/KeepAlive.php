<?php

	namespace Tasks\Operations;

	class KeepAlive extends AbstractOperation {

		public static $enum = 'SHORT';

		public function run( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;
			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::get($token, $credentials['platform'], 'api/session/info')->run();
			$sessionId = $curlTask->getContent()['id'];

			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/set_session_keep_alive', [$sessionId, self::$enum])->run();

			$task->save();
		}

		public function checkReady( \Tasks\Task $task ) {
			return !empty($task->getApiToken());
		}

		public function checkComplete( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::get($token, $credentials['platform'], 'api/session/info')->run();
			$sessionId = $curlTask->getContent()['id'];

			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/get_session_keep_alive', [$sessionId])->run();

			return ($curlTask->getStatus() == 200) && (str_contains( strtolower($curlTask->getContent()), strtolower(self::$enum) ));
		}
	}


?>
