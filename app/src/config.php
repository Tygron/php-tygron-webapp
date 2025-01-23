<?php

	$CONFIG_OVERRIDE_FILE = implode(DIRECTORY_SEPARATOR,[__DIR__,'..','config','config.php']);

	if (file_exists($CONFIG_OVERRIDE_FILE)) {
		include_once($CONFIG_OVERRIDE_FILE);
	}

	//Some of the variables below can be overwritten, others should not.
	//To overwrite, find the "config" folder, create a copy of sample-config.php named config.php and make the desires changes there.

	$WORKSPACE_DIR 			??=	implode(DIRECTORY_SEPARATOR, ['','var','workspace']);
	$WORKSPACE_TASK_DIR 		??=	implode(DIRECTORY_SEPARATOR, [$WORKSPACE_DIR, 'tasks']);
	$WORKSPACE_CREDENTIALS_DIR 	??=	implode(DIRECTORY_SEPARATOR, [$WORKSPACE_DIR, 'credentials']);

	$HTML_DIR 			??=	implode(DIRECTORY_SEPARATOR, [__DIR__, '..','html']);

	$CREDENTIALS_FILE_DEFAULT 	??=	'credentials-default.json';
	$LANGUAGE_DEFAULT 		??=	'EN';

	$KEEP_TASKS_WITH_ERROR 		??=	false;

	$SAFE_CHARACTERS 		??= 	'[a-zA-Z0-9\-_]';
	$DEFAULT_ACTION 		??= 	'CreateTaskForm';


	//When an action is performed, the parameters defined here cannot be overwritten by an end-user.
	$ACTION_PARAMETERS_FIXED ??= [];
	$ACTION_PARAMETERS_FIXED['CreateTask'] ??= [
			'name'			=>	'task',
			'template'		=>	'demo_heat_stress',
			'taskOperations'	=>	["ValidateCredentialsFile", "CreateNewProject","GenerateProject","KeepAlive","OutputWebViewer3DHtml"],
			'cleanupOperations'	=>	["DeleteCredentialsFile","DeleteTaskFile"],
		];
?>
