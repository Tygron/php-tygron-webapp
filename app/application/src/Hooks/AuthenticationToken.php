<?php

	namespace Hooks;

	class AuthenticationToken extends AbstractHook {

		public function run() {
			global $AUTHENTICATION_TOKEN;
			global $CONTEXT_BY_AUTHENTICATION_TOKEN;
			global $WORKSPACE_CONTEXT;

			global $RENDER_PARAMETERS;

			$authenticationToken = get_clean_user_input('authenticationToken');

			if ( !empty($authenticationToken) ) {
				setcookie('authenticationToken', $authenticationToken);
			}

			$acceptableTokens = is_array($AUTHENTICATION_TOKEN) ? $AUTHENTICATION_TOKEN : [$AUTHENTICATION_TOKEN];

			if ( !empty($AUTHENTICATION_TOKEN) && !in_array($authenticationToken, $acceptableTokens) ) {
				$asset = \Assets\AssetReader::getAsset(
					'Login.html',
					'html'
				);
				$renderableAsset = new \Rendering\RenderableAsset();
				$renderableAsset->setAsset($asset);
				$renderableAsset->setData($RENDER_PARAMETERS);
				$renderableAsset->output();
				exit();
			}
			if ( is_null($WORKSPACE_CONTEXT) && array_key_exists($authenticationToken, $CONTEXT_BY_AUTHENTICATION_TOKEN ?? []) ) {
				$WORKSPACE_CONTEXT = $CONTEXT_BY_AUTHENTICATION_TOKEN[$authenticationToken];
			}
		}
	}

?>
