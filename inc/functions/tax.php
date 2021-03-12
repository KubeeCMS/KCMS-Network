<?php
/**
 * Tax Functions
 *
 * Public APIs to load and deal with WP Ultimo taxes.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Tax
 * @version     2.0.0
 */

use \WP_Ultimo\Tax\Tax;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns the tax categories.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_tax_categories() {

	return Tax::get_instance()->get_tax_rates();

} // end wu_get_tax_categories;

/**
 * Returns a given tax category
 *
 * @since 2.0.0
 * @param string $tax_category The tax category to retrieve.
 * @return array
 */
function wu_get_tax_category($tax_category = 'default') {

	$tax_categories = wu_get_tax_categories();

	return wu_get_isset($tax_categories, $tax_category, array(
		'rates' => array(),
	));

} // end wu_get_tax_category;

/**
 * Returns the tax categories as a slug => name array.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_tax_categories_as_options() {

	return array_map(function($item) {

		return $item['name'];

	}, wu_get_tax_categories());

} // end wu_get_tax_categories_as_options;

/**
 * Calculates the tax value.
 *
 * @since 2.0.0
 *
 * @param float   $base_price Original price to calculate based upon.
 * @param float   $amount Tax amount.
 * @param string  $type Type of the tax, can be percentage or absolute.
 * @param boolean $format If we should format the results or not.
 * @return float|string
 */
function wu_get_tax_amount($base_price, $amount, $type, $format = true) {

	if ($type === 'percentage') {

		$tax_total = $base_price * ($amount / 100);

	} elseif ($type === 'absolute') {

		$tax_total = $amount;

	} // end if;

	if (!$format) {

		return round($tax_total, 2);

	} // end if;

	return number_format((float) $tax_total, 2);

} // end wu_get_tax_amount;

/**
 * Searches for applicable tax rates based on the country.
 *
 * @todo This can be greatly improved and should support multiple rates
 * in the future.
 *
 * @since 2.0.0
 *
 * @param string $country The country to search for.
 * @param string $tax_category The tax category of the product.
 * @return array
 */
function wu_get_applicable_tax_rates($country, $tax_category = 'default') {

	if (!$country) {

		return array();

	} // end if;

	$tax_category = wu_get_tax_category($tax_category);

	$tax_rates = \WP_Ultimo\Dependencies\Arrch\Arrch::find($tax_category['rates'], array(
		'sort_key' => 'name',
		'where'    => array(
			array('country', '~', $country),
		),
	));

	return $tax_rates;

} // end wu_get_applicable_tax_rates;
