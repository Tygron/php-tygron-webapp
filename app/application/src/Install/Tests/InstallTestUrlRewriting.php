<?php

	namespace Install\Tests;

	class InstallTestUrlRewriting extends AbstractInstallTest {

		public function __construct() {
			parent::__construct(
				'URL Rewrites enabled',
				'URL rewriting is required for resolving assets and clean web paths',
			);
			$this->setOrder(7);
		}

		public function test() {
			global $APPLICATION_WEB_FULL_URL, $APP_RESOURCES_DIR;

			$fileName = '/assets/images/tygron_logo.png';
			$file = $APP_RESOURCES_DIR.$fileName;

			if ( !file_exists($file)) {
				$this->setResult( 'Could not test');
				$this->throwFeedbackOnNotSuccess('Could not find file to test with: '.$file);
			}

			$url = $APPLICATION_WEB_FULL_URL.$fileName;

			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_SSL_OPTIONS, CURLSSLOPT_NATIVE_CA );
			curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
			$result = curl_exec($ch);

			$errorMessage = curl_error($ch);
			$errorCode = curl_errno($ch);
			$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			$this->setResult( $statusCode === 200 );

			$errorMessage = $this->getFixes();
			if ( $statusCode !== 404 ) {
	                        $result = 'HTTP STATUS CODE: "'.$statusCode.'"';
	                        $result.= ', CURL ERROR: "'.$errorCode.'"';
	                        $result.= ', CURL MESSAGE: "'.$errorMessage.'"';

				$errorMessage = 'Curl encountered an error making the request. '.$result;
			}
			$this->throwFeedbackOnFail($errorMessage);
		}

		public function getFixes() {
			$message = 'URL rewriting not working. Check whether the module is installed, enabled, and allowed<br>';
			$message = '(If running from a subfolder, ensure the config file has the APPLICATION_WEB_FULL_URL set correctly.)<br>';

                        $instructions = [];
                        $instructions['For Apache:'] = [
                                'Activate the module with "a2enmod rewrite"',
                                'Ensure "AllowOverride" is set to all in either the overall config or in the vhost\'s Directory definition',
                                'Restart apache when done',
                        ];
                        $instructions['For IIS:'] = [
                                'In IIS, access the relevant site and folder',
                                'Use "URL Rewrite", and then the action "Import rules"',
                                'Access the relevant .htaccess file for that folder and import it',
                        ];
                        foreach ( $instructions as $type => $instruction) {
                                $message.= '<p>'.$type.'<ul><li>';
                                $message.= implode('</li><li>',$instruction);
                                $message.='</li></ul>';
                        }

                        return $message;
		}

	}

?>
