<?php

	namespace Routes\API\OGC\Processes\Abstracts;

	abstract class AbstractProcess extends \Routes\API\AbstractAPIEndpoint {

		public const CONTROL_MODE_ASYNC = 'async-execute';
		public const CONTROL_MODE_SYNC = 'sync-execute';

		public const CONTROL_MODES = [self::CONTROL_MODE_ASYNC, self::CONTROL_MODE_SYNC];

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
			return $this->createJob($parameters);
		}

		protected function getPreferredControlOption(array $parameters = []) {
			$preferedMode = null;
			if ( is_array($parameters)) {
				$preferedMode = $parameters['syncMode'] ?? null;
			}
			$preferedMode ??= get_clean_user_input('syncMode') ?? get_clean_header_input('Prefer');
			return $preferedMode;
		}

		protected function createJob(array $parameters = []) {
			$modes = $this->getJobControlOptions();
			$mode = self::CONTROL_MODE_SYNC;
			if ( is_array($modes) ) {
				if ( count($modes) > 0) {
					$mode = $modes[0];
				}
			} else if ( in_array($modes, self::CONTROL_MODES) ) {
				$mode = $modes;
			}

			$preferedMode = $this->getPreferredControlOption();
			if ( in_array($preferedMode, $modes) ) {
				$mode = $preferedMode;
			}
			switch( $mode ) {
				case self::CONTROL_MODE_SYNC:
				case self::CONTROL_MODE_SYNC.'-execute':
					return $this->createJobSync($parameters);
				case self::CONTROL_MODE_ASYNC:
				case self::CONTROL_MODE_ASYNC.'-execute':
					return $this->createJobAsync($parameters);
				default:
					throw new \Exception( get_text('Cannot run in mode: %s', [$syncMode]) );
			}
		}

		protected function createJobSync(array $parameters = []) {
			$parameters['syncMode'] = \Tasks\Task::SYNC_MODE_SYNC;

			try {
				$task = \Tasks\TaskGenerator::generate($parameters);
				$task->validate();
				$task->save();

				$taskRunner = new \Tasks\Runners\SyncModeTaskRunner();
                                $taskRunner->setSyncMode(\Tasks\Task::SYNC_MODE_SYNC, false);
                                $taskRunner->setTaskFileName($task->getTaskFileName());
                                $result = $taskRunner->run();
                                $result = $taskRunner->getLogs();

			} catch ( \Throwable $e ) {
				return $this->returnError(null,$e);
			}

			$task = \Tasks\Task::load($task->getTaskFileName());
			return $this->returnSuccess([
					'jobId' => $task->getTaskName(),
					'results' => $task->getOutput(),
					'logs' => $task->getLog(),
				]);

		}

		protected function createJobAsync(array $parameters = []) {
			$parameters['syncMode'] = \Tasks\Task::SYNC_MODE_ASYNC;

			try {
				$task = \Tasks\TaskGenerator::generate($parameters);
				$task->validate();
				$task->save();

			} catch ( \Throwable $e ) {
				return $this->returnError(null,$e);
			}

			return $this->returnSuccess(['jobId' => $task->getTaskName()]);

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
			return self::CONTROL_MODES;
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

		public function getRenderableControlOptions() {
			$modes = $this->getJobControlOptions();
			$modes = is_array($modes) ? $modes : [$modes];
			return array_map(function($a){return ['controlOption'=>$a];}, $modes);
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
			$controlOptions = $this->getRenderableControlOptions();
			foreach ($this->getRenderableParameterDefinitions() as $key=>$paramDesc) {
				if ( !is_null($paramDesc['defaultValue']) ) {
					$example .= $this->getRenderable( 'api/ogc/processes/process-example-input-with-default.html', $paramDesc );
				} else {
					$example .= $this->getRenderable( 'api/ogc/processes/process-example-input.html', $paramDesc );
				}
			}
			return $this->getRenderable( 'api/ogc/processes/process-example.html', [
					'inputs' => $example,
					'controlOption' => $this->getPreferredControlOption() ?? '(unset)',
					'controlOptions' => $controlOptions,
				]);
		}
	}

?>
