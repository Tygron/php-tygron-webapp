<?php

	namespace Routes\API\OGC\Processes;

	use Routes\API\OGC\Processes\Abstracts\AbstractProcess as AbstractProcess;
	use Routes\API\OGC\Processes\Abstracts\GenericOGCProcessParameter;
	use Routes\API\OGC\Processes\Abstracts\GenericOGCProcessOutput;

	class ExistingProjectTask extends AbstractProcess {

		public function run( array $parameters = [] ) {
			return parent::run($parameters);
		}

		public function getDescription() {
			return 'Run a calculation of an existing Project. This will start an existing Project, or optionally join a running one. Note that when joining a Session the state of the Session may be altered by this process.';
		}

		public function getParameterDefinitions() {
			$definitions = [
				GenericOGCProcessParameter::create([
						'name' => 'username',
						'title' => 'Tygron Platform username',
						'description' => 'Tygron Platform account username',
						'type' => 'string',
						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'',
					]),
				GenericOGCProcessParameter::create([
						'name' => 'password',
						'title' => 'Tygron Platform password',
						'description' => 'Tygron Platform account password',
						'type' => 'string',
						'secret' => true,
						'htmlExampleType'=>'password',
						'htmlExampleValue'=>'',

					]),
				GenericOGCProcessParameter::create([
						'name' => 'mfa',
						'title' => 'Multi-factor authentication token',
						'description'=>'Tygron Platform account multi-factor-authentication code',
						'type'=>'string',

						'defaultValue'=>'SMS',

						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'',

					]),
				GenericOGCProcessParameter::create([
						'name' => 'platform',
						'title' => 'Tygron Platform Server',
						'description'=>'The Tygron Platform server to run the calculation on.',
						'type'=>'string',

						'defaultValue'=>'engine',

						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'engine',

					]),
				GenericOGCProcessParameter::create([
						'name' => 'projectName',
						'title' => 'Project Name',
						'description'=>'Name of the Project to run',
						'type'=>'string',

						'defaultValue' => 'demo_heat_stress',

						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'demo_heat_stress',
					]),

				GenericOGCProcessParameter::create([
						'name' => 'joinPreferred',
						'title' => 'Join if already running',
						'description'=>'If a session of this Project is already running, join it rather than starting a session',
						'type'=>'boolean',

						'optional'=> true,
						'defaultValue' => 'false',

						'htmlExampleType'=>'checkbox',
						'htmlExampleValue'=>'true',
					]),
				GenericOGCProcessParameter::create([
						'name' => 'joinIfNeeded',
						'title' => 'Join if needed',
						'description'=>'Prefer starting a session, but if a session cannot be started, join it instead',
						'type'=>'boolean',

						'optional'=> true,
						'defaultValue' => 'false',

						'htmlExampleType'=>'checkbox',
						'htmlExampleValue'=>'true',
					]),
				GenericOGCProcessParameter::create([
						'name' => 'stopTestRun',
						'title' => 'Stop the testrun',
						'description'=>'Stop the Testrun if a Session is joined where a Session is running',
						'type'=>'boolean',

						'optional'=> true,
						'defaultValue' => 'false',

						'htmlExampleType'=>'checkbox',
						'htmlExampleValue'=>'true',
					]),

				GenericOGCProcessParameter::create([
						'name' => 'measure',
						'title' => 'Measure',
						'description'=>'Measure (name or ID) to activate in the Project for calculation',
						'type'=>'string',

						'optional'=> true,
						'defaultValue' => '',

						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'',
					]),
			];
			return $definitions;
		}

		public function getOutputDefinitions() {
			$outputs = [
				GenericOGCProcessOutput::create([
						'name' => 'wms',
						'title' => 'WMS link',
						'description' => 'WMS link for all calculated results',
						'type' => 'wms',
					]),
			];
			return $outputs;
		}

		public function runPost( array $parameters = [] ) {
			try {
				$task = \Tasks\TaskGenerator::generate($parameters);
				$task->validate();
			} catch ( \Throwable $e ) {
				return $this->returnError(null, $e);
			}

			$task->save();

			return $this->returnSuccess(['jobId' => $task->getTaskName()]);
		}

		protected function getMiscInputs() {
			global $_INPUTS;
			$inputs = [];
			if ( array_key_exists('username', $_INPUTS) ) {
				$inputs['username'] = get_clean_user_input('username', '[\.\-_@+a-zA-Z0-9]');
			}
			if ( array_key_exists('password', $_INPUTS) ) {
				$inputs['password'] = get_clean_user_input('password', '[\{\}\[\]\s\+\=\-\_\,\.\'\"\;\\\|\?\!\#\$\%\€\^|&|*a-zA-Z0-9]');
			}
			if ( array_key_exists('mfa', $_INPUTS) ) {
				$inputs['mfa'] = get_clean_user_input('mfa', '[0-9]');
			}
			if ( array_key_exists('measure', $_INPUTS) ) {
				$inputs['measure'] = get_clean_user_input('measure', '[\(\)\{\}\[\]\s\+\=\-\_\,\.\'\"\;\\\|\?\!\#\$\%\€\^|&|*a-zA-Z0-9]');
			}
			return $inputs;
		}

		protected function getParameterSetName() {
			return 'ExistingProjectTask';
		}

		protected function getAllowedExtraParameters() {
			return \Tasks\Task::getAllowedParameters();
		}

		protected function mergeParametersForRoute (array $parameters ) {
			return $this->mergeParametersForTask( $parameters );
		}
		protected function mergeParametersForTask( array $parameters ) {
			$allowed = $this->getAllowedExtraParameters();
			$flatMerge = []
					+ \Tasks\TaskGenerator::normalizeParameters($this->getInjectedParameters(), $allowed)
					+ \Tasks\TaskGenerator::normalizeParameters($this->getFixedParameters(), $allowed)
					+ \Tasks\TaskGenerator::normalizeParameters($parameters, $allowed)
					+ \Tasks\TaskGenerator::normalizeParameters($this->getDefaultParameters(), $allowed)
				;
			return $flatMerge;
		}

	}
?>
