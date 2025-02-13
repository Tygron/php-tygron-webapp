<?php

	$documentroot = "/var/www/html";
	include_once implode(DIRECTORY_SEPARATOR, [$documentroot, 'application','src','includes.php']);

	$runner = new \Tasks\Runners\TasksRunner();
	$runner->run();
?>
