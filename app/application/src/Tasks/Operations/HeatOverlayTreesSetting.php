<?php

	namespace Tasks\Operations;

	class HeatOverlayTreesSetting extends AbstractOperation {

		protected static $PREQUAL_NAME = 'FOLIAGE_HEIGHT';

		public function getInputParameters() {
			return [
					'heatOverlayIds' => null,
				];
		}

		public function run( \Tasks\Task $task ) {
			$token = $task->getApiToken();

			$heatOverlayIds = $this->getHeatOverlayIds( $task );
			if ( count($heatOverlayIds) === 0 ) {
				return;
			}

			foreach ( $heatOverlayIds as $key => $overlayId ) {
				$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
						'api/session/event/editoroverlay/remove_prequel',
						[$overlayId, self::$PREQUAL_NAME]
					)->run();
				$task->log(get_text('Heat Overlay with ID %s, Foliage Prequel disconnected', [$overlayId]));
			}
			$task->save();
		}

		public function checkReady( \Tasks\Task $task ) {
			return !empty($task->getApiToken( $task ));
		}

		public function checkComplete( \Tasks\Task $task ) {

			//TODO: Check whether project still running

			return (count( $this->getHeatOverlayIds( $task ) ) === 0);
		}

		protected function getHeatOverlayIds( $task ) {
			$overlayIds = $task->getData('heatOverlayIds') ?? null;
			$foundOverlayIds = [];

			$token = $task->getApiToken();
			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/overlays')->run();

			foreach ( $curlTask->getContent() as $index => $overlay ) {
				$overlayId = $overlay['id'];
				if ( is_array($overlayIds) && (!in_array($overlayId, $overlayIds)) ) {
					continue;
				}
				if ( !(in_array($overlay['type'] ?? null, ['HEAT_STRESS'])) ) {
					continue;
				}
				if ( !array_key_exists(self::$PREQUAL_NAME, $overlay['prequels'])) {
					continue;
				}
				$foundOverlayIds[] = $overlayId;
			}

			return $foundOverlayIds;
		}
	}


?>
