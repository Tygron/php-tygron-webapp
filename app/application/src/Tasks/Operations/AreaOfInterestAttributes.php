<?php

	namespace Tasks\Operations;

	class AreaOfInterestAttributes extends AbstractOperation {

		public function getInputParameters() {
			return [
					'areaOfInterestAttributes' => []
				];
		}

		public function run( \Tasks\Task $task ) {
			$token = $task->getApiToken();

			$areaIds = [];
			$attributeNames = [];
			$attributeValues = [];

			if ( empty($task->getData('areaOfInterestAttributes')) ) {
				return;
			}

			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/areas-interest_area')->run();
			foreach ( $curlTask->getContent() as $key => $area ) {
				foreach ( $task->getData('areaOfInterestAttributes') as $attName => $attValue ) {
					array_push($areaIds, $area['id']);
					array_push($attributeNames, $attName);
					array_push($attributeValues, $attValue);
				}
			}

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorarea/set_attributes',
					[$areaIds, $attributeNames, $attributeValues]
				)->run();

			$task->save();
		}


		public function checkReady( \Tasks\Task $task ) {
			try {
				$token = $task->getApiToken();

				//TODO: Check whether project still running

				$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/areas-interest_area')->run();
				if ( count($curlTask->getContent())==0 ) {
					$task->log(get_text('There is no area of interest to add attributes to'));
					return true;
				}

				$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/info')->run();
				if ($curlTask->getContent()['state']=='NORMAL') {
					return true;
				}

			} catch (\Throwable $e) {
				return $e->getMessage();
			}
		}

		public function checkComplete( \Tasks\Task $task ) {

			$token = $task->getApiToken();

			//TODO: Check whether project still running

			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/areas-interest_area')->run();
			if ( count($curlTask->getContent())==0 ) {
				return true;
			}
			else {
				$attributesToFind = $task->getData('areaOfInterestAttributes');
				if ( empty($attributesToFind) ) {
					return true;
				}
				foreach ( $curlTask->getContent() as $key => $area ) {
					foreach ( $attributesToFind as $key=> $value ) {
						if ( !array_key_exists($key, $area['attributes']) ) {
							if ( !is_null($value) && is_numeric($value) ) {
								return false;
							}
						}
					}
				}
				return true;
			}
		}
	}


?>
