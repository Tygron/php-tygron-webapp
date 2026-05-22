<?php

	namespace Utils;

	class Strings {

		public static function isValidRegex(string $candidate, bool $stateRegex = true) {
			if(@preg_match($candidate, '') === false) {
				$error = str_replace("preg_match(): ", "", error_get_last()["message"]);

				throw new \Exception('Regex pattern invalid: '
					.( $stateRegex ? '"'.$candidate.'",' : '' )
					.$error);
			}
			return true;
		}

	}

?>
