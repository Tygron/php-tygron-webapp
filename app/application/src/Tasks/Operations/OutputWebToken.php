<?php

	namespace Tasks\Operations;

	class OutputWebToken extends AbstractOperation {

		public function run( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;
			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/stakeholders/')->run();
			$stakeholders = $curlTask->getContent();
			try {
				$firstStakeholder = (count($stakeholders) > 0) ? $stakeholders[ array_key_first($stakeholders) ] : null;
				$stakeholderToken = $firstStakeholder['webToken'];
			} catch (\Throwable $e) {
				$task->log('Unexpected response for stakeholder: '.json_encode($stakeholders, JSON_PRETTY_PRINT));
				$task->retryCurrentOperation('Unexpected response');
			}
			$stakeholderToken = substr(  $token, 0, -strlen($stakeholderToken) ) . $stakeholderToken;

			$task->setOutput('webToken', $stakeholderToken);

			$task->save();
		}


		public function checkReady( \Tasks\Task $task ) {
			return !empty($task->getApiToken());
		}

		public function checkComplete( \Tasks\Task $task ) {
			return !empty($task->getOutput('webToken'));
		}
	}


?>
