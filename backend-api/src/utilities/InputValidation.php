<?php

declare(strict_types=1);

namespace App\utilities;

/**
 * Per research, it is best practice to validate on a
 * populated D.T.O. in lieu of validating the data prior
 * to populating the D.T.O. So this 'service' class
 * addresses that validation.
*/
class InputValidation {
	public static function is_length_valid(string $value, int $min_length, int $max_length) : bool {
		if (strlen($value) < $min_length || strlen($value) > $max_length) {
			return false;
		}
		return true;
	}
}