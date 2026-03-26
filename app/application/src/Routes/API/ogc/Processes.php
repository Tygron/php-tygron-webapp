<?php

	namespace Routes\API\OGC;

	class Processes extends \Routes\API\AbstractAPI {

		private $PROCESSES = null;
		private $PUBLIC_PROCESSES = null;

		public function run( array $parameters = [] ) {

			$parameters ??= [];

			$parameters = array_merge($parameters, [
					'title' 	=> 'OGC Processes API - Processes',
					'description' 	=> 'The following Processes are available',
				]);
			$processes = $this->getRenderableProcessDefinitions();
			$parameters['processes'] = $processes;
			return $this->getRenderable( 'api/ogc/processes/processes.html', $parameters );
		}

		protected function getRenderableProcessDefinitions( bool $includeHidden = false) {
			$processes = $this->getProcesses();
			$definitions = [];
			foreach ( $processes as $key => $value ) {
				if ( $value->getIsHidden() && !$includeHidden ) {
					continue;
				}
				$definition = $value->getRenderableProcessDefinition();
				$definition['link'] = strtolower($value->name());
				array_push($definitions, $definition);
			}
			return $definitions;
		}

		protected function getOGCProcessDefinitions( bool $includeHidden = false) {
			$processes = $this->getProcesses();
			$definitions = [];
			foreach ( $processes as $key => $value ) {
				if ( $value->getIsHidden() && !$includeHidden ) {
					continue;
				}
				$definition = $value->getOGCProcessDefinition();
				array_push($definitions, $definition);
			}
			return $definitions;
		}

		protected function getProcesses() {
			if ( is_null($this->PROCESSES) ) {
				$processes = [];
				$possibleProcessClasses = \Utils\Classes::getClassesInFolder([__DIR__,'processes']);
				foreach ( $possibleProcessClasses as $key => $processClass ) {
					try {
						$process = new $processClass();
						array_push($processes, $process);
					} catch ( \Throwable $e ) {
						//Not instantiable, or not right kind of class
					}
				}
				$this->PROCESSES = $processes;
			}
			return $this->PROCESSES;
		}
	}

?>
