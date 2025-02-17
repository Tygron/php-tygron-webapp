<?php

	namespace Assets;

	class Asset {

		protected $content;
		protected string $type = 'text';

		protected ?string $fileName = null;
		protected ?string $source = null;

		protected ?string $contentHeader = null;

		public function __construct(
				$content,
				$type = null,

				$fileName = null,
				$source = null,

				$contentHeader = null
			) {
			$this->content = $content;
			$this->type = $type ?? $this->type;

			$this->fileName = $fileName ?? $this->fileName;
			$this->source = $source ?? $this->source;
			$this->contentHeader = $contentHeader ?? $this->contentHeader;
		}

		public function __toString() {
			return $this->content;
		}

		public function getContent() {
			return $this->content;
		}
		public function getContentWithMeta() {
			$meta = $this->getContentMeta();
			return is_null($meta)
				? $this->getContent()
				: $meta.$this->getContent();
		}

		public function getFileName() {
			return $this->fileName;
		}
		public function getSource() {
			return $this->source;
		}
		public function getContentHeader() {
			return $this->getContentHeader;
		}

		public function isText() {
			return in_array($this->type,['text', 'html', 'css', 'js']);
		}

		public function getContentMeta( bool $whitespace = true ) {
			if ( !$this->isText() ) {
				return null;
			}

			$meta = null;
			if ( !empty($this->getFileName()) && !empty($this->getSource()) ) {
				$meta = get_text('%s from %s', [$this->getFileName(), $this->getSource()]);
			} else if ( !empty($this->getFileName()) ) {
				$meta = $this->getFileName();
			} else if ( !empty($this->getSource()) ) {
				$meta = get_text('from %s', [$this->getSource()]);
			}

			if ( is_null($meta) ) {
				return null;
			}

			$whitespace = $whitespace ? PHP_EOL.PHP_EOL : '';
			switch ($type) {
				case 'html': return '<!--'.$meta.'-->'.$whitespace;
				case 'css': return '\/\*'.$meta.'\*\/'.$whitespace;
				case 'js': return '\/\*'.$meta.'\*\/'.$whitespace;
				default: return null;
			}
		}
	}
