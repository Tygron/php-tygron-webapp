<?php

	namespace Tasks\Operations;

	class StartProject extends AbstractOperation {

		protected static string $PROJECT_NAME = 'projectName';
		protected static string $JOIN_PREFERRED = 'joinPreferred';
		protected static string $JOIN_IF_NEEDED = 'joinIfNeeded';


                public function getInputParameters() {
                        return [
                                        SELF::$PROJECT_NAME => null,
                                        SELF::$JOIN_PREFERRED => null,
                                        SELF::$JOIN_IF_NEEDED => null,
                                ];
                }

		public function run( \Tasks\Task $task ) {

			$joinBefore = $task->getData(self::$JOIN_PREFERRED);
			$joinAfter = $task->getData(self::$JOIN_IF_NEEDED);

			$joinBefore = is_string($joinBefore) ? strtolower($joinBefore) == 'true' : $joinBefore;
			$joinAfter = is_string($joinAfter) ? strtolower($joinAfter) == 'true' : $joinAfter;

			$result = [];
			$sessionId = null;
			if ( $joinBefore || $joinAfter ) {
				$sessionId = $this->getJoinableProject($task);
			}
			if ( $joinBefore ) {
				try {
					$joined = $this->joinProject($task, $sessionId);
					if ($joined === true) {
						return true;
					}
				} catch ( \Throwable $e ) {
					array_push( $result, $e->getMessage() );
				}
			}

			try {
				$joined = $this->joinProject( $task, $this->startProject($task) );
				if ($joined === true) {
					return true;
				}
			} catch ( \Throwable $e ) {
				array_push( $result, $e->getMessage() );
			}

			if ( $joinAfter ) {
				try {
					$joined = $this->joinProject($task, $sessionId);
					if ($joined === true) {
						return true;
					}
				} catch ( \Throwable $e ) {
					array_push( $result, $e->getMessage() );
				}
			}

			if ( count($result) > 0 ) {
				throw new \Exception( implode( ', ', $result) );
			}

			throw new \Exception(get_text('An unknown issue prevented %s operation from completing, without explicit error', ['StartProject']) );

		}

		protected function startProject( \Tasks\Task $task ) {

			$credentials = $this->getCredentials();

			$projectName = $task->getData(self::$PROJECT_NAME);

			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/start', ['EDITOR', $projectName] )->run();
			if ( !$curlTask->getResponseIsSuccess() ) {
				$message = get_text('Could not start project for reason: %s',[$curlTask->getContent()]);
				$task->log($message);
				$task->save();
				throw new \Exception($message);
			}
		        $sessionId = $curlTask->getContent();

			$task->log(get_text('Started Project: %s, in session with ID: %s',[$projectName, $sessionId]));
			$task->save();
			return $sessionId;
		}

		protected function joinProject( \Tasks\Task $task, $sessionId ) {
			if ( is_null($sessionId) ) {
				return null;
			}

			$credentials = $this->getCredentials();

		        $curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/join', [$sessionId, 'EDITOR'])->run();
			if ( !$curlTask->getResponseIsSuccess() ) {
				$message = get_text('Could not join Session with ID %s, for reason: %s',[$curlTask->getContent()]);
				$task->log($message);
				$task->save();
				throw new \Exception($message);
			}

			$token = $curlTask->getContent()['apiToken'];
			$task->log(get_text('Connected to session with id: %s',[$sessionId]));
			$task->setApiToken($token);
			$task->save();
			return true;
		}

		protected function getJoinableProject( \Tasks\Task $task ) {
			$credentials = $this->getCredentials();
			$projectName = $task->getData(self::$PROJECT_NAME);

			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/get_joinable_sessions', [] )->run();

			$runningSessions = $curlTask->getContent();
			$joinable = null;

			foreach( $runningSessions as $key => $value ) {
				if ( $value['name'] != $projectName ) {
					continue;
				}
				if ( is_null($joinable) ) {
					$joinable = $value['id'];
				} else {
					return null;
				}
			}
			return $joinable;
		}

		public function checkReady( \Tasks\Task $task ) {
			return empty($task->getApiToken());
		}

		public function checkComplete( \Tasks\Task $task ) {
			global $WORKSPACE_CREDENTIALS_DIR;

			$credentials = \Utils\Files::readJsonFile([$WORKSPACE_CREDENTIALS_DIR, $task->getCredentialsFileName()]);
			$token = $task->getApiToken();

	        	$curlTask = \Curl\TygronCurlTask::get($credentials, $credentials['platform'], 'api/session/location/?token='.$token)->run();
			return $curlTask->getResponseIsSuccess();
		}
	}


?>
