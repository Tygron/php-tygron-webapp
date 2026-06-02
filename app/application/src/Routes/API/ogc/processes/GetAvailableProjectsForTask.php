<?php

	namespace Routes\API\OGC\Processes;

	use Routes\API\OGC\Processes\Abstracts\AbstractProcess as AbstractProcess;
	use Routes\API\OGC\Processes\Abstracts\GenericOGCProcessParameter;
	use Routes\API\OGC\Processes\Abstracts\GenericOGCProcessOutput;

	class GetAvailableProjectsForTask extends AbstractProcess {

		public function run( array $parameters = [] ) {
			return parent::run($parameters);
		}

		public function getDescription() {
			return 'Get a list of Projects which can be used to start or join an existing Project with.';
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
						'name' => 'includeUniversal',
						'title' => 'Include Universal Projects',
						'description'=>'Include Projects made available to all domains, ',
						'type'=>'boolean',

						'optional'=> true,
						'defaultValue' => 'false',

						'htmlExampleType'=>'checkbox',
						'htmlExampleValue'=>'true',
					]),
			];
			return $definitions;
		}

		public function getOutputDefinitions() {
			$outputs = [
				GenericOGCProcessOutput::create([
						'name' => 'projectNames',
						'title' => 'Names of available projects',
						'description' => 'A list of all available project names. This does not include meta-data on the contents of the Project.',
						'type' => 'array',
					]),

				GenericOGCProcessOutput::create([
						'name' => 'projectData',
						'title' => 'Data of available projects',
						'description' => 'A map of ProjectData related to project names.',
						'type' => 'array',
					]),
			];
			return $outputs;
		}

		public function getJobControlOptions() {
			return [self::CONTROL_MODE_SYNC, self::CONTROL_MODE_ASYNC];
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
			return 'GetAvailableProjectsForTask';
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
