<?php

	namespace Tasks\Operations;

	class ActivateServiceOverlays extends AbstractOperation {

		public function getInputParameters() {
			return [
					'serviceOverlayIds' => null
				];
		}

		public function run( \Tasks\Task $task ) {
			$token = $task->getApiToken();

			$serviceOverlayIds = $this->getServiceOverlayIds( $task );
			if ( count($serviceOverlayIds) === 0 ) {
				return;
			}

			foreach ( $serviceOverlayIds as $key => $overlayId ) {
				$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
						'api/session/event/editoroverlay/set_grid_active',
						[$overlayId, true]
					)->run();
				$task->log(get_text('Service Overlay with ID %s activated', [$overlayId]));
			}
			$task->save();
		}

		public function checkReady( \Tasks\Task $task ) {
			return !empty($task->getApiToken( $task ));
		}

		public function checkComplete( \Tasks\Task $task ) {

			//TODO: Check whether project still running

			return (count( $this->getServiceOverlayIds( $task ) ) === 0);
		}




		protected function getServiceOverlayIds( $task ) {
			$overlayIds = $task->getData('serviceOverlayIds') ?? null;
			$foundOverlayIds = [];

			$token = $task->getApiToken();
			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/overlays')->run();

			foreach ( $curlTask->getContent() as $index => $overlay ) {
				$overlayId = $overlay['id'];
				if ( is_array($overlayIds) && (!in_array($overlayId, $overlayIds)) ) {
				        continue;
				}
				if ( !(in_array($overlay['type'] ?? null, ['WMS','WCS'])) ) {
					continue;
				}
				if ( !($overlay['active'] === false) ) {
					continue;
				}
				$foundOverlayIds[] = $overlay['id'];
			}
			return $foundOverlayIds;
		}
	}


?>
