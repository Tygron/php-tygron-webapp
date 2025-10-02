<?php

	// Webbase. Use "/" when running directly as example.tygron.com/
	// Use "/subdir/" when running from example.tygron.com/subdir/
	$APPLICATION_WEBBASE = '/';

	//Location of the workspace folder
	$WORKSPACE_DIR = '/var/workspace/';

	//Authentication token. If set, this token must be provided before access to the application proper.
	$AUTHENTICATION_TOKEN = '';

	//The name of the default credentials file. When set, users needn't provide their own Tygron Platform credentials.
	//Should be placed in the workspace folder, in a subfolder named "credentials".
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


	//When an action is performed, the parameters defined here cannot be overwritten by an end-user.
	$ACTION_PARAMETERS_FIXED['CreateTask'] ??= [];
	$ACTION_PARAMETERS_FIXED['CreateTask']['useDefaultCredentials'] = false;
	$ACTION_PARAMETERS_FIXED['CreateTask']['template'] = 'demo_heat_stress';
	$ACTION_PARAMETERS_FIXED['CreateTask']['taskOperations'] = ["ValidateCredentialsFile", "CreateNewProject","GenerateProject","KeepAlive","OutputWebViewer3DHtml", "SetTaskComplete", "Wait"];
	$ACTION_PARAMETERS_FIXED['CreateTask']['cleanupOperations'] = ["DeleteCredentialsFile","DeleteTaskFile"];

	//When an action is performed, the parameters defined here are offered as defaults and available for change.
	$ACTION_PARAMETERS_DEFAULT['CreateTask'] ??= [];
	$ACTION_PARAMETERS_DEFAULT['CreateTask']['name'] = 'task';

	//When an action is performed, and one of these parameters has a specified value, the parameters provided are injected (overwriting fixed where applicable).
	//Note that only user-provided inputs are checked to see whether parameters should be injected.
	$ACTION_PARAMETERS_INJECTION ??= [];
	$ACTION_PARAMETERS_INJECTION['CreateTask'] ??= [];
	$ACTION_PARAMETERS_INJECTION['CreateTask']['size'] ??= [
		'small' => [
			'name'                  =>      'small',
			'size'                  =>      [1000,1000],
		],
		'large' => [
			'name'                  =>      'large',
			'size'                  =>      [2500,2500],
		],
	];
?>
