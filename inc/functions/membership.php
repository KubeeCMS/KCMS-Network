<?php
/**
 * Memberships Functions
 *
 * Public APIs to load and deal with WP Ultimo membership.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Membership
 * @version     2.0.0
 */

use \WP_Ultimo\Models\Membership;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns a membership.
 *
 * @since 2.0.0
 *
 * @param int $membership_id The ID of the membership.
 * @return \WP_Ultimo\Models\Membership|false
 */
function wu_get_membership($membership_id) {

	return \WP_Ultimo\Models\Membership::get_by_id($membership_id);

} // end wu_get_membership;

/**
 * Returns a single membership defined by a particular column and value.
 *
 * @since 2.0.0
 *
 * @param string $column The column name.
 * @param mixed  $value The column value.
 * @return \WP_Ultimo\Models\Membership|false
 */
function wu_get_membership_by($column, $value) {

	return \WP_Ultimo\Models\Membership::get_by($column, $value);

} // end wu_get_membership_by;

/**
 * Gets a membership based on the hash.
 *
 * @since 2.0.0
 *
 * @param string $hash The hash for the membership.
 * @return \WP_Ultimo\Models\Membership|false
 */
function wu_get_membership_by_hash($hash) {

	return \WP_Ultimo\Models\Membership::get_by_hash($hash);

} // end wu_get_membership_by_hash;

/**
 * Queries memberships.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Membership[]
 */
function wu_get_memberships($query = array()) {

	if (!empty($query['search'])) {

		$customer_ids = wu_get_customers(array(
			'search' => $query['search'],
			'fields' => 'ids',
		));

		if (!empty($customer_ids)) {

			$query['customer_id__in'] = $customer_ids;

			unset($query['search']);

		} // end if;

	} // end if;

	return \WP_Ultimo\Models\Membership::query($query);

} // end wu_get_memberships;

/**
 * Creates a new membership.
 *
 * @since 2.0.0
 *
 * @param array $membership_data Membership data.
 * @return \WP_Error|\WP_Ultimo\Models\Membership
 */
function wu_create_membership($membership_data) {
	/*
	* Why do we use shortcode atts here?
	* Shortcode atts clean the array from not-allowed keys, so we don't need to worry much.
	*/
	$membership_data = shortcode_atts(array(
		'customer_id'             => false,
		'user_id'                 => false,
		'migrated_from_id'        => false,
		'plan_id'                 => false,
		'addon_products'          => false,
		'currency'                => false,
		'initial_amount'          => false,
		'recurring'               => false,
		'duration'                => false,
		'duration_unit'           => false,
		'amount'                  => false,
		'auto_renew'              => false,
		'times_billed'            => false,
		'billing_cycles'          => false,
		'gateway_customer_id'     => false,
		'gateway_subscription_id' => false,
		'gateway'                 => false,
		'signup_method'           => false,
		'subscription_key'        => false,
		'upgraded_from'           => false,
		'disabled'                => false,
		'status'                  => 'pending',
		'date_created'            => current_time('mysql'),
		'date_modified'           => current_time('mysql'),
		'date_expiration'         => false,
		'skip_validation'         => false,
	), $membership_data);

	$membership = new Membership($membership_data);

	$saved = $membership->save();

	return is_wp_error($saved) ? $saved : $membership;

} // end wu_create_membership;

/**
 * Get all customers with a specific membership using the product_id as reference.
 *
 * @since 2.0.0
 *
 * @param array $product_id Membership product.
 * @return array    With all users within the membership.
 */
function wu_get_membership_customers($product_id) {

	$memberships = wu_get_memberships(array('plan_id' => $product_id));

	$customers = array();

	foreach ($memberships as $key => $membership) {

		if ($membership->get_plan_id() == $product_id) {

			array_push($customers, $membership->get_customer_id());

		} // end if;

	} // end foreach;

	return $customers;

} // end wu_get_membership_customers;
