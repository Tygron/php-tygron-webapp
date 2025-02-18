<?php

	namespace Assets;

	class Asset {

		protected $content;
		protected string $mimeType = 'text/plain';

		protected ?string $fileName = null;
		protected ?string $source = null;

		protected string $type = 'text';

		public function __construct(
				$content,
				$mimeType = null,

				$fileName = null,
				$source = null,
				$type = null,

			) {
			$this->content = $content;
			$this->mimeType = $mimeType;

			$this->fileName = $fileName ?? $this->fileName;
			$this->source = $source ?? $this->source;

			$this->type = $type ?? $this->type;
		}

		public function __toString() {
			return $this->content;
		}

		public function getContent(bool $includeMeta = false) {
			return $includeMeta ? $this->getContentWithMeta() : $this->content;
		}
		public function getContentWithMeta() {
			$meta = $this->getContentMeta();
			return is_null($meta)
				? $this->getContent()
				: $meta.$this->getContent();
		}

		public function getType() {
			return $this->type;
		}

		public function getMimeType() {
			switch($this->type) {
				case 'html'	:	return	'text/html';
				case 'css'	:	return	'text/css';
				case 'js'	:	return	'application/javascript';
				default	:	return 	$this->mimeType;
			}
		}
		public function getFileName() {
			return $this->fileName;
		}
		public function getSource() {
			return $this->source;
		}

		public function isText() {
			$mime = $this->getMimeType();
			return (str_contains($mime,'text') || $mime == 'application/javascript');
		}

		public function isImage() {
			return in_array($this->type,['image','images','jpg','jpeg','gif','png']);
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
			switch ($this->mimeType) {
				case 'text/html'		:	return '<!--'.$meta.'-->'.$whitespace;
				case 'text/css'			:	return '/*'.$meta.'*/'.$whitespace;
				case 'application/javascript'	:	return '/*'.$meta.'*/'.$whitespace;
				default				:	return null;
			}
		}
	}
