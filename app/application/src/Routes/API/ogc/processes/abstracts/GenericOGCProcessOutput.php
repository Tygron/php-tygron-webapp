<?php

	namespace Routes\API\OGC\Processes\Abstracts;

	class GenericOGCProcessOutput {

		private ?string $name = null;
		private ?string $title = null;
		private ?string $description = null;
		private ?string $type = null;
		private ?array $schema = [
				'type' => 'string',
			];

		public static function create( array $definition ) {
			return new static($definition);
		}

		public function __construct( array $definition ) {

			foreach ( $definition as $key => $value ) {
				$this->setProperty($key, $value);
			}
		}

		public function configureWms() {
			$this->type = 'wms';
			$this->schema = [
					'type'=>'string',
					'format'=>'url',
				];
			return $this;
		}

		protected function setProperty($key, $value) {
			switch($key) {
				case 'name':			return $this->setName($value);
				case 'title':			return $this->setTitle($value);
				case 'description':		return $this->setDescription($value);
				case 'type':			return $this->setType($value);
				case 'schema':			return $this->setSchema($value);
			}
			throw new \Exception(get_text('Unknown parameter: %s', [$key]));
		}



		public function setName($value) {
			$this->name = $value;
			return $this;
		}
		public function setTitle($value) {
			$this->title = $value;
			return $this;
		}
		public function setDescription($value) {
			$this->description = $value;
			return $this;
		}
		public function setType($value) {
			switch ($value) {
				case 'wms': 	return $this->configureWms();
				default:	break;
			}
			$this->type = $value;
			return $this;
		}
		public function setSchema(array|string $value) {
			$this->schema = $value;
			return $this;
		}

		public function getName() {
			return $this->name;
		}
		public function getTitle() {
			return $this->title;
		}
		public function getDescription() {
			return $this->description;
		}
		public function getType() {
			return $this->type;
		}
		public function getSchema() {
			return $this->schema;
		}

		public function getAsArray() {
			return [
				'name'=>$this->getName(),
				'title'=>$this->getTitle(),
				'description'=>$this->getDescription(),

				'type'=>$this->getType(),

				'schema'=>$this->getSchema(),
			];
		}

		public function getAsOGCSchemaArray() {
			$parameterDescription = [];
			$parameterDescription['schema'] = $this->getSchema();
			return $parameterDescription;
		}

	}
