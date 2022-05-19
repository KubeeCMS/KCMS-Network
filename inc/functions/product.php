<?php
/**
 * Product Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Product;

/**
 * Returns a product.
 *
 * @since 2.0.0
 *
 * @param int|string $product_id_or_slug The ID or slug of the product.
 * @return \WP_Ultimo\Models\Product|false
 */
function wu_get_product($product_id_or_slug) {

	if (is_numeric($product_id_or_slug) === false) {

		return wu_get_product_by_slug($product_id_or_slug);

	} // end if;

	return \WP_Ultimo\Models\Product::get_by_id($product_id_or_slug);

} // end wu_get_product;

/**
 * Queries products.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Product[]
 */
function wu_get_products($query = array()) {

	return \WP_Ultimo\Models\Product::query($query);

} // end wu_get_products;

/**
 * Queries plans.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Product[]
 */
function wu_get_plans($query = array()) {

	$query['type'] = 'plan';

	/*
	 * Fixes the order.
	 */
	$query['order']   = 'ASC';
	$query['orderby'] = 'list_order';

	return \WP_Ultimo\Models\Product::query($query);

} // end wu_get_plans;

/**
 * Returns the list of plans as ID -> Name.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_plans_as_options() {

	$options = array();

	foreach (wu_get_plans() as $plan) {

		$options[$plan->get_id()] = $plan->get_name();

	} // end foreach;

	return $options;

} // end wu_get_plans_as_options;

/**
 * Returns a product based on slug.
 *
 * @since 2.0.0
 *
 * @param string $product_slug The slug of the product.
 * @return \WP_Ultimo\Models\Product|false
 */
function wu_get_product_by_slug($product_slug) {

	return \WP_Ultimo\Models\Product::get_by('slug', $product_slug);

} // end wu_get_product_by_slug;

/**
 * Returns a single product defined by a particular column and value.
 *
 * @since 2.0.0
 *
 * @param string $column The column name.
 * @param mixed  $value The column value.
 * @return \WP_Ultimo\Models\Membership|false
 */
function wu_get_product_by($column, $value) {

	return \WP_Ultimo\Models\Product::get_by($column, $value);

} // end wu_get_product_by;

/**
 * Creates a new product.
 *
 * @since 2.0.0
 *
 * @param array $product_data Product data.
 * @return \WP_Error|\WP_Ultimo\Models\Product
 */
function wu_create_product($product_data) {

	$product_data = wp_parse_args($product_data, array(
		'name'                => false,
		'description'         => false,
		'currency'            => false,
		'pricing_type'        => false,
		'setup_fee'           => false,
		'parent_id'           => 0,
		'slug'                => false,
		'recurring'           => false,
		'trial_duration'      => 0,
		'trial_duration_unit' => 'day',
		'duration'            => 1,
		'duration_unit'       => 'day',
		'amount'              => false,
		'billing_cycles'      => false,
		'list_order'          => false,
		'active'              => false,
		'type'                => false,
		'featured_image_id'   => 0,
		'list_order'          => 0,
		'date_created'        => wu_get_current_time('mysql', true),
		'date_modified'       => wu_get_current_time('mysql', true),
		'migrated_from_id'    => 0,
		'meta'                => array(),
		'available_addons'    => array(),
		'group'               => '',
	));

	$product = new Product($product_data);

	$saved = $product->save();

	return is_wp_error($saved) ? $saved : $product;

} // end wu_create_product;

/**
 * Returns a list of available product groups.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_product_groups() {

	global $wpdb;

	$query = "SELECT DISTINCT `product_group` FROM {$wpdb->base_prefix}wu_products WHERE `product_group` <> ''";

	$results = array_column($wpdb->get_results($query, ARRAY_A), 'product_group'); // phpcs:ignore

	return array_combine($results, $results);

} // end wu_get_product_groups;

/**
 * Takes a list of product objects and separates them into plan and addons.
 *
 * @since 2.0.0
 *
 * @param Product[] $products List of products.
 * @return array first element is the first plan found, the second is an array with all the other products.
 */
function wu_segregate_products($products) {

	$results = array(false, array());

	foreach ($products as $product) {

		if (is_a($product, \WP_Ultimo\Models\Product::class) === false) {

			$product = wu_get_product($product);

			if (!$product) {

				continue;

			} // end if;

		} // end if;

		if ($product->get_type() === 'plan' && $results[0] === false) {

			$results[0] = $product;

		} else {

			$results[1][] = $product;

		} // end if;

	} // end foreach;

	return $results;

} // end wu_segregate_products;
