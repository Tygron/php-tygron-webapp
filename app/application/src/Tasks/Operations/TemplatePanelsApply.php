<?php

	namespace Tasks\Operations;

	class TemplatePanelsApply extends AbstractOperation {

		protected static int $WAIT_TIME = 5;

		public function run( \Tasks\Task $task ) {
			$token = $task->getApiToken();

			$panelIds = $this->getTemplatePanelIds($task);
			if ( count($panelIds) == 0 ) {
				return true;
			}

			$curlTask = \Curl\TygronCurlTask::post($token, $task->getPlatform(),
					'api/session/event/editorpanel/apply_template_panels',
					[$panelIds]
				)->run();

			$task->save();
		}


		public function checkReady( \Tasks\Task $task ) {
			try {
				$token = $task->getApiToken();

				//TODO: Check whether project still running

				$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/info')->run();
				$state = $curlTask->getContent()['state'];
				if ( $state=='NORMAL' ) {
					return true;
				} else {
					$task->log(get_text('Session in unexpected state: %s',[$state]));
					$task->save();
					return false;
				}

			} catch (\Throwable $e) {
				return $e->getMessage();
			}
		}

		public function checkComplete( \Tasks\Task $task ) {
			//TODO: Perform check
			return true;
		}

		public function getTemplatePanelIds( \Tasks\Task $task ) {
			$token = $task->getApiToken();
			$curlTask = \Curl\TygronCurlTask::get($token, $task->getPlatform(), 'api/session/items/panels')->run();
			$templatePanelIds = [];
			foreach ( $curlTask->getContent() as $key => $value ) {
				if ( !is_null($value['mapLink'] ?? null) ) {
					array_push($templatePanelIds, $value['id']);
				}
			}
			return $templatePanelIds;
		}
	}


?>
