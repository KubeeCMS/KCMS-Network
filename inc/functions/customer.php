<?php
/**
 * Customers Functions
 *
 * Public APIs to load and deal with WP Ultimo customers.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Customer
 * @version     2.0.0
 */

use \WP_Ultimo\Models\Customer;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns a customer.
 *
 * @since 2.0.0
 *
 * @param int $customer_id The id of the customer. This is not the user ID.
 * @return \WP_Ultimo\Models\Customer|false
 */
function wu_get_customer($customer_id) {

	return \WP_Ultimo\Models\Customer::get_by_id($customer_id);

} // end wu_get_customer;

/**
 * Returns a single customer defined by a particular column and value.
 *
 * @since 2.0.0
 *
 * @param string $column The column name.
 * @param mixed  $value The column value.
 * @return \WP_Ultimo\Models\Customer|false
 */
function wu_get_customer_by($column, $value) {

	return \WP_Ultimo\Models\Customer::get_by($column, $value);

} // end wu_get_customer_by;

/**
 * Queries customers.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Customer[]
 */
function wu_get_customers($query = array()) {

	if (!empty($query['search'])) {

		$user_ids = get_users(array(
			'blog_id' => 0,
			'number'  => -1,
			'search'  => '*' . $query['search'] . '*',
			'fields'  => 'ids',
		));

		if (!empty($user_ids)) {

			$query['user_id__in'] = $user_ids;

			unset($query['search']);

		} // end if;

	} // end if;

	/*
	 * Force search limit to customers only.
	 */
	$query['type'] = 'customer';

	return \WP_Ultimo\Models\Customer::query($query);

} // end wu_get_customers;

/**
 * Returns a customer based on user_id.
 *
 * @since 2.0.0
 *
 * @param int $user_id The ID of the WP User associated with that customer.
 * @return \WP_Ultimo\Models\Customer|false
 */
function wu_get_customer_by_user_id($user_id) {

	return \WP_Ultimo\Models\Customer::get_by('user_id', $user_id);

} // end wu_get_customer_by_user_id;

/**
 * Returns the current customer.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Customer|false
 */
function wu_get_current_customer() {

	return wu_get_customer_by_user_id(get_current_user_id());

} // end wu_get_current_customer;

/**
 * Creates a new customer.
 *
 * Check the wp_parse_args below to see what parameters are necessary.
 * If the use_id is not passed, we try to create a new WP User.
 *
 * @since 2.0.0
 *
 * @param array $customer_data Customer attributes.
 * @return \WP_Error|\WP_Ultimo\Models\Customer
 */
function wu_create_customer($customer_data) {

	$customer_data = wp_parse_args($customer_data, array(
		'user_id'            => false,
		'email'              => false,
		'username'           => false,
		'password'           => false,
		'vip'                => false,
		'ip'                 => false,
		'email_verification' => 'none',
		'meta'               => array(),
		'date_registered'    => current_time('mysql'),
		'date_modified'      => current_time('mysql'),
	));

	$user = get_user_by('email', $customer_data['email']);

	if (!$user) {

		$user = get_user_by('ID', $customer_data['user_id']);

	} // end if;

	if (!$user) {

		$user_id = wpmu_create_user($customer_data['username'], $customer_data['password'], $customer_data['email']);

		if ($user_id === false) {

			return new \WP_Error('user', __('We were not able to create a new user.', 'wp-ultimo'), $customer_data);

		} // end if;

	} else {

		$user_id = $user->ID;

	} // end if;

	$customer = new Customer(array(
		'user_id'            => $user_id,
		'email_verification' => $customer_data['email_verification'],
		'meta'               => $customer_data['meta'],
		'date_registered'    => $customer_data['date_registered'],
		'type'               => 'customer',
	));

	$saved = $customer->save();

	return is_wp_error($saved) ? $saved : $customer;

} // end wu_create_customer;

/**
 * Searches for a existing gateway customer among the customer memberships.
 *
 * @since 2.0.0
 *
 * @param int   $customer_id The local (wu) customer id.
 * @param array $allowed_gateways The list of allowed gateways to search.
 * @return string
 */
function wu_get_customer_gateway_id($customer_id, $allowed_gateways = array()) {

	$memberships = wu_get_memberships(array(
		'customer_id'                 => absint($customer_id),
		'gateway__in'                 => $allowed_gateways,
		'number'                      => 1,
		'gateway_customer_id__not_in' => array(''),
	));

	return !empty($memberships) ? $memberships[0]->get_gateway_customer_id() : '';

} // end wu_get_customer_gateway_id;
