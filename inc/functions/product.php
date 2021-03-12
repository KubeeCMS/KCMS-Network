<?php
/**
 * Products Functions
 *
 * Public APIs to load and deal with WP Ultimo product.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Product
 * @version     2.0.0
 */

use \WP_Ultimo\Models\Product;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns a product.
 *
 * @since 2.0.0
 *
 * @param int $product_id The ID of the product.
 * @return \WP_Ultimo\Models\Product|false
 */
function wu_get_product($product_id) {

	return \WP_Ultimo\Models\Product::get_by_id($product_id);

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
		'feature_image_id'    => false,
		'list_order'          => 0,
		'date_created'        => current_time('mysql'),
		'date_modified'       => current_time('mysql'),
		'migrated_from_id'    => 0,
		'meta'                => array(),
	));

	$product = new Product($product_data);

	$saved = $product->save();

	return is_wp_error($saved) ? $saved : $product;

} // end wu_create_product;
