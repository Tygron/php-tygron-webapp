<?php
	$WORKSPACE_DIR = implode(DIRECTORY_SEPARATOR, ['','var','workspace']);

	$WORKSPACE_TASK_DIR = implode(DIRECTORY_SEPARATOR, [$WORKSPACE_DIR, 'tasks']);
	$WORKSPACE_CREDENTIALS_DIR = implode(DIRECTORY_SEPARATOR, [$WORKSPACE_DIR, 'credentials']);

	$CREDENTIALS_FILE_DEFAULT = 'credentials-default.json';

	$SAFE_CHARACTERS = '[a-zA-Z0-9\-_]';

	$LANGUAGE_DEFAULT = 'EN'
?>
