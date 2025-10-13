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
			try {
				$token = $task->getApiToken();

				//TODO: Check whether project still running

				$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/info')->run();
				if ($curlTask->getContent()['state']=='NORMAL') {
					return true;
				}

			} catch (\Throwable $e) {
				return $e->getMessage();
			}
		}

		public function checkComplete( \Tasks\Task $task ) {

			//TODO: Check whether project still running

			return (count( $this->getServiceOverlayIds( $task ) ) === 0);
		}

		protected function getServiceOverlayIds( $task ) {
			$overlayIds = $task->getData('serviceOverlayIds') ?? null;
			if ( !is_array($overlayIds) ) {
				$overlayIds = [];
				$token = $task->getApiToken();
				$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/overlays')->run();
				foreach ( $curlTask->getContent() as $index => $overlay ) {
					if ( !(in_array($overlay['type'] ?? null, ['WMS','WCS'])) ) {
						continue;
					}
					if ( !($overlay['active'] === false) ) {
						continue;
					}
					$overlayIds[] = $overlay['id'];
				}
			}
			return $overlayIds;
		}
	}


?>
