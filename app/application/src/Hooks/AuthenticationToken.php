<?php

	namespace Hooks;

	class AuthenticationToken extends AbstractHook {

		public function run() {
			global $AUTHENTICATION_TOKEN;

			$authenticationToken = get_clean_user_input('authenticationToken');

			if ( !empty($authenticationToken) ) {
				setcookie('authenticationToken', $authenticationToken);
			}

			if ( !empty($AUTHENTICATION_TOKEN && $authenticationToken != $AUTHENTICATION_TOKEN)) {
				$asset = \Assets\AssetReader::getAsset(
					'login.html',
					'html'
				);
				$renderableAsset = new \Rendering\RenderableAsset();
				$renderableAsset->setAsset($asset);
				$renderableAsset->output();
				exit();
			}
		}
	}

?>
