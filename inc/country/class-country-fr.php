<?php
/**
 * Country Class for France (FR).
 *
 * State/province count: 42
 * City count: 8895
 * City count per state/province:
 * - ARA: 1231 cities
 * - NAQ: 968 cities
 * - HDF: 921 cities
 * - GES: 887 cities
 * - OCC: 828 cities
 * - IDF: 694 cities
 * - BRE: 667 cities
 * - PDL: 660 cities
 * - PAC: 535 cities
 * - NOR: 526 cities
 * - CVL: 476 cities
 * - BFC: 451 cities
 * - COR: 51 cities
 *
 * @package WP_Ultimo\Country
 * @since 2.0.11
 */

namespace WP_Ultimo\Country;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Country Class for France (FR).
 *
 * @since 2.0.11
 * @internal last-generated in 2022-05
 * @generated class generated by our build scripts, do not change!
 *
 * @property-read string $code
 * @property-read string $currency
 * @property-read int $phone_code
 */
class Country_FR extends Country {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * General country attributes.
	 *
	 * This might be useful, might be not.
	 * In case of doubt, keep it.
	 *
	 * @since 2.0.11
	 * @var array
	 */
	protected $attributes = array(
		'country_code' => 'FR',
		'currency'     => 'EUR',
		'phone_code'   => 33,
	);

	/**
	 * The type of nomenclature used to refer to the country sub-divisions.
	 *
	 * @since 2.0.11
	 * @var string
	 */
	protected $state_type = 'region';

	/**
	 * Return the country name.
	 *
	 * @since 2.0.11
	 * @return string
	 */
	public function get_name() {

		return __('France', 'wp-ultimo');

	} // end get_name;

	/**
	 * Returns the list of states for FR.
	 *
	 * @since 2.0.11
	 * @return array The list of state/provinces for the country.
	 */
	protected function states() {

		return array(
			'75' => __('Paris', 'wp-ultimo'),
			'WF-AL' => __('Alo', 'wp-ultimo'),
			'A' => __('Alsace', 'wp-ultimo'),
			'B' => __('Aquitaine', 'wp-ultimo'),
			'C' => __('Auvergne', 'wp-ultimo'),
			'ARA' => __('Auvergne-Rhône-Alpes', 'wp-ultimo'),
			'BFC' => __('Bourgogne-Franche-Comté', 'wp-ultimo'),
			'BRE' => __('Brittany', 'wp-ultimo'),
			'D' => __('Burgundy', 'wp-ultimo'),
			'CVL' => __('Centre-Val de Loire', 'wp-ultimo'),
			'G' => __('Champagne-Ardenne', 'wp-ultimo'),
			'COR' => __('Corsica', 'wp-ultimo'),
			'I' => __('Franche-Comté', 'wp-ultimo'),
			'GF' => __('French Guiana', 'wp-ultimo'),
			'PF' => __('French Polynesia', 'wp-ultimo'),
			'GES' => __('Grand Est', 'wp-ultimo'),
			'GP' => __('Guadeloupe', 'wp-ultimo'),
			'HDF' => __('Hauts-de-France', 'wp-ultimo'),
			'K' => __('Languedoc-Roussillon', 'wp-ultimo'),
			'L' => __('Limousin', 'wp-ultimo'),
			'M' => __('Lorraine', 'wp-ultimo'),
			'P' => __('Lower Normandy', 'wp-ultimo'),
			'MQ' => __('Martinique', 'wp-ultimo'),
			'YT' => __('Mayotte', 'wp-ultimo'),
			'O' => __('Nord-Pas-de-Calais', 'wp-ultimo'),
			'NOR' => __('Normandy', 'wp-ultimo'),
			'NAQ' => __('Nouvelle-Aquitaine', 'wp-ultimo'),
			'OCC' => __('Occitania', 'wp-ultimo'),
			'PDL' => __('Pays de la Loire', 'wp-ultimo'),
			'S' => __('Picardy', 'wp-ultimo'),
			'T' => __('Poitou-Charentes', 'wp-ultimo'),
			'PAC' => __("Provence-Alpes-Côte d'Azur", 'wp-ultimo'),
			'V' => __('Rhône-Alpes', 'wp-ultimo'),
			'RE' => __('Réunion', 'wp-ultimo'),
			'BL' => __('Saint Barthélemy', 'wp-ultimo'),
			'MF' => __('Saint Martin', 'wp-ultimo'),
			'PM' => __('Saint Pierre and Miquelon', 'wp-ultimo'),
			'WF-SG' => __('Sigave', 'wp-ultimo'),
			'Q' => __('Upper Normandy', 'wp-ultimo'),
			'WF-UV' => __('Uvea', 'wp-ultimo'),
			'WF' => __('Wallis and Futuna', 'wp-ultimo'),
			'IDF' => __('Île-de-France', 'wp-ultimo'),
		);

	} // end states;

} // end class Country_FR;
