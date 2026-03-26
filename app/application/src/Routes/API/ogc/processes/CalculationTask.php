<?php

	namespace Routes\API\OGC\Processes;

	use Routes\API\OGC\Processes\Abstracts\AbstractProcess as AbstractProcess;
	use Routes\API\OGC\Processes\Abstracts\GenericOGCProcessParameter;
	use Routes\API\OGC\Processes\Abstracts\GenericOGCProcessOutput;

	class CalculationTask extends AbstractProcess {

		public function run( array $parameters = [] ) {
			return parent::run($parameters);
		}

		public function getDescription() {
			return 'Run a calculation, based on a provided template, on an arbitrary location';
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
						'name' => 'template',
						'title' => 'Template project',
						'description'=>'Tygron Platform template project',
						'type'=>'string',

						'defaultValue' => 'demo_heat_stress',

						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'demo_heat_stress',
					]),
				GenericOGCProcessParameter::create([
						'name' => 'locationX',
						'title' => 'Location X-coordinate',
						'description'=>'X-coordinate of center of calculation, CRS 4326',
						'type'=>'number',

						'htmlExampleType'=>'number',
						'htmlExampleValue'=>'4.31226',
						//'htmlExampleValue'=>'4.312262535095216',
						]),
				GenericOGCProcessParameter::create([
						'name' => 'locationY',
						'title' => 'Location Y-coordinate',
						'description'=>'Y-coordinate of center of calculation, CRS 4326',
						'type'=>'number',

						'htmlExampleType'=>'number',
						'htmlExampleValue'=>'52.08027',
						//'htmlExampleValue'=>'52.0802794772862',
					]),
				GenericOGCProcessParameter::create([
						'name' => 'size',
						'title' => 'Size of the extent',
						'description'=>'Size in meters of calculation extent',
						'type'=>'integer',

						'defaultValue' => '1000',

						'htmlExampleType'=>'number',
						'htmlExampleValue'=>'500',
					]),
				GenericOGCProcessParameter::create([
						'name' => 'useDefaultTrees',
						'title' => 'Use default generated trees',
						'description'=>'Whether or not to use default generated trees when creating a new project',
						'type'=>'boolean',

						'optional'=> true,
						'defaultValue' => 'false',

						'htmlExampleType'=>'checkbox',
						'htmlExampleValue'=>'true',
					]),
				GenericOGCProcessParameter::create([
						'name' => 'treesImportWFSUrl',
						'title' => 'Tree locations WFS',
						'description'=>'Accessible WFS service, indicating locations of trees or forests.',
						'type'=>'wfs',

						'optional'=> true,
						'defaultValue' => null,

						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'',
					]),
				GenericOGCProcessParameter::create([
						'name' => 'treesImportWFSLayer',
						'title' => 'Tree locations WFS layer',
						'description'=>'Accessible WFS service\'s layer, indicating locations of trees or forests.',
						'type'=>'string',

						'optional'=> true,
						'defaultValue' => null,

						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'',
					]),
					GenericOGCProcessParameter::create([
						'name' => 'crs',
						'title' => 'Coordinate Reference System',
						'description'=>'CRS overwrite for Project creation',
						'type'=>'string',

						'optional'=> true,

						'defaultValue'=>'4326',

						'htmlExampleType'=>'text',
						'htmlExampleValue'=>'4326',
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
			if ( array_key_exists('locationX', $_INPUTS) ) {
				$inputs['locationX'] = get_clean_user_input('locationX', '[\.\-0-9]');
			}
			if ( array_key_exists('locationY', $_INPUTS) ) {
				$inputs['locationY'] = get_clean_user_input('locationY', '[\.\-0-9]');
			}
			if ( array_key_exists('location', $_INPUTS) ) {
				$inputs['location'] = get_clean_user_input('location', '[\[\]\,\.0-9]');
				$inputs['location'] = json_decode($inputs['location'], true);
			}
			if ( array_key_exists('areaOfInterest', $_INPUTS) ) {
				$inputs['areaOfInterest'] = get_clean_user_input('areaOfInterest', '[\{\}\[\]\s\-\_\,\.\'\"\:a-zA-Z0-9]');
				$inputs['areaOfInterest'] = json_decode($inputs['areaOfInterest'], true);
			}
			if ( array_key_exists('treesImportWFSUrl', $_INPUTS) ) {
				$inputs['treesImportWFSUrl'] = get_clean_user_input('treesImportWFSUrl',
					'[\s\-\_\,\.\'\"\:\/\&\=\+\-\%\?a-zA-Z0-9]');
			}
			if ( array_key_exists('treesImportWFSLayer', $_INPUTS) ) {
				$inputs['treesImportWFSLayer'] = get_clean_user_input('treesImportWFSLayer',
					'[\s\-\_\,\.\'\"\:\&\=\+\-\%\?a-zA-Z0-9]');
			}
			return $inputs;
		}

		protected function getParameterSetName() {
			return 'CreateTask';
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
