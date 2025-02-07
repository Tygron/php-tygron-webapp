<?php

	namespace Utils;

	class Strings {

		public static function dateTimeString( string $format = 'Y-m-d-H-i-s', $dateTime = null) {
			return \Utils\Time::getReadableDateTime($format, $dateTime);
		}

	}

?>
