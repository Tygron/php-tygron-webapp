
<?php

	$action = get_clean_user_input('action');
	$className = '\\Actions\\'.$action;

	if ( empty($action) ) {
		throw new Exception(get_text('Required parameter missing: action'));
	}

	if ( !class_exists($className) ) {
		throw new Exception(get_text('Unknown action: %s',[$action]));
	}

	$actionObject = null;
	try {
		$actionObject = new $className();
	} catch (\Throwable $e) {
		throw new Exception( get_text('Could not load action %s',[$action]), previous:$e);
	}

	echo $actionObject->run();


	// Get provided parameters
	// Validate provided parameters
	// Create task file

?>
