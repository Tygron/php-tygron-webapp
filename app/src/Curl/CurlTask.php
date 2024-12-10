<?php

	namespace Curl;

	class CurlTask {

		private $curlObject = null;
		private $curlError = null;
		private $curlErrorCode = null;

		private $statusCode = -1;
		private $result = null;

		private $requestUrl = null;
		private $requestHeaders = null;
		private $requestData = null;

		private $url = '';
		private $parameters = [];
		private $data = null;

		private $headers = [];

		private $method = 'GET';
		private $timeoutInSeconds = 120;

		public function setMethod( string $method ) {
			$this->method = $method;
			return $this;
		}

		public function setUrl( string $url = '' ) {
			$split = explode('?',$url);
			$this->url = $split[0];
			if ( count($split)>1 ) {
				foreach ( array_slice($split) as $key => $value ) {
					$keyValue = explode('=',$value,2);
					$this->setParameters($keyValue);
				}
			}
			return $this;
		}
		public function setParameters( array $parameters = [] ) {
			$this->parameters = array_merge($this->parameters, $parameters);
			foreach ( $this->parameters as $key => $value ) {
				if ( is_null($value) ) {
					unset($this->parameters[$key]);
				}
			}
			return $this;
		}

		public function setData( $data ) {
			if ( is_string($data) ) {
				$this->data = $data;
			} else if ( is_array($data) ) {
				if ( !is_array($this->data) ) {
					$this->data = [];
				}
				$this->data = array_merge( $this->data, $data );
			} else {
				$this->data = $data;
			}
			return $this;
		}

		public function setHeaders( array $headers ) {
			$this->headers = array_merge($this->headers, $headers);
			foreach ( $this->headers as $key => $value ) {
				if ( is_null($value) ) {
					unset($this->headers[$key]);
				}
			}
			return $this;
		}

		public function setAuthHeader( string $header, string $type='Basic' ) {
			$this->setHeaders( ['Authorization' => $type.' '.$header] );
			return $this;
		}



		public function getRequest() {
			return [
					'url' => $this->requestUrl,
					'headers' => $this->headers,
					'data' => $this->requestData,
					'method' => $this->method
				];
		}
		public function getResponse() {
			return [
					'curlError' => $this->curlError,
					'curlErrorCode' => $this->curlErrorCode,

					'statusCode' => $this->statusCode,
					'content' => $this->result,
					'headers' => $this->requestHeaders
				];
		}
		public function getStatus() {
			return $this->statusCode;
		}
		public function getContent() {
			return $this->result;
		}



		public function run() {
			$this->prepareForCurl();
			$this->createCurlObject();
			$this->executeCurlObject();
			return $this;
		}

		protected function prepareForCurl() {
                        $this->requestUrl = $this->prepareUrl($this->url, $this->parameters);
                        if ( $this->method == 'GET' ) {
                                $this->requestUrl = $this->prepareUrl($this->requestUrl, $this->data);
				$this->requestData = null;
                        } else {
				$this->requestData = $this->prepareData($this->data);
			}
			$this->requestHeaders = $this->prepareHeaders($this->headers);
		}



		protected function prepareUrl( string $url, string|array|null $parameters = [] ) {
			$preparedUrl = $url;
			if ( is_array($parameters) && count($parameters)>0 ) {
				$preparedParameters = http_build_query($parameters);
				$preparedUrl = $preparedUrl . (str_contains($url,'?') ? '&' : '?') . $preparedParameters;
			} else if ( is_string($parameters) && !empty($parameters)) {
				$preparedUrl = $preparedUrl . (str_contains($url,'?') || (str_starts_with($parameters,'?')) ? '&' : '?').$parameters;
			}
			return $preparedUrl;
		}

		protected function prepareData( string|array $data ) {
			$preparedData = $data;
			return $preparedData;
		}

		protected function prepareHeaders( array $headers ) {
			$preparedHeaders = [];
			foreach ( $headers as $key => $value ) {
				array_push($preparedHeaders, $key.': '.$value);
			}
			return $preparedHeaders;
		}



		private function createCurlObject() {
			$ch = curl_init();

			curl_setopt( $ch, CURLOPT_URL, $this->requestUrl );
			curl_setopt( $ch, CURLOPT_TIMEOUT, $this->timeoutInSeconds );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

			curl_setopt( $ch, CURLOPT_HTTPHEADER, $this->requestHeaders );

			if ( $this->method == 'POST' ) {
				curl_setopt( $ch, CURLOPT_POST, TRUE );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($this->requestData) );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, array_merge($this->requestHeaders,[
						'Accept: application/json',
						"Content-Type: application/json",
					] ) );
			}

			$this->curlObject = $ch;
		}

		private function executeCurlObject() {
			if ( $this->curlObject === null ) {
				throw new \Exception('No Curl object prepared');
			}
			$ch = $this->curlObject;

			curl_setopt($ch, CURLINFO_HEADER_OUT, true);

			$result = curl_exec($ch);
			$this->curlError = curl_error( $ch );
			$this->curlErrorCode = curl_errno( $ch );

			$this->statusCode = curl_getinfo ( $ch ,CURLINFO_HTTP_CODE );
			$this->requestHeaders = curl_getinfo($ch, CURLINFO_HEADER_OUT );

			$this->result = json_decode( $result, true );
			if ( ($this->result == null) && ($result !== 'null') ) {
				if ($result !== 'null') {
					$this->result = $result;
				}
			}

		}

	}

?>
