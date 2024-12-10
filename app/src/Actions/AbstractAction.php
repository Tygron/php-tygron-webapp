<?php

	namespace Actions;

	abstract class AbstractAction {

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public abstract function run( array $parameters = null );
	}
