<?php
/**
 * Default Country class for when country data is not available.
 *
 * @package WP_Ultimo
 * @subpackage Country
 * @since 2.0.11
 */

namespace WP_Ultimo\Country;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Default Country class for when country data is not available.
 *
 * @since 2.0.11
 *
 * @property-read string $code
 * @property-read string $currency
 * @property-read int $phone_code
 */
class Country_Default extends Country {

	/**
	 * Holds the country name.
	 *
	 * @since 2.0.11
	 * @var string
	 */
	protected $name = '';

	/**
	 * Return the country name.
	 *
	 * @since 2.0.11
	 * @return string
	 */
	public function get_name() {

		return $this->name;

	} // end get_name;

	/**
	 * Creates a country instance with a code and name.
	 *
	 * @since 2.0.11
	 *
	 * @param string $code The country two-letter code.
	 * @param string $name The country name.
	 * @param array  $attributes The country attributes.
	 * @return \WP_Ultimo\Country\Country
	 */
	public static function build($code, $name = null, $attributes = array()) {

		$instance = new self;

		$instance->name = $name ? $name : wu_get_country_name($code);

		$instance->attributes = wp_parse_args($attributes, array(
			'country_code' => strtoupper($code),
			'currency'     => strtoupper($code),
			'phone_code'   => 00,
		));

		return $instance;

	} // end build;

} // end class Country_Default;
