<?php

	include_once "src/includes.php";

	$tasksRunner = new \Tasks\Runners\TasksRunner();
	$tasksRunner->run();

?>
