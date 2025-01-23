<?php

	//Location of the workspace folder
	$WORKSPACE_DIR = '/var/workspace/';

	//Authentication token. If set, this token must be provided before access to the application proper.
	$AUTHENTICATION_TOKEN = '';

	//The name of the default credentials file. When set, users needn't provide their own Tygron Platform credentials.
	//Should be placed in the workspace folder, in a subfolder named "credentials".
	$CREDENTIALS_FILE_DEFAULT = 'credentials-default.json';

	//The (default) language to use for texts and messages.
	$LANGUAGE_DEFAULT = 'EN';

	//Whether to keep task files of tasks which have resulted in an error.
	$KEEP_TASKS_WITH_ERROR = true;


	//When an action is performed, the parameters defined here cannot be overwritten by an end-user.
	$ACTION_PARAMETERS_FIXED['CreateTask'] ??= [];
	$ACTION_PARAMETERS_FIXED['CreateTask']['template'] = 'demo_heat_stress';
	$ACTION_PARAMETERS_FIXED['CreateTask']['taskOperations'] = ["ValidateCredentialsFile", "CreateNewProject","GenerateProject","KeepAlive","OutputWebViewer3DHtml"];
	$ACTION_PARAMETERS_FIXED['CreateTask']['cleanupOperations'] = ["DeleteCredentialsFile","DeleteTaskFile"];

	//When an action is performed, the parameters defined here are offered as defaults and available for change.
	$ACTION_PARAMETERS_DEFAULT['CreateTask'] ??= [];
	$ACTION_PARAMETERS_DEFAULT['CreateTask']['platform'] = 'engine';

?>
