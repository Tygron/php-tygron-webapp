<?php

	namespace Tasks\Operations;

	class OutputWebViewer3DHtml extends AbstractOperation {

		public function run( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;
			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/stakeholders/')->run();
			$stakeholders = $curlTask->getContent();
			$firstStakeholder = (count($stakeholders) > 0) ? $stakeholders[0] : null;
			$stakeholderToken = $firstStakeholder['webToken'];
			$stakeholderToken = substr(  $token, 0, -strlen($stakeholderToken) ) . $stakeholderToken;

			$webViewerPath = 'web/3dmap.html';
			$curlTask = \Curl\TygronCurlTask::get($stakeholderToken, $task->getPlatform(), $webViewerPath)->run();

			$task->setOutput('webViewer3DHtml', $curlTask->getRequest()['url']);

			$task->save();
		}


		public function checkReady( \Tasks\Task $task ) {
			return !empty($task->getApiToken());
		}

		public function checkComplete( \Tasks\Task $task ) {
			return !empty($task->getOutput('webViewer3DHtml'));
		}
	}


?>
