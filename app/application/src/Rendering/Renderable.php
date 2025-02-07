<?php

	namespace Rendering;

	class Renderable {

		private ?string $template = null;
		private array $data = [];

		private ?string $rendered = null;

		public function __construct() {
		}

		public function __toString() {
			return $this->getRendered();
		}

		public function setTemplate( string $template ) {
			$this->template = $template;
		}

		public function setData( array $data ) {
			$this->data = $data;
		}

		public function getTemplate() {
			return $this->template;
		}

		public function getData() {
			return $this->data;
		}

		public function getRendered() {
			if (!$this->isRendered()) {
				$this->render();
			}
			return $this->rendered;
		}

		public function validate() {
			return  is_string($this->template)
				&& !empty($this->template)
				&& is_array($this->data);
		}

		public function isRendered() {
			return !is_null($this->rendered);
		}

		public function render() {
			if (!$this->validate()) {
				throw new Exception('Renderable not ready');
			}
			$rendered = Renderer::getRendered( $this->template, $this->data);
			$this->rendered = $rendered;
			return $this->rendered;
		}

	}
