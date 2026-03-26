<?php

	namespace Tasks\Operations;

	class TreesImportWFS extends AbstractOperation {

		private static string $TREES_FUNCTION_ID = '440';
		private static string $TREES_HEIGHT_ATTRIBUTE = 'FLOOR_HEIGHT_M';

		public function getInputParameters() {
			return [
					'treesImportWFSUrl' => [],
					'treesImportWFSLayer' => [],
					'treesImportWFSHeightAttribute' => '',
				];
		}

		public function run( \Tasks\Task $task ) {
			$token = $task->getApiToken();

			$wfsUrl = $task->getData('treesImportWFSUrl');
			$wfsLayer = $task->getData('treesImportWFSLayer');
			$wfsTreesHeightAttribute = $task->getData('treesImportWFSHeightAttribute');

			if ( empty($wfsUrl) ) {
				return;
			}

			$pluginId = $this->importTreesWFS($task, $wfsUrl, $wfsLayer, $wfsTreesHeightAttribute);

			$task->log(get_text('GeoPlugin (ID:%s) with Trees WFS source added',[$pluginId]));
			$task->save();
		}

		public function importTreesWFS( \Tasks\Task $task, $url, $layer, $attribute ) {

			$sourceId = $this->createWFSSource( $task, $url );
			$pluginId = $this->createGeoPlugin( $task, $attribute );

			$this->setGeoPluginSource( $task, $pluginId, $sourceId, $layer );

			return $pluginId;
		}

		protected function createWFSSource( \Tasks\Task $task , $url ) {
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorsource/add_service',
					['WFS_JSON', 'Trees source via WebApp', $url, 'Automated Source']
				)->run();

			return $curlTask->getContent();
		}

		protected function createGeoPlugin( \Tasks\Task $task, $heightAttribute) {
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorgeoplugin/add',
					['BUILDING']
				)->run();
			$pluginId = $curlTask->getContent();
			$linkId = $this->createGeoLink($task, $heightAttribute);

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorgeoplugin/add_link',
					[$pluginId, $linkId]
				)->run();
			return $pluginId;
		}

		protected function createGeoLink( \Tasks\Task $task, $heightAttribute ) {
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorgeolink/add',
					['BUILDING', 'Trees link']
				)->run();
			$linkId = $curlTask->getContent();

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorgeolink/set_function',
					[$linkId, self::$TREES_FUNCTION_ID]
				)->run();
			if ( !is_null($heightAttribute) ) {
				$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
						'api/session/event/editorgeolink/set_mapping',
						[$linkId, $heightAttribute, self::$TREES_HEIGHT_ATTRIBUTE]
					)->run();
			}
			return $linkId;
		}

		protected function setGeoPluginSource( \Tasks\Task $task, $pluginId, $sourceId, $layer ) {
			$token = $task->getApiToken();

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorgeoplugin/set_source',
					[$pluginId, $sourceId]
				)->run();
			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorgeoplugin/set_layer_name',
					[$pluginId, $layer]
				)->run();
			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorgeoplugin/set_new_project',
					[$pluginId, true]
				)->run();
		}


		public function checkReady( \Tasks\Task $task ) {
			try {
				$token = $task->getApiToken();

				return true;

			} catch (\Throwable $e) {
				return $e->getMessage();
			}
		}

		public function checkComplete( \Tasks\Task $task ) {

			$token = $task->getApiToken();

			//TODO: Check whether project still running

			return true;
		}
	}


?>
