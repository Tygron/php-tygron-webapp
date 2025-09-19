<?php

	$documentroot = "/var/www/html";
	include_once implode(DIRECTORY_SEPARATOR, [$documentroot, 'application', 'src', 'includes.php']);

	include_once implode(DIRECTORY_SEPARATOR, [$documentroot, 'application', 'cron.php']);
?>
