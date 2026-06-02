<?php

	namespace Tasks\Operations;

	class GetAvailableProjects extends AbstractOperation {

		protected static string $INCLUDE_UNIVERSAL = 'includeUniversal';
		protected static string $CRS = 'crs';

                public function getInputParameters() {
                        return [
                                        SELF::$INCLUDE_UNIVERSAL => null,
					SELF::$CRS => null,
                                ];
                }

		public function run( \Tasks\Task $task ) {

			$credentials = $this->getCredentials();
			$curlTask = \Curl\TygronCurlTask::post($credentials, $credentials['platform'], 'api/event/io/get_startable_projects', [] )
					->setParameters([ 'crs'=> $task->getCrs() ])
					->run();

			if ( !$curlTask->getResponseIsSuccess() ) {
				$message = get_text('Could not obtain projects, for reason: %s',[$curlTask->getContent()]);
				$task->log($message);
				$task->save();
				throw new \Exception($message);
			}

			$projects = $curlTask->getContent();
			$includeUniversalProjects = $task->getData(self::$INCLUDE_UNIVERSAL);
			$includeUniversalProjects = ($includeUniversalProjects == 'true') || ($includeUniversalProjects === true);

			if ( !$includeUniversalProjects ) {
				$projects = array_filter($projects, function($project) {
						return !( $project['universal'] ?? false );
					});
			}

			$projectNames = array_values(array_map( function($project){ return $project['fileName'];}, $projects ));
			$projectData = [];
			foreach ( $projects as $index => $value ) {
					$projectData[$value['fileName']] = $value;
				}
			$task->setOperationFeedback(get_text('Found %s project(s)',[count($projects)]));
			$task->setOutput([
					'projectNames' => $projectNames,
					'projectData' => $projectData,
				]);

			$task->save();
			return true;
		}

		public function checkReady( \Tasks\Task $task ) {
			return !empty($this->getCredentials());
		}

		public function checkComplete( \Tasks\Task $task ) {
			return !empty($task->getOperationFeedback());
		}
	}


?>
