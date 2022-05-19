<?php
/**
 * Tax-related Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Tax\Tax;

/**
 * Checks if WP Ultimo should collect taxes.
 *
 * @since 2.0.0
 * @return bool
 */
function wu_should_collect_taxes() {

	return (bool) wu_get_setting('enable_taxes', false);

} // end wu_should_collect_taxes;

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
 * @param boolean $inclusive If we should calculate taxes as inclusive.
 * @return float|string
 */
function wu_get_tax_amount($base_price, $amount, $type, $format = true, $inclusive = false) {
	/*
	 * If the tax is an absolute (type == absolute) value, there's nothing
	 * to do, pretty much.
	 */
	$tax_total = $amount;

	if ($type === 'percentage') {

		if (!$inclusive) {

			/**
			 * Exclusive tax
			 *
			 * Calculates tax to be ADDED to the order.
			 */
			$tax_total = $base_price * ($amount / 100);

		} else {

			/**
			 * Inclusive tax
			 *
			 * Calculates the tax value inside the total price.
			 */
			$tax_total = $base_price - ($base_price / (1 + ($amount / 100)));

		} // end if;

	} // end if;

	/*
	 * Return results
	 */

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
 * @param string $state The state to filter by.
 * @param string $city The city to filter by.
 * @return array
 */
function wu_get_applicable_tax_rates($country, $tax_category = 'default', $state = '*', $city = '*') {

	if (!$country) {

		return array();

	} // end if;

	$tax_category = wu_get_tax_category($tax_category);

	$results = array();

	foreach ($tax_category['rates'] as &$rate) {
		/*
 		 * Step 0: Prepare.
		 */
		$rate['state'] = explode(',', $rate['state']);
		$rate['city']  = explode(',', $rate['city']);

		$keys_of_interest = array_intersect_key($rate, array(
			'country' => 1,
			'state'   => 1,
			'city'    => 1,
		));

		$priority = 0;

		foreach ($keys_of_interest as $key => $value) {

			$value = is_array($value) ? array_filter($value) : trim($value);

			/*
			 * Step 1: The country.
			 */
			if ($key === 'country' && $rate['country'] === $country) {

				$priority += 10;

			} // end if;

			/*
			 * Step 2: The state / province
			 */
			if ($key === 'state' && $state !== '*') {

				if (in_array($state, $value, true)) {

					$priority += 1;

				} elseif (empty($value) || in_array('*', $value, true)) {

					$priority += 0.5;

				} // end if;

			} // end if;

			/*
			 * Step 3: The city
			 */
			if ($key === 'city' && $city !== '*') {

				if (in_array($city, $value, true)) {
					/*
					 * If it's a full match, gives 1 point.
					 */
					$priority += 1;

				} elseif (empty($value) || in_array('*', $value, true)) {
					/*
					 * If it is a wildcard, award half a point.
					 */
					$priority += 0.5;

				} // end if;

			} // end if;

		} // end foreach;

		if ($priority >= 10) {

			$rate['order'] = $priority;

			$results[$rate['id']] = $rate;

		} // end if;

	} // end foreach;

	uasort($results, 'wu_sort_by_order');

	return array_values($results);

} // end wu_get_applicable_tax_rates;
