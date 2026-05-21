<?php

	namespace Routes\API\OGC\Processes\Abstracts;

	abstract class AbstractProcess extends \Routes\API\AbstractAPIEndpoint {

		public function run( array $parameters = [] ) {
			$method = $this->getMethod();
			$subPath = $this->getSubPath() ?? '';
			$execute = (explode('/', $subPath)[0]) == 'execute';
			if ( $execute ) {
				$method = 'POST';
			}
			$result = null;
			$parameters = array_merge($parameters,['contextName'=>$this->getRequestContext()]);
			switch( $method ) {
				case 'POST':
					$result = $this->runPost($parameters);
					break;
				case 'GET':
				default:
					$result = $this->runGet($parameters);
					break;
			}
			return $result;
		}

		protected function runGet(array $parameters = []) {
			if ( $this->getRequestedFormat('json') ) {
				return $this->getOGCProcessDefinition();
			}
			$renderable = $this->getRenderable( 'api/ogc/processes/process.html',
					$this->getRenderableProcessDefinition()
				);
                        return $renderable;
		}

		protected function runPost(array $parameters = []) {
			return $this->runGet($parameters);
		}

		public function getRenderableProcessDefinition() {
			$processDefinition = [];
			$processDefinition['title'] = $this->getTitle();
			$processDefinition['description'] = $this->getDescription();
			$processDefinition['parameters'] = $this->getRenderableParameterDefinitions();
			$processDefinition['outputs'] = $this->getRenderableOutputDefinitions();
			$processDefinition['example'] = $this->getRenderedExampleRunner();
			return $processDefinition;
		}

		public function getOGCProcessDefinition() {
			$processDefinition = [];
			$processDefinition['id'] = $this->name();
			$processDefinition['title'] = $this->getTitle();
			$processDefinition['description'] = $this->getDescription();
			$processDefinition['version'] = $this->getVersion();
			$processDefinition['jobControlOptions'] = $this->getJobControlOptions();
			$processDefinition['outputTransmission'] = 'reference';
			$processDefinition['inputs'] = $this->getOGCParameterDefinitions();
			$processDefinition['outputs'] = $this->getOGCOutputDefinitions();

			return $processDefinition;
		}

		public function getName() {
			return $this->name();
		}

		public function getTitle() {
			return $this->getName();
		}

		public function getDescription() {
			return 'No description';
		}

		public function getVersion() {
			return '0.0.0';
		}

		public function getJobControlOptions() {
			return ['async-execute'];
		}

		public function getParameterDefinitions() {
			return [];
		}

		public function getOutputDefinitions() {
			return [];
		}

		public function getIsHidden() {
			return false;
		}

		public function getRenderableParameterDefinitions(array $definitions = null) {
			$definitions = $definitions ?? $this->getParameterDefinitions();
			return $this->getRenderableDefinitions($definitions);
		}

		public function getOGCParameterDefinitions(array $definitions = null) {
			$definitions = $definitions ?? $this->getParameterDefinitions();
			return $this->getOGCDefinitions($definitions);
		}

		public function getRenderableOutputDefinitions(array $definitions = null) {
			$definitions = $definitions ?? $this->getOutputDefinitions();
			return $this->getRenderableDefinitions($definitions);

		}
		public function getOGCOutputDefinitions(array $definitions = null) {
			$definitions = $definitions ?? $this->getOutputDefinitions();
			return $this->getOGCDefinitions($definitions);
		}


		public function getRenderableDefinitions(array $definitions) {
			$definitions = $this->getFixedDefinitions($definitions);
			$definitions = $this->getDescribedDefinitions($definitions, 'getAsArray');
			return $definitions;
		}
		public function getOGCDefinitions(array $definitions) {
			$definitions = $this->getFixedDefinitions($definitions);
			$definitions = $this->getDescribedDefinitions($definitions, 'getAsOGCSchemaArray');
			return $definitions;
		}

		public function getDescribedDefinitions(array $definitions, $describerFunctionName) {
			//Get the definition restructured according to a describer functon, e.g. making a "common" array or one that functions as OGC schema.
			foreach ($definitions as $key => $value) {
				if ( is_array($value) ) {
				} else if ( is_object($value) ) {
					try {
						$value = $value->$describerFunctionName();
					} catch ( \Throwable $e ) {
						throw new \Exception(get_text('Could not describe definition %s',[$key]));
					}
				}
				$definitions[$key] = $value;
			}
			return $definitions;
		}

		public function getFixedDefinitions(array $definitions = null) {
			//Where the definitions have a name, ensure those definitions are keyed by that name, Where they are keyed, ensure they have that as name.
			$fixedDefinitions = [];
			foreach ( $definitions as $key => $value ) {
				$name = $key;
				if ( is_object($value) ) {
					try {
						$name = $value->getName();
					} catch ( \Throwable $e ) {
						throw new \Exception(get_text('Could not get name of definition %s',[$key]));
					}
				} else if ( array_key_exists('name', $value) ) {
					$name = $value['name'];
				} else {
					$value['name'] = $name;
				}
				$fixedDefinitions[$name] = $value;
			}
			return $fixedDefinitions;

		}

		protected function getRenderedExampleRunner() {
			$example = '';
			foreach ($this->getRenderableParameterDefinitions() as $key=>$paramDesc) {
				if ( !is_null($paramDesc['defaultValue']) ) {
					$example .= $this->getRenderable( 'api/ogc/processes/process-example-input-with-default.html', $paramDesc );
				} else {
					$example .= $this->getRenderable( 'api/ogc/processes/process-example-input.html', $paramDesc );
				}
			}
			return $this->getRenderable( 'api/ogc/processes/process-example.html', ['inputs' => $example]);
		}
	}

?>
