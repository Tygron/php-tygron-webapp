<?php

	namespace Tasks\Operations;

	class GenerateProject extends AbstractOperation {

		public function getInputParameters() {
			return [
					'location'	=> null,
					'areaOfInterest' => null
				];
		}

		public function run( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;
			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(), 'api/session/event/editor/set_initial_map_size', $task->getSize())->run();
			if ( !$curlTask->getResponseIsSuccess() ) {
				throw new \Exception($curlTask->getContent());
			}
			$task->log(get_text( 'Project size set to: %s', [ implode(', ',$task->getSize()) ] ));
			$task->save();

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(), 'api/session/event/editor/start_map_creation', [$task->getLocation()[0], $task->getLocation()[1], null, $task->getData('areaOfInterest')])->run();
			if ( !$curlTask->getResponseIsSuccess() ) {
				throw new \Exception($curlTask->getContent());
			}
			$task->log(get_text( 'Started project generation' ));

			$task->setOperationFeedback( '0 %' );
			$task->save();
		}


		public function checkReady( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			try {
				$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
				$token = $task->getApiToken();

		        	$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/location/')->run();
				return ($curlTask->getStatus() == 400) && (str_contains( strtolower($curlTask->getContent()), 'not set' ));
			} catch (\Throwable $e) {
				return false;
			}
		}

		public function checkComplete( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
			$token = $task->getApiToken();

			//TODO: Check whether project still running

			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/progress')->setTimeout(5)->run();
			if ( $curlTask->isTimedOut() ) {
				//Acceptable that the session is non-responsive for a while
				return false;
			}
			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/progress')->run();
			if ( !is_countable($curlTask->getContent()) ) {
				throw new \Exception('No progress items (not countable)');
			}
			if ( count($curlTask->getContent())==0 ) {
				throw new \Exception('No progress items (count is 0)');
			}

			$lastItem = $curlTask->getContent()[array_key_last($curlTask->getContent())];

			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/info')->run();
			if ($curlTask->getContent()['state']=='NORMAL') {
				return true;
			}

			$task->setOperationFeedback( round($curlTask->getContent()['loaded'] * 100).' %' );
			$task->save();

			return false;
		}
	}


?>
