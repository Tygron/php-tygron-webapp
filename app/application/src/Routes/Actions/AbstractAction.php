<?php

	namespace Routes\Actions;
	use Routes\AbstractRoute;

	abstract class AbstractAction extends AbstractRoute {

                public function getRenderableDefaultName() {
                        return [ implode(DIRECTORY_SEPARATOR,['Actions', $this->name()]), $this->name() ];
                }
	}
