<?php

	namespace Operations;

	class KeepAlive {

		public static $enum = 'SHORT';

		public function __construct() {

		}

		public static function run($task) {
			global $WORKSPACE_CREDENTIALS_DIR;
			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::get($token, $credentials['platform'], 'api/session/info')->run();
			$sessionId = $curlTask->getContent()['id'];

			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/set_session_keep_alive', [$sessionId, self::$enum])->run();

			$task->save();
		}

		public static function checkReadyForOperation($task) {
			return !empty($task->getApiToken());
		}

		public static function checkOperationComplete($task, bool $thrown = true) {
			global $WORKSPACE_CREDENTIALS_DIR;

			try {
				$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
				$token = $task->getApiToken();

				$curlTask = \Curl\TygronCurlTask::get($token, $credentials['platform'], 'api/session/info')->run();
				$sessionId = $curlTask->getContent()['id'];

				$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/get_session_keep_alive', [$sessionId])->run();

				return ($curlTask->getStatus() == 200) && (str_contains( strtolower($curlTask->getContent()), strtolower(self::$enum) ));
			} catch (\Throwable $e) {
				if ($thrown) {
					throw $e;
				}
				return false;
			}
		}
	}


?>
