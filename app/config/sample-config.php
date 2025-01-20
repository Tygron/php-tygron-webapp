<?php

	//Location of the workspace folder
	$WORKSPACE_DIR = '/var/workspace/';

	//Authentication token. If set, this token must be provided before access to the application proper.
	$AUTHENTICATION_TOKEN = null;

	//The name of the default credentials file. When set, users needn't provide their own Tygron Platform credentials.
	//Should be placed in the workspace folder, in a subfolder named "credentials".
	$CREDENTIALS_FILE_DEFAULT = 'credentials-default.json';

	//The (default) language to use for texts and messages.
	$LANGUAGE_DEFAULT = 'EN';

	//Whether to keep task files of tasks which have resulted in an error.
	$KEEP_TASKS_WITH_ERROR = true;
?>
