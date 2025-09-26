<?php

	namespace Install\Tests;

	class InstallTestCurlSuccess extends AbstractInstallTest {

		public function __construct() {
			parent::__construct(
				'Curl can make requests',
				'Curl must be able to make external calls.',
			);
			$this->setOrder(3);
		}

		public function test() {

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

			$this->setResult( empty($errorMessage) );

			$result = 'HTTP STATUS CODE: "'.$statusCode.'"';
			$result.= ', CURL ERROR: "'.$errorCode.'"';
			$result.= ', CURL MESSAGE: "'.$errorMessage.'"';

			$this->throwFeedbackOnFail('Curl encountered an error making the request. '.$result);

		}

	}

?>
