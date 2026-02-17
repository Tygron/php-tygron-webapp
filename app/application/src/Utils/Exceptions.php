<?php

	namespace Utils;

	class Exceptions {

		public static function exceptionToObject(\Throwable|string $exception, bool $detailed = false) {
			return $detailed ? self::exceptionToDetailedOject($exception) : self::exceptionToSimpleObject($exception);
		}
		public static function exceptionToSimpleObject(\Throwable|string $exception) {
			$obj = self::exceptionToDetailedObject($exception, true);

			return [
				'message' => $obj['message'],
			];

		}
		public static function exceptionToDetailedObject(\Throwable|string $exception) {
			$obj = [
				'message'	=>	is_string($exception) ? $exception : 'unknown',
				'file'		=>	'unknown',
				'line'		=>	'unknown'
			];

			if ($exception instanceof \Throwable) {
				$obj =	[
					'message' => $exception->getMessage(),
					'file' => $exception->getFile(),
					'line' => $exception->getLine(),
				];
			}
			return $obj;
		}

		public static function exceptionToString( \Throwable|string $exception, bool $detailed = false) {
			return $detailed ? self::exceptionToDetailedString($exception) : self::exceptionToSimpleString($exception);
		}

		public static function exceptionToSimpleString(\Throwable|string $exception) {
			$obj = self::exceptionToDetailedObject($exception);

			return $obj['message'];
		}

		public static function exceptionToDetailedString(\Throwable|string $exception) {
			$obj = self::exceptionToDetailedObject($exception);

			return $obj['message'] . ' (line '.$obj['line'].' in '.$obj['file'].')';
		}

	}

?>
