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

	//PHP Version
	try {
		$output[] = \Install\Tests\InstallTestPHPVersion::runStatic()->getOutputAsArray();
		$output[] = \Install\Tests\InstallTestExtentionFileInfo::runStatic()->getOutputAsArray();
		$output[] = \Install\Tests\InstallTestExtentionCurl::runStatic()->getOutputAsArray();
		$output[] = \Install\Tests\InstallTestCurlSuccess::runStatic()->getOutputAsArray();
		$output[] = \Install\Tests\InstallTestUrlRewriting::runStatic()->getOutputAsArray();
		$output[] = \Install\Tests\InstallTestCustomConfig::runStatic()->getOutputAsArray();
		$output[] = \Install\Tests\InstallTestWorkspaceValid::runStatic()->getOutputAsArray();
		$output[] = \Install\Tests\InstallTestWorkspaceSubdirsValid::runStatic()->getOutputAsArray();
		$output[] = \Install\Tests\InstallTestCronRunning::runStatic()->getOutputAsArray();
	} catch(\Throwable $e) {
		$output[] = ['title'=>'Exception while testing','description'=>'An unexpected exception occured','message'=>$e->getMessage()];
	}

	echo '<table>'.PHP_EOL;

	foreach ($output as $index => $entry) {

		$title = $entry['title'];
		$description = $entry['description'];
		$result = $entry['result'];
		$message = $entry['message'];

		$rowClass = $result===true ? 'success' : 'fail';
		$result = $result===true ? 'Success' : ($result === false ? 'Fail' : $result);

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
