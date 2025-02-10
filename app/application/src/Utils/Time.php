<?php

	namespace Utils;

	class Time {

		public static $DEFAULT_TIMEZONE = 'UTC';

		public static function getCurrentTimestamp() {
			return time();
		}

		public static function getTimePeriod( int $timestampStart = null, int $timestampEnd = null ) {
			$current = self::getCurrentTimestamp();
			$timestampStart = $timestampStart ?? $current;
			$timestampEnd = $timestampEnd ?? $current;
			return $timestampEnd - $timestampStart;
		}

		public static function getReadableDateTime( $dateTime = null, string $format = 'Y-m-d-H-i-s', string $timezone = null ) {
			$dateTimeObject = new \DateTime();
			$dateTimeObject->setTimestamp($dateTime ?? self::getCurrentTimestamp());
			$dateTimeObject->setTimezone(new \DateTimeZone($timezone ?? self::$DEFAULT_TIMEZONE));
			return $dateTimeObject->format( $format );
		}
		public static function getReadableDate( $dateTime = null, string $format = 'Y-m-d', string $timezone = null ) {
			return self::getReadableDateTime( $dateTime, $format, $timezone );
		}
		public static function getReadableTime( $dateTime = null, string $format = 'H-i-s', string $timezone = null ) {
			return self::getReadableDateTime( $dateTime, $format, $timezone );
		}
	}

?>
