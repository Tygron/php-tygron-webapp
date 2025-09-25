<?php
	# <nophp><pre>
	# ##### ##### ##### ##### ##### ##### ##### ##### ##### ###### ##### ##### ##### ##### #
	#										       #
	# Error: If you can read this, then this file is not properly interpreted.	       #
	# Check whether PHP is installed for your webserver, and used to interpret .php files  #
	#										       #
	# ##### ##### ##### ##### ##### ##### ##### ##### ##### ###### ##### ##### ##### ##### #</pre><div style='display:none;' 

	try {
		$includesFile = implode(DIRECTORY_SEPARATOR, ['..','application','src','includes.php']);
		if ( !file_exists($includesFile) || !is_readable($includesFile) ) {
			throw new \Exception('Could not access application include file. It either does not exist or is not readable.');
		}
		include_once implode(DIRECTORY_SEPARATOR, ['..','application','src','includes.php']);
	} catch ( \Throwable $e ) {
		exit($e->getMessage());
	}

	$outputs=[];
	$outputMessage = '';
	function logCheck(&$outputTable, &$outputMessage, $title, $description, $result, $message) {
	}

	//PHP Version
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = 'PHP Version';
		$newOutput['description'] = 'Minimum PHP version required is '.$MIN_VERSION_PHP.'.';

		$success = version_compare(phpversion(), $MIN_VERSION_PHP, 'gt');
		$newOutput['result'] = $success;

		if (!$success) {
			throw new \Exception('PHP version is '.phpversion().', but at least 8.2 is required. Please update to continue.');
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMessage();
	}
	$output[] = $newOutput;

	//File info
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = 'Extention: fileinfo';
		$newOutput['description'] = 'Fileinfo extention is required for mimetype interpretation of assets.';

		$success=(!(phpversion('fileinfo') === false));
		$newOutput['result'] = $success;

		if (!$success) {
			throw new \Exception('Extention is either not installed or activated. Install and activate to continue.');
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMesssage();
	}
	$output[] = $newOutput;

	//Curl available
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = 'Extention: curl';
		$newOutput['description'] = 'Curl extention is required for communication with external services.';

		$success=(!(phpversion('curl') === false));
		$newOutput['result'] = $success;

		if (!$success) {
			throw new \Exception('Extention is either not installed or activated. Install and activate to continue.');
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMessage();
	}
	$output[] = $newOutput;

	//Curl operational
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = 'Curl can make requests';
		$newOutput['description'] = 'Curl must be able to make external calls.';

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, 'https://www.tygron.com/' );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA );
		curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
		$result = curl_exec($ch);

		$errorMessage = curl_error($ch);
		$errorCode = curl_errno($ch);
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$success=empty($errorMessage);
		$newOutput['result'] = $success;

		if (!$success) {
			$result = 'HTTP STATUS CODE: "'.$statusCode.'"';
			$result.= ', CURL ERROR: "'.$errorCode.'"';
			$result.= ', CURL MESSAGE: "'.$errorMessage.'"';

			throw new \Exception('Curl encountered an error making the request. '.$result);
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMessage();
	}
	$output[] = $newOutput;


	//Rewrites enabled
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = 'URL rewrites enabled';
		$newOutput['description'] = 'Url rewriting is required for resolving assets and clean web paths';

		$success=(!empty($_GET['rewrite']??null));
		$newOutput['result'] = $success;

		if (!$success) {
			$message = 'URL rewriting not working. Check whether the module is installed, enabled, and allowed.<br>';

			$instructions = [];

			$instructions['For Apache:'] = [
				'Activate the module with "a2enmod rewrite"',
				'Ensure "AllowOverride" is set to all in either the overall config or in the vhost\'s Directory definition',
				'Restart apache when done',
			];
			$instructions['For IIS:'] = [
				'In IIS, access the relevant site and folder',
				'Use "URL Rewrite, and then the action ""Import rules"',
				'Access the relevant htaccess file for that folder and import it',
			];
			foreach ( $instructions as $type => $instruction) {
				$message.= '<p>'.$type.'<ul><li>';
				$message.= implode('</li><li>',$instruction);
				$message.='</li></ul>';
			}

			throw new \Exception($message);
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMessage();
	}
	$output[] = $newOutput;

	//Custom config file set up
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = 'Custom config file set up';
		$newOutput['description'] = 'A custom config file is required for deployment-specific configurations. ';

		$success=( file_exists($CONFIG_OVERRIDE_FILE) && is_readable($CONFIG_OVERRIDE_FILE) );
		$newOutput['result'] = $success;

		if (!$success) {
			throw new \Exception('Custom config file not found or not readable. Find the "custom/config/sample-config.php" file and create a copy such that "custom/config/config.php" exists.');
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMessage();
	}
	$output[] = $newOutput;

	//Workspace directory
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = 'Workspace directory is set up';
		$newOutput['description'] = 'A workspace directory is defined and is writable';

		$success=is_dir($WORKSPACE_DIR) && is_writable($WORKSPACE_DIR);
		$newOutput['result'] = $success;

		if (!$success) {
			throw new \Exception('Workspace directory (defined in $WORKSAPCE_DIR) either does not exist or is not writable. Define it in the custom config file.');
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMessage();
	}
	$output[] = $newOutput;

	//Workspace subdirectories
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = 'Workspace subdirectories writable';
		$newOutput['description'] = 'The directories in the workspace must be writable';

		$subdirs = scandir($WORKSPACE_DIR);
		$unwritable = [];
		foreach ($subdirs as $index=>$dirName) {
			$dir = $WORKSPACE_DIR.DIRECTORY_SEPARATOR.$dirName;
			if ( !in_array($dirName,['.','..']) && is_dir($dir) && !is_writable($dir) ) {
				$unwritable[] = $dirName;
			}
		}

		$success=count($unwritable) === 0;
		$newOutput['result'] = $success;

		if (!$success) {
			throw new \Exception('The following workspace subdirectories are unwritable: '.implode(', ',$unwritable));
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMessage();
	}
	$output[] = $newOutput;

	//Cron or scheduled
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = 'Cronjobs are running';
		$newOutput['description'] = 'Cron, or alternative periodic task scheduler, should run approximately once per minute';

		if ( empty($CRON_LAST_RUN_FILE) ) {
			$success = 'Cannot verify';
			throw new \Exception('No flag-file (defined in CRON_LAST_RUN_FILE) defined.');
		}

		$lastUpdate = @file_get_contents( $WORKSPACE_DIR.DIRECTORY_SEPARATOR.$CRON_LAST_RUN_FILE );
		$lastUpdateTimeDifference = \Utils\Time::getCurrentTimestamp() - $lastUpdate;
		$success=$lastUpdateTimeDifference < (60 * 1.5);
		$newOutput['result'] = $success;

		if (!$success) {
			throw new \Exception('Last update was '.$lastUpdateTimeDifference.' seconds ago.');
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMessage();
	}
	$output[] = $newOutput;


	/*

	//Generic test
	$newOutput = ['title'=>'','description'=>'','result'=>'','message'=>''];
	try {
		$newOutput['title'] = '';
		$newOutput['description'] = '';

		$success=();
		$newOutput['result'] = $success;

		if (!$success) {
			throw new \Exception('');
		}

	} catch ( \Throwable $e ) {
			$newOutput['message'] = $e->getMessage();
	}
	$output[] = $newOutput;

	*/

	/**
		Include includes.php

		Checks to run:
			PHP version
				Instruct update of PHP version
			Curl installed
				Instruct install and activation of Curl
			Filetype info working
				Instruct filetype extention activation
			Rewrite installed and active
				Instruct if apache2 to activate mod rewrite, and allow overrides in vhost directory definition
				Instruct if different environment to activate relevant extention and modify rules in .htaccess as needed
			Check custom config file in place
				Instruct copying of sample-config and essential keys to set up
			Check workspace directory and subdirectories writable
				Instruct verifying workspace directory exists and is usable by webserver user
Check running periodic task (last-automatic-update somewhere)
	Instruct either:
		Set up CLI call to run cron.php file
		Configure CLI_TOKEN and set up web call to call cron.php file, with token
			( * * * * * wget -O - https://webapplocation.com/application/cron.php?cli_token= )
	**/

	echo '<table>'.PHP_EOL;

	foreach ($output as $index => $entry) {

		$title = $entry['title'];
		$description = $entry['description'];
		$result = $entry['result'];
		$message = $entry['message'];

		$rowClass = $result===true ? 'success' : 'fail';
		$result = $result===true ? 'Success' : 'Fail';

		$entryString = '';
		$entryString.='<tr class="'.$rowClass.'">';
		$entryString.='<th class="title">'.$title.'</th>';
		$entryString.='<td class="description">'.$description.'</td>';
		$entryString.='<td class="result">'.$result.'</td>';
		$entryString.='<td class="message">'.$message.'</td>';
		$entryString.='</tr>';

		echo $entryString.PHP_EOL;

	}

	echo '</table>'.PHP_EOL;
?>
