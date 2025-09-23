<?php

	namespace Tasks\Operations;

	class ValidateCredentialsFile extends AbstractOperation {

		public function run( \Tasks\Task $task ) {
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
			$task->save();

			$credentials['password'] = $curlTask->getContent();

                        \Utils\Files::WriteJsonFile([$WORKSPACE_CREDENTIALS_DIR, $credentialsFileName], $credentials);
			return true;
		}


		public function checkReady( \Tasks\Task $task ) {
			return true;
		}

		public function checkComplete( \Tasks\Task $task ) {
			return $task->getOperationResult() === true;
		}
	}


?>
