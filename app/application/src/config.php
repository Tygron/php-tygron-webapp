<?php


	$APP_DIR		=	implode(DIRECTORY_SEPARATOR,	[__DIR__,	'..']);
	$CUSTOM_DIR		=	implode(DIRECTORY_SEPARATOR,    [$APP_DIR,	'..','custom']);

	$CONFIG_OVERRIDE_FILE	=	implode(DIRECTORY_SEPARATOR,	[$CUSTOM_DIR,'config','config.php']);

	if (file_exists($CONFIG_OVERRIDE_FILE)) {
		include_once($CONFIG_OVERRIDE_FILE);
		include_once($CONFIG_OVERRIDE_FILE); // In case location changed
	}
	$APP_HTML_DIR 		=	implode(DIRECTORY_SEPARATOR,	[$APP_DIR, 	'resources', 'html'] );


	//Some of the variables below can be overwritten, others should not.
	//To overwrite, find the "config" folder, create a copy of sample-config.php named config.php and make the desires changes there.

	$CUSTOM_HTML_DIR		??=	implode(DIRECTORY_SEPARATOR, [$CUSTOM_DIR, 'html'] );

	$WORKSPACE_DIR 			??=	implode(DIRECTORY_SEPARATOR, ['','var','workspace']);
	$WORKSPACE_TASK_DIR 		??=	implode(DIRECTORY_SEPARATOR, [$WORKSPACE_DIR, 'tasks']);
	$WORKSPACE_CREDENTIALS_DIR 	??=	implode(DIRECTORY_SEPARATOR, [$WORKSPACE_DIR, 'credentials']);


	$CREDENTIALS_FILE_DEFAULT 	??=	'credentials-default.json';
	$LANGUAGE_DEFAULT 		??=	'EN';
	$TIMEZONE_DEFAULT		??=	'UTC';

	$KEEP_TASKS_WITH_ERROR 		??=	false;

	$SAFE_CHARACTERS 		??= 	'[a-zA-Z0-9\-_]';
	$DEFAULT_ACTION 		??= 	'CreateTaskForm';


	//Hooks are run upon each request, and allow for authentication checks, parameter translations, request logging, etc
	$HOOKS ??= [
		'AuthenticationToken', //Check whether authenticationtoken is required and valid
	];

	//When an action is performed, the parameters defined here cannot be overwritten by an end-user.
	$ACTION_PARAMETERS_FIXED ??= [];
	$ACTION_PARAMETERS_FIXED['CreateTask'] ??= [];

	$ACTION_PARAMETERS_FIXED['CreateTask']['taskOperations']	??=	["ValidateCredentialsFile", "CreateNewProject","GenerateProject","KeepAlive","OutputWebViewer3DHtml","SetTaskComplete","Wait"];
	$ACTION_PARAMETERS_FIXED['CreateTask']['cleanupOperations']	??=	["DeleteCredentialsFile","DeleteTaskFile"];


	//When an action is performed, these parameters are injected based on the submission of other values.
	$ACTION_PARAMETERS_INJECTION ??= [
		'CreateTask'	=> [
			'theme'	=> [ // If a "theme" parameter is sent...
				'heat' => [ // If "theme" is "heat"
					'template'	=>	'demo_heat_stress',
					'name'		=>	'heat',
				],
				'green' => [ // If "theme" is "green"
					'template'	=>	'demo_3-30-300',
					'name'		=>	'green',
				],
			],
		],
	];

	\Utils\Time::$DEFAULT_TIMEZONE = $TIMEZONE_DEFAULT;
?>
