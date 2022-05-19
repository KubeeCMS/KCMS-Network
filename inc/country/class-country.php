<?php
/**
 * Base Country class.
 *
 * @see https://github.com/harpreetkhalsagtbit/country-state-city
 *
 * @package WP_Ultimo
 * @subpackage Country
 * @since 2.0.11
 */

namespace WP_Ultimo\Country;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base Country class.
 *
 * @since 2.0.0
 */
abstract class Country {

	/**
	 * General country attributes.
	 *
	 * This might be useful, might be not.
	 * In case of doubt, keep it.
	 *
	 * @since 2.0.11
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The type of nomenclature used to refer to the country sub-divisions.
	 *
	 * @since 2.0.11
	 * @var string
	 */
	protected $state_type = 'unknown';

	/**
	 * Return the country name.
	 *
	 * @since 2.0.11
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Magic getter to allow us to access attributes as properties.
	 *
	 * @since 2.0.11
	 *
	 * @param string $attribute The attribute to fetch.
	 * @return mixed|null
	 */
	public function __get($attribute) {

		return wu_get_isset($this->attributes, $attribute, null);

	} // end __get;

	/**
	 * Returns the list of states/provinces of this country.
	 *
	 * The list returned is in the XX => Name format.
	 *
	 * @since 2.0.11
	 * @return array
	 */
	public function get_states() {

		$states = $this->states();

		/**
		 * Returns the list of states for this country.
		 *
		 * @since 2.0.11
		 *
		 * @param array $states List of states in a XX => Name format.
		 * @param string $country_code Two-letter ISO code for the country.
		 * @param WP_Ultimo\Country\Country $current_country Instance of the current class.
		 * @return array The filtered list of states.
		 */
		return apply_filters('wu_country_get_states', $states, $this->country_code, $this);

	} // end get_states;

	/**
	 * Returns states as options.
	 *
	 * @since 2.0.12
	 *
	 * @param string $placeholder The placeholder for the empty option.
	 * @return array
	 */
	public function get_states_as_options($placeholder = '') {

		$options = $this->get_states();

		$placeholder_option = array();

		if ($placeholder !== false && $options) {

			$division_name = $this->get_administrative_division_name();

			$placeholder_option[''] = $placeholder !== '' ? $placeholder : sprintf(__('Select your %s', 'wp-ultimo'), $division_name);

		} // end if;

		return array_merge($placeholder_option, $options);

	} // end get_states_as_options;

	/**
	 * Returns the list of cities for a country and state.
	 *
	 * @since 2.0.11
	 *
	 * @param string $state_code Two-letter ISO code for the state.
	 * @return array
	 */
	public function get_cities($state_code = '') {

		if (empty($state_code)) {

			return array();

		} // end if;

		$repository_file = wu_path("inc/country/{$this->country_code}/{$state_code}.php");

		if (file_exists($repository_file) === false) {

			return array();

		} // end if;

		$cities = include($repository_file);

		/**
		 * Returns the list of cities for a state in a country.
		 *
		 * @since 2.0.11
		 *
		 * @param array $cities List of state city names. No keys are present.
		 * @param string $country_code Two-letter ISO code for the country.
		 * @param string $state_code Two-letter ISO code for the state.
		 * @param WP_Ultimo\Country\Country $current_country Instance of the current class.
		 * @return array The filtered list of states.
		 */
		return apply_filters('wu_country_get_cities', $cities, $this->country_code, $state_code, $this);

	} // end get_cities;

	/**
	 * Get state cities as options.
	 *
	 * @since 2.0.12
	 *
	 * @param string $state_code The state code.
	 * @param string $placeholder The placeholder for the empty option.
	 * @return array
	 */
	public function get_cities_as_options($state_code = '', $placeholder = '') {

		$options = $this->get_cities($state_code);

		$placeholder_option = array();

		if ($placeholder !== false && $options) {

			$placeholder_option[''] = $placeholder !== '' ? $placeholder : __('Select your city', 'wp-ultimo');

		} // end if;

		$options = array_combine($options, $options);

		return array_merge($placeholder_option, $options);

	} // end get_cities_as_options;

	/**
	 * Returns the list of states for a country.
	 *
	 * @since 2.0.11
	 * @return array The list of state/provinces for the country.
	 */
	protected function states() {

		return array();

	} // end states;

	/**
	 * Get the name of municipalities for a country/state.
	 *
	 * Some countries call cities, cities, other, town,
	 * others municipalities, etc.
	 *
	 * @since 2.0.12
	 *
	 * @param string  $state_code The municipality name.
	 * @param boolean $ucwords If we need to return the results with ucwords applied.
	 * @return string
	 */
	public function get_municipality_name($state_code = null, $ucwords = false) {

		$name = __('city', 'wp-ultimo');

		$name = $ucwords ? ucwords($name) : $name;

		return apply_filters('wu_country_get_municipality_name', $name, $this->country_code, $state_code, $ucwords, $this);

	} // end get_municipality_name;

	/**
	 * Get the name given to states for a country.
	 *
	 * Some countries call states states, others provinces,
	 * others regions, etc.
	 *
	 * @since 2.0.12
	 *
	 * @param string  $state_code The state code.
	 * @param boolean $ucwords If we need to return the results with ucwords applied.
	 * @return string
	 */
	public function get_administrative_division_name($state_code = null, $ucwords = false) {

		$denominations = array(
			'province'             => __('province', 'wp-ultimo'),
			'state'                => __('state', 'wp-ultimo'),
			'territory'            => __('territory', 'wp-ultimo'),
			'region'               => __('region', 'wp-ultimo'),
			'department'           => __('department', 'wp-ultimo'),
			'district'             => __('district', 'wp-ultimo'),
			'prefecture'           => __('prefecture', 'wp-ultimo'),
			'autonomous_community' => __('autonomous community', 'wp-ultimo'),
			'parish'               => __('parish', 'wp-ultimo'),
			'county'               => __('county', 'wp-ultimo'),
			'division'             => __('division', 'wp-ultimo'),
			'unknown'              => __('state / province', 'wp-ultimo'),
		);

		$name = wu_get_isset($denominations, $this->state_type, $denominations['unknown']);

		$name = $ucwords ? ucwords($name) : $name;

		/**
		 * Returns nice name of the country administrative sub-divisions.
		 *
		 * @since 2.0.11
		 *
		 * @param string $name The division name. Usually something like state, province, region, etc.
		 * @param string $country_code Two-letter ISO code for the country.
		 * @param string $state_code Two-letter ISO code for the state.
		 * @param WP_Ultimo\Country\Country $current_country Instance of the current class.
		 * @param bool $current_country Instance of the current class.
		 * @return string The modified division name.
		 */
		return apply_filters('wu_country_get_administrative_division_name', $name, $this->country_code, $state_code, $ucwords, $this);

	} // end get_administrative_division_name;

} // end class Country;
