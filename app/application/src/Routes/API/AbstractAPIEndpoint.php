<?php

	namespace Routes\API;

	abstract class AbstractAPIEndpoint extends AbstractAPI {

                protected function returnSuccess($content = null) {
                        $success = [
                                        'success'=>true,
                                        'content'=>$content,
                                ];
			return $success;
                }
                protected function returnError($content=null, \Throwable|string $exception=null) {
			global $DEBUG_EXCEPTIONS;
			if ( ($content instanceof \Throwable) && is_null($exception) ) {
				$exception = $content;
				$content = null;
			}
                        $error = [
                                        'success'=>false,
                                        'content'=>$content,
                                        'error'=>\Utils\Exceptions::exceptionToString($exception, $DEBUG_EXCEPTIONS),
                                ];
			return $error;
                }

	}
