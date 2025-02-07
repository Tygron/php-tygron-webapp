<?php

	namespace Utils;

	class Time {

		public static function getCurrentTimestamp() {
			return time();
		}

		public static function getTimePeriod( int $timestampStart = null, int $timestampEnd = null ) {
			$current = self::getCurrentTimestamp();
			$timestampStart = $timestampStart ?? $current;
			$timestampEnd = $timestampEnd ?? $current;
			return $timestampEnd - $timestampStart;
		}

		public static function getReadableDateTime( string $format = 'Y-m-d-H-i-s', $dateTime = null ) {
			return date( $format, $dateTime );
		}
		public static function getReadableDate( string $format = 'Y-m-d', $dateTime = null ) {
			return self::getReadableDateTime( $format, $dateTime );
		}
		public static function getReadableTime( string $format = 'H-i-s', $dateTime = null ) {
			return self::getReadableDateTime( $format, $dateTime );
		}
	}

?>
