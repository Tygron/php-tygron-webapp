<?php

	namespace Routes\API\OGC\Processes\Abstracts;

	class GenericOGCProcessParameter {

		private ?string $name = null;
		private ?string $title = null;
		private ?string $description = null;
		private ?string $type = null;

		private bool $secret = false;
		private bool $optional = false;

		private ?array $schema = null;
		private $defaultValue = null;

		private ?string $htmlExampleType = null;
		private ?string $htmlExampleValue = null;

		public static function create( array $definition ) {
			return new static($definition);
		}

		public function __construct( array $definition ) {

			foreach ( $definition as $key => $value ) {
				$this->setProperty($key, $value);
			}
		}

		protected function setProperty($key, $value) {
			switch($key) {
				case 'name':			return $this->setName($value);
				case 'title':			return $this->setTitle($value);
				case 'description':		return $this->setDescription($value);

				case 'type':			return $this->setType($value);
				case 'secret':			return $this->setSecret($value);
				case 'optional':		return $this->setOptional($value);

				case 'schema':			return $this->setSchema($value);
				case 'defaultValue':		return $this->setDefaultValue($value);
				case 'htmlExampleType':		return $this->setHtmlExampleType($value);
				case 'htmlExampleValue':	return $this->setHtmlExampleValue($value);
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
			$this->type = $value;
			return $this;
		}
		public function setSecret($value) {
			$this->secret = $value;
			return $this;
		}
		public function setOptional($value) {
			$this->optional = $value;
			return $this;
		}

		public function setSchema(array|string $value) {
			$this->schema = $value;
			return $this;
		}
		public function setDefaultValue($value) {
			$this->defaultValue = $value;
			return $this;
		}

		public function setHtmlExampleType($value) {
			$this->htmlExampleType = $value;
			return $this;
		}
		public function setHtmlExampleValue($value) {
			$this->htmlExampleValue = $value;
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
		public function getSecret() {
			return $this->secret;
		}
		public function getOptional() {
			return $this->optional;
		}

		public function getSchema() {
			//Inject default value
			return $this->schema;
		}
		public function getDefaultValue() {
			//Property, or read from schema
			return $this->defaultValue;
		}

		public function getHtmlExampleType() {
			return $this->htmlExampleType;
		}
		public function getHtmlExampleValue() {
			return $this->htmlExampleValue;
		}



		public function getAsArray() {
			return [
				'name'=>$this->getName(),
				'title'=>$this->getTitle(),
				'description'=>$this->getDescription(),

				'type'=>$this->getType(),
				'secret'=>$this->getSecret(),
				'optional'=>$this->getOptional(),

				'schema'=>$this->getSchema(),
				'defaultValue'=>$this->getDefaultValue(),

				'htmlExampleType'=>$this->getHtmlExampleType(),
				'htmlExampleValue'=>$this->getHtmlExampleValue(),
			];
		}

		public function getAsOGCSchemaArray() {
			$parameterDescription = [];
			$parameterDescription['title'] = $this->getTitle();
			$parameterDescription['description'] = $this->getDescription();

			$parameterDescription['schema'] = $this->getSchema();

			return $parameterDescription;
		}

	}
