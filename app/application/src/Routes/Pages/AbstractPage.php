<?php

	namespace Routes\Pages;
	use Routes\AbstractRoute;

	abstract class AbstractPage extends AbstractRoute {

		public function getRenderableDefaultName() {
			return [ implode(DIRECTORY_SEPARATOR,['Pages', $this->name()]), $this->name() ];
		}
	}

?>
