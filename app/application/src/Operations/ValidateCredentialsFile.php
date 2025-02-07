<?php

	namespace Operations;

	class ValidateCredentialsFile {

		public function __construct() {

		}

		public static function run($task) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$credentialsFileName = $task->getCredentialsFileName();
			if ( \Tasks\TaskCredentials::isDefaultCredentialsFile($credentialsFileName) ) {
				return true;
			}

			$credentials = $task->getCredentials();
			if ( is_null($credentials) ) {
				throw new \Exception('No credentials provided');
			}
			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/user/get_my_login_key', [])->run();
			if ( !$curlTask->getResponseIsSuccess() ) {
				throw new \Exception('Credentials were invalid');
			}
			$task->log(get_text('Validated credentials for user'));

			$credentials['password'] = $curlTask->getContent();

                        \Utils\Files::WriteJsonFile([$WORKSPACE_CREDENTIALS_DIR, $credentialsFileName], $credentials);
			return true;
		}


		public static function checkReadyForOperation($task) {
			return true;
		}

		public static function checkOperationComplete($task, bool $thrown = true) {
			return $task->getOperationResult() === true;
		}
	}


?>
