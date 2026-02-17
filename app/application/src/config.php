<?php

	$APP_DIR		=	implode(DIRECTORY_SEPARATOR,	[__DIR__,	'..']);
	$SRC_DIR		=	implode(DIRECTORY_SEPARATOR,	[$APP_DIR,	'src']);
	$CUSTOM_DIR		=	implode(DIRECTORY_SEPARATOR,    [$APP_DIR,	'..','custom']);

	$CONFIG_OVERRIDE_FILE	=	implode(DIRECTORY_SEPARATOR,	[$CUSTOM_DIR,'config','config.php']);

	if (file_exists($CONFIG_OVERRIDE_FILE)) {
		include_once($CONFIG_OVERRIDE_FILE);
		include_once($CONFIG_OVERRIDE_FILE); // In case location changed
	}

	$APP_RESOURCES_DIR 		  =	implode(DIRECTORY_SEPARATOR, [$APP_DIR, 'resources'] );
	$APP_ASSETS_DIR			  =	implode(DIRECTORY_SEPARATOR, [$APP_RESOURCES_DIR, 'assets']);
	$APP_CRON_DIR			  =	implode(DIRECTORY_SEPARATOR, [$APP_DIR, 'cron'] );

	//Variables can be overwritten.
	//To overwrite, find the "config" folder, create a copy of sample-config.php named config.php and make the desires changes there.
	$APPLICATION_WEBHOST		??=	$_SERVER['HTTP_HOST'] ?? 'localhost';
	$APPLICATION_WEBBASE		??=	'/';
	$APPLICATION_WEB_PROTOCOL	??=	'https';
	$APPLICATION_WEB_FULL_URL	??=	 ($APPLICATION_WEB_PROTOCOL ?? 'http').'://'. preg_replace('#/+#','/',$APPLICATION_WEBHOST . '/' . $APPLICATION_WEBBASE . '/');

	$CUSTOM_RESOURCES_DIR		??=	implode(DIRECTORY_SEPARATOR, [$CUSTOM_DIR, 'resources'] );
	$CUSTOM_ASSETS_DIR		??=	implode(DIRECTORY_SEPARATOR, [$CUSTOM_RESOURCES_DIR, 'assets'] );
	$CUSTOM_SRC_DIR			??=	implode(DIRECTORY_SEPARATOR, [$CUSTOM_DIR, 'src'] );
	$CUSTOM_CRON_DIR		??=	implode(DIRECTORY_SEPARATOR, [$CUSTOM_DIR, 'cron'] );


	$WORKSPACE_DIR 			??=	implode(DIRECTORY_SEPARATOR, ['','var','workspace']);
	$WORKSPACE_TASK_DIR 		??=	implode(DIRECTORY_SEPARATOR, [$WORKSPACE_DIR, 'tasks']);
	$WORKSPACE_CREDENTIALS_DIR 	??=	implode(DIRECTORY_SEPARATOR, [$WORKSPACE_DIR, 'credentials']);
	$WORKSPACE_LOCK_DIR		??=	implode(DIRECTORY_SEPARATOR, [$WORKSPACE_DIR, 'locks']);

	$CREDENTIALS_FILE_DEFAULT 	??=	'credentials-default.json';
	$LANGUAGE_DEFAULT 		??=	'EN';
	$TIMEZONE_DEFAULT		??=	'UTC';

	$KEEP_TASKS_WITH_ERROR 		??=	false;

	$SAFE_CHARACTERS 		??= 	'[a-zA-Z0-9\-_]';

	$ROUTES_NAMESPACE		??=	'Routes';
	$DEFAULT_ROUTE_NAMESPACE	??=	'Pages';
	$DEFAULT_ROUTE	 		??= 	'Pages\\CreateTaskForm';

	$FORMAT_PARAMETER_KEY		??=	'f';

	//Cron tasks should be run automatically by touching the application/cron endpoint. The CLI_TOKEN should be an alphanumeric string to authenticate a scheduler with, or null to allow any source (using null is not recommended).
	$CLI_TOKEN			??=	'';
	$CRON_LAST_RUN_FILE		??=	'last-cron';
	$CRON_TASKS			??=	['runtasks.php'];
	$TASKS_STANDOFF_IN_SECONDS	??=	10;

	$COOLDOWN_SECONDS		??=	60;

	$RENDER_PARAMETERS ??=[];
	$RENDER_PARAMETERS['baseUrl'] = $APPLICATION_WEB_FULL_URL;

	//Hooks are run upon each request, and allow for authentication checks, parameter translations, request logging, etc
	$HOOKS ??= [
		'AuthenticationToken', //Check whether authenticationtoken is required and valid
	];

	//When an action is performed, the parameters defined here cannot be overwritten by an end-user.
	$ROUTE_PARAMETERS_FIXED ??= [];
	$ROUTE_PARAMETERS_FIXED['CreateTask'] ??= [];

	$ROUTE_PARAMETERS_FIXED['CreateTask']['taskOperations']	??=	["ValidateCredentialsFile", "CreateNewProject","GenerateProject","KeepAlive","OutputServices","DeleteCredentialsFile","SetTaskComplete","Wait"];
	$ROUTE_PARAMETERS_FIXED['CreateTask']['cleanupOperations']	??=	["DeleteCredentialsFile","DeleteTaskFile"];


	//When an action is performed, these parameters are injected based on the submission of other values.
	$ROUTE_PARAMETERS_INJECTION ??= [
		'CreateTask'	=> [
			'theme'	=> [ // If a "theme" parameter is sent...
				'heat' => [ // If "theme" is "heat"
					'template'	=>	'demo_heat_stress',
					'name'		=>	'heat',
					'taskOperations'=>	["ValidateCredentialsFile", "CreateNewProject","ActivateServiceOverlays","GenerateProject","KeepAlive","OutputWebToken","DeleteCredentialsFile","SetTaskComplete","Wait"]
				],
				'green' => [ // If "theme" is "green"
					'template'	=>	'demo_3-30-300',
					'name'		=>	'green',
				],
			],
		],
	];


	//Debug flags

	//Output file and linenumbers with exceptions where they occur
	$DEBUG_EXCEPTIONS ??= false;

	//When rendering assets such as html, css, js files, include originating file and dir as comment or other meta information.
	$DEBUG_ASSETS_METADATA ??= false;

	//
	$CONTENT_HEADERS ??= [];
	$CONTENT_HEADERS['js'] ??= [
			'Content-Type' => 'application/javascript'
		];

	\Utils\Time::$DEFAULT_TIMEZONE = $TIMEZONE_DEFAULT;

	\Curl\LockingCurlTask::setLockLocation($WORKSPACE_LOCK_DIR);
	\Curl\TygronCurlTask::setCooldownTimeInSeconds($COOLDOWN_SECONDS);
?>
