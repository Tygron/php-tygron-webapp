<?php

	namespace Hooks;

	class AuthenticationToken extends AbstractHook {

		public function run() {
			global $AUTHENTICATION_TOKEN;
			global $CONFIG_PARAMETERS;

			$authenticationToken = get_clean_user_input('authenticationToken');

			if ( !empty($authenticationToken) ) {
				setcookie('authenticationToken', $authenticationToken);
			}

			$acceptableTokens = is_array($AUTHENTICATION_TOKEN) ? $AUTHENTICATION_TOKEN : [$AUTHENTICATION_TOKEN];

			if ( !empty($AUTHENTICATION_TOKEN) && !in_array($authenticationToken, $acceptableTokens) ) {
				$asset = \Assets\AssetReader::getAsset(
					'login.html',
					'html'
				);
				$renderableAsset = new \Rendering\RenderableAsset();
				$renderableAsset->setAsset($asset);
				$renderableAsset->setData($CONFIG_PARAMETERS);
				$renderableAsset->output();
				exit();
			}
		}
	}

?>
