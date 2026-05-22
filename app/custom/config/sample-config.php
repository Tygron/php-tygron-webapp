<?php

	// Webbase. Use "/" when running directly as example.tygron.com/
	// Use "/subdir/" when running from example.tygron.com/subdir/
	$APPLICATION_WEBBASE = '/';

	//Location of the workspace folder
	$WORKSPACE_DIR = '/var/workspace/';

	//Authentication token. If set, this token must be provided before access to the application proper.
	//Authentication token can be a string, or an array ov valid authentication tokens
	$AUTHENTICATION_TOKEN = '';
	//Instead, an array of contexts indexed by authentication token can be provided as well.
	//Contexts will cause Task files to be organizes into separate folders, preventing task access between contexts
	//When this is defined and AUTHENTICATION_TOKEN is null, AUTHENTICATION_TOKEN is auto-derived
	$CONTEXT_BY_AUTHENTICATIONTOKEN = [ 'token1' => 'context1', 'token2' => 'context2' ];



	//The name of the default credentials file. When set, users needn't provide their own Tygron Platform credentials.
	//Should be placed in the workspace folder, in a subfolder named "credentials/default".
	$CREDENTIALS_FILE_DEFAULT = 'credentials-default.json';

	//The (default) language to use for texts and messages.
	$LANGUAGE_DEFAULT = 'EN';

	//The (default) timezone for human-readable date-times.
	$TIMEZONE_DEFAULT = 'Europe/Amsterdam';

	//Verification token to run cron tasks with
	//The CLI_TOKEN should be an alphanumeric string to authenticate a scheduler with, or null to allow any source (using null is not recommended).
	$CLI_TOKEN = null; //Set to a random character string

	//Whether to keep task files of tasks which have resulted in an error.
	$KEEP_TASKS_WITH_ERROR = true;

        //Output file and linenumbers with exceptions where they occur
        $DEBUG_EXCEPTIONS ??= false;





	//Disable the out-of-the-box parameter rules, allowing full customization
	$OUT_OF_BOX_CONFIG = false;

	//When an action is performed, the parameters defined here cannot be overwritten by an end-user.
	$ROUTE_PARAMETERS_FIXED['CreateTask'] = [
			'template'		=> 'demo_heat_stress',
			'taskOperations'	= [
					"ValidateCredentialsFile",
					"CreateNewProject",
					"ActivateServiceOverlays",
					"GenerateProject",
					"KeepAlive",
					"OutputServices",
					"SetTaskComplete",
					"Wait",
				],
		];
	$ROUTE_PARAMETERS_FIXED['ExistingProjectTask'] = [
			'taskOperations'	= [
					"ValidateCredentialsFile",
					"StartProject",
					"KeepAlive",
					"Recalculate",
					"ActivateMeasure",
					"OutputServices",
					"DeleteCredentialsFile",
					"SetTaskComplete",
					"Wait",
				],
		];
	$ROUTE_PARAMETERS_FIXED['*'] = [
			'useDefaultCredentials'	=> false,
			'cleanupOperations'	= [
					'DeleteCredentialsFile',
					'DeleteTaskFile',
				],
		];

	//When an action is performed, the parameters defined here are offered as defaults and available for change.
	$ROUTE_PARAMETERS_DEFAULT['*'] = [
			'name'			=>	'task',
		];


	//When an action is performed, and one of these parameters has a specified value, the parameters provided are injected (overwriting fixed where applicable).
	//Note that only user-provided inputs are checked to see whether parameters should be injected.

	$ROUTE_PARAMETERS_INJECTION['*'] = [
		'requestContext' => [
			'context1'	=> [ 'useDefaultCredentials' => 'default-credentials1.json', ],
			'context2'	=> [ 'useDefaultCredentials' => 'default-credentials2.json', ],
		],
	];
	$ROUTE_PARAMETERS_INJECTION['CreateTask'] = [
		'size'	=> [
			'small'		=> [ 'name'	=> 'small',	'size'	=> [1000,1000], ],
			'large'		=> [ 'name'	=> 'large',	'size'	=> [2500,2500], ],
		];
	];
?>
