<?php

	namespace Operations;

	class OutputWebViewer3DHtml {

		public function __construct() {

		}

		public static function run($task) {
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


		public static function checkReadyForOperation($task) {
			return !empty($task->getApiToken());
		}

		public static function checkOperationComplete($task, bool $thrown = true) {
			return !empty($task->getOutput('webViewer3DHtml'));
		}
	}


?>
