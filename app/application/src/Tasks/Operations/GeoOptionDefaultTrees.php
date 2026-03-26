<?php

	namespace Tasks\Operations;

	class GeoOptionDefaultTrees extends AbstractOperation {

		public function getInputParameters() {
			return [
					'useDefaultTrees' => true
				];
		}

		public function run( \Tasks\Task $task ) {
			$token = $task->getApiToken();

			$useDefaultTrees = $task->getData('useDefaultTrees');

			if ( is_null($useDefaultTrees) ) {
				return true;
			}

			if ( is_string($useDefaultTrees) ) {
				$useDefaultTrees = (strtolower($useDefaultTrees) == 'true');
			}

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorgeooption/set_string',
					['TREES', $useDefaultTrees ? 'DEFAULT' : 'NONE']
				)->run();

			$task->log(get_text('Geo Option for trees: %s', [$useDefaultTrees ? 'use default trees' : 'do not use default trees']));
			$task->save();

			return true;
		}

		public function checkReady( \Tasks\Task $task ) {
			return !empty($task->getApiToken( $task ));
		}

		public function checkComplete( \Tasks\Task $task ) {

			//TODO: Check whether project still running
			return $task->getOperationResult() === true;
		}
	}


?>
