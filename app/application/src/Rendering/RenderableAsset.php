<?php

	namespace Rendering;

	class RenderableAsset extends Renderable{

		private ?\Assets\Asset $asset;
		private array $data = [];

		private ?string $rendered = null;

		public function __construct() {
		}

		public function __toString() {
			return $this->getRendered();
		}

		public function setAsset( \Assets\Asset $asset ) {
			$this->asset = $asset;
		}

		public function setData( array $data ) {
			$this->data = $data;
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

		public function validate(bool $thrown = true) {
			$ready = !is_null($this->asset)
				&& is_array($this->data);
			if ($ready || !$thrown) {
				return $ready;
			}
			throw new \Exception('Renderable not ready');
		}

		public function isRendered() {
			return !is_null($this->rendered);
		}

		public function render() {
			global $DEBUG_ASSETS_METADATA;
			$this->validate();
			if ($this->asset->isText()) {
				$this->rendered = Renderer::renderFromTemplate( $this->asset->getContent($DEBUG_ASSETS_METADATA), $this->getData() );
			} else {
				$this->rendered = $this->asset->getContent($DEBUG_ASSETS_METADATA);
			}
			return $this->rendered;
		}

		public function output() {
			$this->validate();
			if ( $this->asset->getMimeType() ) {
				header('Content-Type: '.$this->asset->getMimeType());
			}
			foreach( ($CONTENT_HEADERS[$this->asset->getType()] ?? []) as $key => $value ) {
				header( $key.': '.$value );
			}
			echo $this->getRendered();
		}

	}
