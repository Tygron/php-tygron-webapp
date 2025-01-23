
<?php

	$action = get_clean_user_input('action');

	if ( empty($action) && !isset($DEFAULT_ACTION)) {
		if (!isset($DEFAULT_ACTION)) {
			throw new Exception(get_text('Required parameter missing: action'));
		}
	}
	$action = $action ?? $DEFAULT_ACTION;

	$className = '\\Actions\\'.$action;

	if ( !class_exists($className) ) {
		throw new Exception(get_text('Unknown action: %s',[$action]));
	}

	$actionObject = null;
	try {
		$actionObject = new $className();
	} catch (\Throwable $e) {
		throw new Exception( get_text('Could not load action %s',[$action]), previous:$e);
	}

	echo $actionObject->startAction($_INPUTS_CLEANED);


?>
