<?php
/**
 * Membership Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Membership;
use \WP_Ultimo\Database\Payments\Payment_Status;

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
		'migrated_from_id'        => 0,
		'plan_id'                 => false,
		'addon_products'          => false,
		'currency'                => false,
		'initial_amount'          => false,
		'recurring'               => false,
		'duration'                => 1,
		'duration_unit'           => 'month',
		'amount'                  => false,
		'auto_renew'              => false,
		'times_billed'            => 0,
		'billing_cycles'          => 0,
		'gateway_customer_id'     => false,
		'gateway_subscription_id' => false,
		'gateway'                 => '',
		'signup_method'           => '',
		'upgraded_from'           => false,
		'disabled'                => false,
		'status'                  => 'pending',
		'date_created'            => wu_get_current_time('mysql', true),
		'date_trial_end'          => null,
		'date_renewed'            => null,
		'date_modified'           => wu_get_current_time('mysql', true),
		'date_expiration'         => wu_get_current_time('mysql', true),
		'skip_validation'         => false,
	), $membership_data);

	$membership_data['migrated_from_id'] = is_numeric($membership_data['migrated_from_id']) ? $membership_data['migrated_from_id'] : 0;

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

		if (absint($membership->get_plan_id()) === absint($product_id)) {

			array_push($customers, $membership->get_customer_id());

		} // end if;

	} // end foreach;

	return $customers;

} // end wu_get_membership_customers;

/**
 * Returns a membership based on the customer gateway ID.
 *
 * This is NOT a very reliable way of retrieving memberships
 * as the same customer can have multiple memberships using
 * the same gateway.
 *
 * As this is only used as a last ditch effort, mostly when
 * trying to process payment-related webhooks,
 * we always get pending memberships, and the last one
 * created (order by ID DESC).
 *
 * @since 2.0.0
 *
 * @param string  $customer_gateway_id The customer gateway id. E.g. cus_***.
 * @param array   $allowed_gateways List of allowed gateways.
 * @param boolean $amount The amount. Increases accuracy.
 * @return \WP_Ultimo\Models\Membership|false
 */
function wu_get_membership_by_customer_gateway_id($customer_gateway_id, $allowed_gateways = array(), $amount = false) {

	$search_data = array(
		'gateway__in'             => $allowed_gateways,
		'number'                  => 1,
		'gateway_customer_id__in' => array($customer_gateway_id),
		'status__in'              => array('pending'),
		'orderby'                 => 'id',
		'order'                   => 'DESC',
	);

	if (!empty($amount)) {

		$search_data['initial_amount'] = $amount;

	} // end if;

	$memberships = wu_get_memberships($search_data);

	return !empty($memberships) ? current($memberships) : false;

} // end wu_get_membership_by_customer_gateway_id;

/**
 * Creates a new payment for a membership.
 *
 * This is used by gateways to create a new payment when necessary.
 *
 * @since 2.0.0
 *
 * @param \WP_Ultimo\Models\Membership $membership The membership object.
 * @param boolean                      $should_cancel_pending_payments If we should cancel pending payments.
 * @return int|\WP_Error
 */
function wu_membership_create_new_payment($membership, $should_cancel_pending_payments = true) {
	/*
	 * If we should cancel the previous
	 * pending payment, do that.
	 */
	if ($should_cancel_pending_payments) {

		$pending_payment = $membership->get_last_pending_payment();

		/*
		 * Change pending payment to cancelled.
		 */
		if ($pending_payment) {

			$pending_payment->set_status(Payment_Status::CANCELLED);
			$pending_payment->save();

		} // end if;

	} // end if;

	/*
	 * Create the new payment
	 */
	$previous_payments = wu_get_payments(array(
		'number'        => 1,
		'membership_id' => $membership->get_id(),
		'status'        => 'completed',
		'orderby'       => 'id',
		'order'         => 'DESC',
	));

	if (empty($previous_payments)) {

		return new \WP_Error('previous-payment-not-found', __('Previous payment not found', 'wp-ultimo'));

	} // end if;

	$previous_payment = $previous_payments[0];

	/*
		* This is kinda hack-y,
		* but this needs to be here to make sure
		* line items get loaded from the meta
		* and get copied over.
		*
		* Do not remove =)
		*/
	$previous_payment->get_line_items();

	$new_payment = $previous_payment->duplicate();
	$new_payment->remove_non_recurring_items();
	$new_payment->set_status(Payment_Status::PENDING);
	$new_payment->set_gateway_payment_id('');

	$status = $new_payment->save();

	if (is_wp_error($status)) {

		return $status;

	} // end if;

	return $new_payment;

} // end wu_membership_create_new_payment;
