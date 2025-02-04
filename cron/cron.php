<?php

	$documentroot = "/var/www/html";
	include_once implode(DIRECTORY_SEPARATOR, [$documentroot, 'application','src','includes.php']);

	$runner = new \Runners\TasksRunner();
	$runner->run();
?>
