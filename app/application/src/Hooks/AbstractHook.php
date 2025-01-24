<?php

	namespace Hooks;

	abstract class AbstractHook {

		public function name() {
			return substr(strrchr(get_class($this),'\\'),1);
		}

		public abstract function run();

	}
