<?php

	namespace Operations;

	class AreaOfInterestAttributes {

		public function __construct() {

		}

		public static function run($task) {
			$token = $task->getApiToken();

			$areaIds = [];
			$attributeNames = [];
			$attributeValues = [];

			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/areas-interest_area')->run();
			foreach ( $curlTask->getContent() as $key => $area ) {
				foreach ( $task->getData()['areaOfInterestAttributes'] as $attName => $attValue ) {
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


		public static function checkReadyForOperation($task) {
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

		public static function checkOperationComplete($task, bool $thrown = true) {

			try {
				$token = $task->getApiToken();

				//TODO: Check whether project still running

				$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/areas-interest_area')->run();
				if ( count($curlTask->getContent())==0 ) {
					return true;
				}
				else {
					$attributesToFind = $task->getData()['areaOfInterestAttributes'];
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
			} catch (\Throwable $e) {
				if ($thrown) {
					throw $e;
				}
				return false;
			}
		}
	}


?>
