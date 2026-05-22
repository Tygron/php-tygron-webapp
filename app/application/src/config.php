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
	$WORKSPACE_CONTEXT		??=	null;

	$CLASS_FIND_ROOT_DIRS		??=	[$SRC_DIR, $CUSTOM_SRC_DIR];

	//Default credentials file configuration.
	$CREDENTIALS_FILE_DEFAULT 	??=	'credentials-default.json';
	//In the workspace, there are dedicated folders for default credentials and transient credentials (transient being temporary on a per-task basis).
	$CREDENTIALS_DEFAULTS_FOLDER	??=	'default';
	$CREDENTIALS_TRANSIENT_FOLDER	??=	'transient';

	$LANGUAGE_DEFAULT 		??=	'EN';
	$TIMEZONE_DEFAULT		??=	'UTC';

	$SAFE_CHARACTERS 		??= 	'[a-zA-Z0-9\-_]';

	$ROUTES_NAMESPACE		??=	'Routes';
	$DEFAULT_ROUTE_NAMESPACE	??=	'Pages';
	$DEFAULT_ROUTE	 		??= 	'Pages\\CreateTaskForm';

	$FORMAT_PARAMETER_KEY		??=	'f';

	//Cron tasks should be run automatically by touching the application/cron endpoint.
	// The CLI_TOKEN should be an alphanumeric string to authenticate a scheduler with, or null to allow any source.
	// (using null is not recommended).
	$CLI_TOKEN			??=	'';
	$CRON_LAST_RUN_FILE		??=	'last-cron';
	$CRON_TASKS			??=	['runtasks.php'];
	//Minimal interval between task updates
	$TASKS_STANDOFF_IN_SECONDS	??=	10;

	//Time interval to pause all communiocation when authentication of a task fails, to prevent temporary blocks.
	$COOLDOWN_SECONDS		??=	60;

	//Parameters always added when rendering
	$RENDER_PARAMETERS ??=[];
	$RENDER_PARAMETERS['baseUrl'] = $APPLICATION_WEB_FULL_URL;

	//Hooks are run upon each request, and allow for authentication checks, parameter translations, request logging, etc
	$HOOKS ??= [
		'AuthenticationToken', //Check whether authenticationtoken is required and valid
	];
	//Parameters for hooks:
	$CONTEXT_BY_AUTHENTICATION_TOKEN	??=	null;
	$AUTHENTICATION_TOKEN			??=	is_array($CONTEXT_BY_AUTHENTICATION_TOKEN) ? array_keys($CONTEXT_BY_AUTHENTICATION_TOKEN) : null ;


	//Parameters for overal routes
	$ROUTE_PARAMETERS_INJECTION	??= []; //Allow reading (other) parameters, and if it matches, inject or overwrite parameters
	$ROUTE_PARAMETERS_FIXED		??= []; //These parameters cannot be overwritten by external requests (but can be overwritten by injection)
	$ROUTE_PARAMETERS_DEFAULT	??= []; //If an external request does not provice these parameters, use these defaults.


	//Debug flags
	//When an erorr occurs, while running a task, do not delete the task.
	$KEEP_TASKS_WITH_ERROR 		??=	false;

	//Output file and linenumbers with exceptions where they occur.
	$DEBUG_EXCEPTIONS		??=	false;

	//When rendering assets such as html, css, js files, include originating file and dir as comment or other meta information.
	$DEBUG_ASSETS_METADATA		??=	false;



	//Content headers for outputs
	$CONTENT_HEADERS ??= [];
	$CONTENT_HEADERS['js'] ??= [
			'Content-Type' => 'application/javascript'
		];


	//After setting all the config values, some should be set in a few helper objects
	\Utils\Time::$DEFAULT_TIMEZONE = $TIMEZONE_DEFAULT;

	\Curl\LockingCurlTask::setLockLocation($WORKSPACE_LOCK_DIR);
	\Curl\TygronCurlTask::setCooldownTimeInSeconds($COOLDOWN_SECONDS);



	//Sample configuration out-of-the-box. To overwrite, set $OUT_OF_BOX_CONFIG to false
	$OUT_OF_BOX_CONFIG ??= true;

	if ( $OUT_OF_BOX_CONFIG ?? true ) {

		//When an action is performed, the parameters defined here cannot be overwritten by an end-user.
		$ROUTE_PARAMETERS_FIXED ??= [];
		$ROUTE_PARAMETERS_FIXED['CreateTask']	??= [];
		$ROUTE_PARAMETERS_FIXED['ExistingTask'] ??= [];
		$ROUTE_PARAMETERS_FIXED['.*Task'] 	??= [];

		$ROUTE_PARAMETERS_FIXED['CreateTask']['taskOperations']			??=	["ValidateCredentialsFile","CreateNewProject","GenerateProject","KeepAlive","OutputServices","DeleteCredentialsFile","SetTaskComplete","Wait"];
		$ROUTE_PARAMETERS_FIXED['ExistingProjectTask']['taskOperations']	??=	["ValidateCredentialsFile","StartProject","KeepAlive","Recalculate","ActivateMeasure","OutputServices","DeleteCredentialsFile","SetTaskComplete","Wait"];

		//Using regex for hitting multiple routes/actions
		$ROUTE_PARAMETERS_FIXED['.*Task']['cleanupOperations']			??=	["DeleteCredentialsFile","DeleteTaskFile"];



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



		//When for a specified parameter nothing is fixed, injected, or provided, add these defaults
		$ROUTE_PARAMETERS_DEFAULTS ??= [
			'CreateTask'	=> [
				'name'	=>	'unnamed',
			],
		];

	}




?>
