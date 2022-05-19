<?php
/**
 * Adds a validation rules for countries
 *
 * @package WP_Ultimo
 * @subpackage Helpers/Validation_Rules
 * @since 2.0.11
 */

namespace WP_Ultimo\Helpers\Validation_Rules;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;

/**
 * Validates template sites.
 *
 * @since 2.0.4
 */
class Country extends Rule {

	/**
	 * Parameters that this rule accepts.
	 *
	 * @since 2.0.4
	 * @var array
	 */
	protected $fillableParams = array(); // phpcs:ignore

	/**
	 * Performs the actual check.
	 *
	 * @since 2.0.4
	 *
	 * @param mixed $country The country value detected.
	 * @return boolean
	 */
	public function check($country) : bool { // phpcs:ignore

		$check = true;

		if ($country) {

			$country = strtoupper($country);

			$allowed_countries = array_keys(wu_get_countries());

			$check = in_array($country, $allowed_countries, true);

		} // end if;

		return $check;

	} // end check;

} // end class Country;
