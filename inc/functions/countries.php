<?php
/**
 * Country Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns the list of countries.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_countries() {

	return apply_filters('wu_get_countries', array(
		'AF' => __('Afghanistan', 'wp-ultimo'),
		'AX' => __('&#197;land Islands', 'wp-ultimo'),
		'AL' => __('Albania', 'wp-ultimo'),
		'DZ' => __('Algeria', 'wp-ultimo'),
		'AS' => __('American Samoa', 'wp-ultimo'),
		'AD' => __('Andorra', 'wp-ultimo'),
		'AO' => __('Angola', 'wp-ultimo'),
		'AI' => __('Anguilla', 'wp-ultimo'),
		'AQ' => __('Antarctica', 'wp-ultimo'),
		'AG' => __('Antigua and Barbuda', 'wp-ultimo'),
		'AR' => __('Argentina', 'wp-ultimo'),
		'AM' => __('Armenia', 'wp-ultimo'),
		'AW' => __('Aruba', 'wp-ultimo'),
		'AU' => __('Australia', 'wp-ultimo'),
		'AT' => __('Austria', 'wp-ultimo'),
		'AZ' => __('Azerbaijan', 'wp-ultimo'),
		'BS' => __('Bahamas', 'wp-ultimo'),
		'BH' => __('Bahrain', 'wp-ultimo'),
		'BD' => __('Bangladesh', 'wp-ultimo'),
		'BB' => __('Barbados', 'wp-ultimo'),
		'BY' => __('Belarus', 'wp-ultimo'),
		'BE' => __('Belgium', 'wp-ultimo'),
		'PW' => __('Belau', 'wp-ultimo'),
		'BZ' => __('Belize', 'wp-ultimo'),
		'BJ' => __('Benin', 'wp-ultimo'),
		'BM' => __('Bermuda', 'wp-ultimo'),
		'BT' => __('Bhutan', 'wp-ultimo'),
		'BO' => __('Bolivia', 'wp-ultimo'),
		'BQ' => __('Bonaire, Saint Eustatius and Saba', 'wp-ultimo'),
		'BA' => __('Bosnia and Herzegovina', 'wp-ultimo'),
		'BW' => __('Botswana', 'wp-ultimo'),
		'BV' => __('Bouvet Island', 'wp-ultimo'),
		'BR' => __('Brazil', 'wp-ultimo'),
		'IO' => __('British Indian Ocean Territory', 'wp-ultimo'),
		'VG' => __('British Virgin Islands', 'wp-ultimo'),
		'BN' => __('Brunei', 'wp-ultimo'),
		'BG' => __('Bulgaria', 'wp-ultimo'),
		'BF' => __('Burkina Faso', 'wp-ultimo'),
		'BI' => __('Burundi', 'wp-ultimo'),
		'KH' => __('Cambodia', 'wp-ultimo'),
		'CM' => __('Cameroon', 'wp-ultimo'),
		'CA' => __('Canada', 'wp-ultimo'),
		'CV' => __('Cape Verde', 'wp-ultimo'),
		'KY' => __('Cayman Islands', 'wp-ultimo'),
		'CF' => __('Central African Republic', 'wp-ultimo'),
		'TD' => __('Chad', 'wp-ultimo'),
		'CL' => __('Chile', 'wp-ultimo'),
		'CN' => __('China', 'wp-ultimo'),
		'CX' => __('Christmas Island', 'wp-ultimo'),
		'CC' => __('Cocos (Keeling) Islands', 'wp-ultimo'),
		'CO' => __('Colombia', 'wp-ultimo'),
		'KM' => __('Comoros', 'wp-ultimo'),
		'CG' => __('Congo (Brazzaville)', 'wp-ultimo'),
		'CD' => __('Congo (Kinshasa)', 'wp-ultimo'),
		'CK' => __('Cook Islands', 'wp-ultimo'),
		'CR' => __('Costa Rica', 'wp-ultimo'),
		'HR' => __('Croatia', 'wp-ultimo'),
		'CU' => __('Cuba', 'wp-ultimo'),
		'CW' => __('Cura&ccedil;ao', 'wp-ultimo'),
		'CY' => __('Cyprus', 'wp-ultimo'),
		'CZ' => __('Czech Republic', 'wp-ultimo'),
		'DK' => __('Denmark', 'wp-ultimo'),
		'DJ' => __('Djibouti', 'wp-ultimo'),
		'DM' => __('Dominica', 'wp-ultimo'),
		'DO' => __('Dominican Republic', 'wp-ultimo'),
		'EC' => __('Ecuador', 'wp-ultimo'),
		'EG' => __('Egypt', 'wp-ultimo'),
		'SV' => __('El Salvador', 'wp-ultimo'),
		'GQ' => __('Equatorial Guinea', 'wp-ultimo'),
		'ER' => __('Eritrea', 'wp-ultimo'),
		'EE' => __('Estonia', 'wp-ultimo'),
		'ET' => __('Ethiopia', 'wp-ultimo'),
		'FK' => __('Falkland Islands', 'wp-ultimo'),
		'FO' => __('Faroe Islands', 'wp-ultimo'),
		'FJ' => __('Fiji', 'wp-ultimo'),
		'FI' => __('Finland', 'wp-ultimo'),
		'FR' => __('France', 'wp-ultimo'),
		'GF' => __('French Guiana', 'wp-ultimo'),
		'PF' => __('French Polynesia', 'wp-ultimo'),
		'TF' => __('French Southern Territories', 'wp-ultimo'),
		'GA' => __('Gabon', 'wp-ultimo'),
		'GM' => __('Gambia', 'wp-ultimo'),
		'GE' => __('Georgia', 'wp-ultimo'),
		'DE' => __('Germany', 'wp-ultimo'),
		'GH' => __('Ghana', 'wp-ultimo'),
		'GI' => __('Gibraltar', 'wp-ultimo'),
		'GR' => __('Greece', 'wp-ultimo'),
		'GL' => __('Greenland', 'wp-ultimo'),
		'GD' => __('Grenada', 'wp-ultimo'),
		'GP' => __('Guadeloupe', 'wp-ultimo'),
		'GU' => __('Guam', 'wp-ultimo'),
		'GT' => __('Guatemala', 'wp-ultimo'),
		'GG' => __('Guernsey', 'wp-ultimo'),
		'GN' => __('Guinea', 'wp-ultimo'),
		'GW' => __('Guinea-Bissau', 'wp-ultimo'),
		'GY' => __('Guyana', 'wp-ultimo'),
		'HT' => __('Haiti', 'wp-ultimo'),
		'HM' => __('Heard Island and McDonald Islands', 'wp-ultimo'),
		'HN' => __('Honduras', 'wp-ultimo'),
		'HK' => __('Hong Kong', 'wp-ultimo'),
		'HU' => __('Hungary', 'wp-ultimo'),
		'IS' => __('Iceland', 'wp-ultimo'),
		'IN' => __('India', 'wp-ultimo'),
		'ID' => __('Indonesia', 'wp-ultimo'),
		'IR' => __('Iran', 'wp-ultimo'),
		'IQ' => __('Iraq', 'wp-ultimo'),
		'IE' => __('Ireland', 'wp-ultimo'),
		'IM' => __('Isle of Man', 'wp-ultimo'),
		'IL' => __('Israel', 'wp-ultimo'),
		'IT' => __('Italy', 'wp-ultimo'),
		'CI' => __('Ivory Coast', 'wp-ultimo'),
		'JM' => __('Jamaica', 'wp-ultimo'),
		'JP' => __('Japan', 'wp-ultimo'),
		'JE' => __('Jersey', 'wp-ultimo'),
		'JO' => __('Jordan', 'wp-ultimo'),
		'KZ' => __('Kazakhstan', 'wp-ultimo'),
		'KE' => __('Kenya', 'wp-ultimo'),
		'KI' => __('Kiribati', 'wp-ultimo'),
		'KW' => __('Kuwait', 'wp-ultimo'),
		'KG' => __('Kyrgyzstan', 'wp-ultimo'),
		'LA' => __('Laos', 'wp-ultimo'),
		'LV' => __('Latvia', 'wp-ultimo'),
		'LB' => __('Lebanon', 'wp-ultimo'),
		'LS' => __('Lesotho', 'wp-ultimo'),
		'LR' => __('Liberia', 'wp-ultimo'),
		'LY' => __('Libya', 'wp-ultimo'),
		'LI' => __('Liechtenstein', 'wp-ultimo'),
		'LT' => __('Lithuania', 'wp-ultimo'),
		'LU' => __('Luxembourg', 'wp-ultimo'),
		'MO' => __('Macao S.A.R., China', 'wp-ultimo'),
		'MK' => __('Macedonia', 'wp-ultimo'),
		'MG' => __('Madagascar', 'wp-ultimo'),
		'MW' => __('Malawi', 'wp-ultimo'),
		'MY' => __('Malaysia', 'wp-ultimo'),
		'MV' => __('Maldives', 'wp-ultimo'),
		'ML' => __('Mali', 'wp-ultimo'),
		'MT' => __('Malta', 'wp-ultimo'),
		'MH' => __('Marshall Islands', 'wp-ultimo'),
		'MQ' => __('Martinique', 'wp-ultimo'),
		'MR' => __('Mauritania', 'wp-ultimo'),
		'MU' => __('Mauritius', 'wp-ultimo'),
		'YT' => __('Mayotte', 'wp-ultimo'),
		'MX' => __('Mexico', 'wp-ultimo'),
		'FM' => __('Micronesia', 'wp-ultimo'),
		'MD' => __('Moldova', 'wp-ultimo'),
		'MC' => __('Monaco', 'wp-ultimo'),
		'MN' => __('Mongolia', 'wp-ultimo'),
		'ME' => __('Montenegro', 'wp-ultimo'),
		'MS' => __('Montserrat', 'wp-ultimo'),
		'MA' => __('Morocco', 'wp-ultimo'),
		'MZ' => __('Mozambique', 'wp-ultimo'),
		'MM' => __('Myanmar', 'wp-ultimo'),
		'NA' => __('Namibia', 'wp-ultimo'),
		'NR' => __('Nauru', 'wp-ultimo'),
		'NP' => __('Nepal', 'wp-ultimo'),
		'NL' => __('Netherlands', 'wp-ultimo'),
		'NC' => __('New Caledonia', 'wp-ultimo'),
		'NZ' => __('New Zealand', 'wp-ultimo'),
		'NI' => __('Nicaragua', 'wp-ultimo'),
		'NE' => __('Niger', 'wp-ultimo'),
		'NG' => __('Nigeria', 'wp-ultimo'),
		'NU' => __('Niue', 'wp-ultimo'),
		'NF' => __('Norfolk Island', 'wp-ultimo'),
		'MP' => __('Northern Mariana Islands', 'wp-ultimo'),
		'KP' => __('North Korea', 'wp-ultimo'),
		'NO' => __('Norway', 'wp-ultimo'),
		'OM' => __('Oman', 'wp-ultimo'),
		'PK' => __('Pakistan', 'wp-ultimo'),
		'PS' => __('Palestinian Territory', 'wp-ultimo'),
		'PA' => __('Panama', 'wp-ultimo'),
		'PG' => __('Papua New Guinea', 'wp-ultimo'),
		'PY' => __('Paraguay', 'wp-ultimo'),
		'PE' => __('Peru', 'wp-ultimo'),
		'PH' => __('Philippines', 'wp-ultimo'),
		'PN' => __('Pitcairn', 'wp-ultimo'),
		'PL' => __('Poland', 'wp-ultimo'),
		'PT' => __('Portugal', 'wp-ultimo'),
		'PR' => __('Puerto Rico', 'wp-ultimo'),
		'QA' => __('Qatar', 'wp-ultimo'),
		'RE' => __('Reunion', 'wp-ultimo'),
		'RO' => __('Romania', 'wp-ultimo'),
		'RU' => __('Russia', 'wp-ultimo'),
		'RW' => __('Rwanda', 'wp-ultimo'),
		'BL' => __('Saint Barth&eacute;lemy', 'wp-ultimo'),
		'SH' => __('Saint Helena', 'wp-ultimo'),
		'KN' => __('Saint Kitts and Nevis', 'wp-ultimo'),
		'LC' => __('Saint Lucia', 'wp-ultimo'),
		'MF' => __('Saint Martin (French part)', 'wp-ultimo'),
		'SX' => __('Saint Martin (Dutch part)', 'wp-ultimo'),
		'PM' => __('Saint Pierre and Miquelon', 'wp-ultimo'),
		'VC' => __('Saint Vincent and the Grenadines', 'wp-ultimo'),
		'SM' => __('San Marino', 'wp-ultimo'),
		'ST' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'wp-ultimo'),
		'SA' => __('Saudi Arabia', 'wp-ultimo'),
		'SN' => __('Senegal', 'wp-ultimo'),
		'RS' => __('Serbia', 'wp-ultimo'),
		'SC' => __('Seychelles', 'wp-ultimo'),
		'SL' => __('Sierra Leone', 'wp-ultimo'),
		'SG' => __('Singapore', 'wp-ultimo'),
		'SK' => __('Slovakia', 'wp-ultimo'),
		'SI' => __('Slovenia', 'wp-ultimo'),
		'SB' => __('Solomon Islands', 'wp-ultimo'),
		'SO' => __('Somalia', 'wp-ultimo'),
		'ZA' => __('South Africa', 'wp-ultimo'),
		'GS' => __('South Georgia/Sandwich Islands', 'wp-ultimo'),
		'KR' => __('South Korea', 'wp-ultimo'),
		'SS' => __('South Sudan', 'wp-ultimo'),
		'ES' => __('Spain', 'wp-ultimo'),
		'LK' => __('Sri Lanka', 'wp-ultimo'),
		'SD' => __('Sudan', 'wp-ultimo'),
		'SR' => __('Suriname', 'wp-ultimo'),
		'SJ' => __('Svalbard and Jan Mayen', 'wp-ultimo'),
		'SZ' => __('Swaziland', 'wp-ultimo'),
		'SE' => __('Sweden', 'wp-ultimo'),
		'CH' => __('Switzerland', 'wp-ultimo'),
		'SY' => __('Syria', 'wp-ultimo'),
		'TW' => __('Taiwan', 'wp-ultimo'),
		'TJ' => __('Tajikistan', 'wp-ultimo'),
		'TZ' => __('Tanzania', 'wp-ultimo'),
		'TH' => __('Thailand', 'wp-ultimo'),
		'TL' => __('Timor-Leste', 'wp-ultimo'),
		'TG' => __('Togo', 'wp-ultimo'),
		'TK' => __('Tokelau', 'wp-ultimo'),
		'TO' => __('Tonga', 'wp-ultimo'),
		'TT' => __('Trinidad and Tobago', 'wp-ultimo'),
		'TN' => __('Tunisia', 'wp-ultimo'),
		'TR' => __('Turkey', 'wp-ultimo'),
		'TM' => __('Turkmenistan', 'wp-ultimo'),
		'TC' => __('Turks and Caicos Islands', 'wp-ultimo'),
		'TV' => __('Tuvalu', 'wp-ultimo'),
		'UG' => __('Uganda', 'wp-ultimo'),
		'UA' => __('Ukraine', 'wp-ultimo'),
		'AE' => __('United Arab Emirates', 'wp-ultimo'),
		'GB' => __('United Kingdom (UK)', 'wp-ultimo'),
		'US' => __('United States (US)', 'wp-ultimo'),
		'UM' => __('United States (US) Minor Outlying Islands', 'wp-ultimo'),
		'VI' => __('United States (US) Virgin Islands', 'wp-ultimo'),
		'UY' => __('Uruguay', 'wp-ultimo'),
		'UZ' => __('Uzbekistan', 'wp-ultimo'),
		'VU' => __('Vanuatu', 'wp-ultimo'),
		'VA' => __('Vatican', 'wp-ultimo'),
		'VE' => __('Venezuela', 'wp-ultimo'),
		'VN' => __('Vietnam', 'wp-ultimo'),
		'WF' => __('Wallis and Futuna', 'wp-ultimo'),
		'EH' => __('Western Sahara', 'wp-ultimo'),
		'WS' => __('Samoa', 'wp-ultimo'),
		'YE' => __('Yemen', 'wp-ultimo'),
		'ZM' => __('Zambia', 'wp-ultimo'),
		'ZW' => __('Zimbabwe', 'wp-ultimo'),
	));

} // end wu_get_countries;

/**
 * Returns the list of countries with an additional empty state option.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_countries_as_options() {

	return array_merge(array(
		'' => __('Select Country', 'wp-ultimo'),
	), wu_get_countries());

} // end wu_get_countries_as_options;

/**
 * Returns the country object.
 *
 * @since 2.0.11
 *
 * @param string      $country_code Two-letter country ISO code.
 * @param string|null $name The country name.
 * @param array       $fallback_attributes Fallback attributes if the country class is not present.
 * @return \WP_Ultimo\Country\Country
 */
function wu_get_country($country_code, $name = null, $fallback_attributes = array()) {

	$country_code = strtoupper($country_code);

	$country_class = "\\WP_Ultimo\\Country\\Country_{$country_code}";

	if (class_exists($country_class)) {

		return $country_class::get_instance();

	} // end if;

	return \WP_Ultimo\Country\Country_Default::build($country_code, $name, $fallback_attributes);

} // end wu_get_country;

/**
 * Get the state list for a country.
 *
 * @since 2.0.12
 *
 * @param string $country_code The country code.
 * @param string $key_name The name to use for the key entry.
 * @param string $value_name The name to use for the value entry.
 * @return array
 */
function wu_get_country_states($country_code, $key_name = 'id', $value_name = 'value') {

	static $state_options = array();

	$options = array();

	$cache = wu_get_isset($state_options, $country_code, false);

	if ($cache) {

		$options = $cache;

	} else {

		$country = wu_get_country($country_code);

		$state_options[$country_code] = $country->get_states_as_options(false);

		$options = $state_options[$country_code];

	} // end if;

	if (empty($key_name)) {

		return $options;

	} // end if;

	return wu_key_map_to_array($options, $key_name, $value_name);

} // end wu_get_country_states;

/**
 * Get cities for a collection of states of a country.
 *
 * @since 2.0.11
 *
 * @param string $country_code The country code.
 * @param array  $states The list of state codes to retrieve.
 * @param string $key_name The name to use for the key entry.
 * @param string $value_name The name to use for the value entry.
 * @return array
 */
function wu_get_country_cities($country_code, $states, $key_name = 'id', $value_name = 'value') {

	static $city_options = array();

	$states = (array) $states;

	$options = array();

	foreach ($states as $state_code) {

		$cache = wu_get_isset($city_options, $state_code, false);

		if ($cache) {

			$options = array_merge($options, $cache);

		} else {

			$country = wu_get_country($country_code);

			$city_options[$state_code] = $country->get_cities_as_options($state_code, false);

			$options = array_merge($options, $city_options[$state_code]);

		} // end if;

	} // end foreach;

	if (empty($key_name)) {

		return $options;

	} // end if;

	return wu_key_map_to_array($options, $key_name, $value_name);

} // end wu_get_country_cities;

/**
 * Returns the country name for a given country code.
 *
 * @since 2.0.0
 *
 * @param string $country_code Country code.
 * @return string
 */
function wu_get_country_name($country_code) {

	$country_name = wu_get_isset(wu_get_countries(), $country_code, __('Not found', 'wp-ultimo'));

	return apply_filters('wu_get_country_name', $country_name, $country_code);

} // end wu_get_country_name;

/**
 * Get the list of countries and counts based on customers.
 *
 * @since 2.0.0
 * @param integer        $count The number of results to return.
 * @param boolean|string $start_date The start date.
 * @param boolean|string $end_date The end date.
 * @return array
 */
function wu_get_countries_of_customers($count = 10, $start_date = false, $end_date = false) {

	global $wpdb;

	$table_name          = "{$wpdb->base_prefix}wu_customermeta";
	$customer_table_name = "{$wpdb->base_prefix}wu_customers";

	$date_query = '';

	if ($start_date || $end_date) {

		$date_query = 'AND c.date_registered >= %s AND c.date_registered <= %s';

		$date_query = $wpdb->prepare($date_query, $start_date . ' 00:00:00', $end_date . " 23:59:59"); // phpcs:ignore

	} // end if;

	$query = "
		SELECT m.meta_value as country, COUNT(distinct c.id) as count
		FROM `{$table_name}` as m
		INNER JOIN `{$customer_table_name}` as c ON m.wu_customer_id = c.id
		WHERE m.meta_key = 'ip_country' AND m.meta_value != ''
		$date_query
		GROUP BY m.meta_value
		ORDER BY count DESC
		LIMIT %d
	";

	$query = $wpdb->prepare($query, $count); // phpcs:ignore

	$results = $wpdb->get_results($query); // phpcs:ignore

	$countries = array();

	foreach ($results as $result) {

		$countries[$result->country] = $result->count;

	} // end foreach;

	return $countries;

} // end wu_get_countries_of_customers;

/**
 * Get the list of countries and counts based on customers.
 *
 * @since 2.0.0
 * @param string         $country_code The country code.
 * @param integer        $count The number of results to return.
 * @param boolean|string $start_date The start date.
 * @param boolean|string $end_date The end date.
 * @return array
 */
function wu_get_states_of_customers($country_code, $count = 100, $start_date = false, $end_date = false) {

	global $wpdb;

	static $states = array();

	$table_name          = "{$wpdb->base_prefix}wu_customermeta";
	$customer_table_name = "{$wpdb->base_prefix}wu_customers";

	$date_query = '';

	if ($start_date || $end_date) {

		$date_query = 'AND c.date_registered >= %s AND c.date_registered <= %s';

		$date_query = $wpdb->prepare($date_query, $start_date . ' 00:00:00', $end_date . " 23:59:59"); // phpcs:ignore

	} // end if;

	$states = wu_get_country_states('BR', false);

	if (empty($states)) {

		return array();

	} // end if;

	$states_in = implode("','", array_keys($states));

	$query = "
		SELECT m.meta_value as state, COUNT(distinct c.id) as count
		FROM `{$table_name}` as m
		INNER JOIN `{$customer_table_name}` as c ON m.wu_customer_id = c.id
		WHERE m.meta_key = 'ip_state' AND m.meta_value IN ('$states_in')
		$date_query
		GROUP BY m.meta_value
		ORDER BY count DESC
		LIMIT %d
	";

	$query = $wpdb->prepare($query, $count); // phpcs:ignore

	$results = $wpdb->get_results($query); // phpcs:ignore

	if (empty($results)) {

		return array();

	} // end if;

	$_states = array();

	foreach ($results as $result) {

		$final_label = sprintf('%s (%s)', $states[$result->state], $result->state);

		$_states[$final_label] = absint($result->count);

	} // end foreach;

	return $_states;

} // end wu_get_states_of_customers;
