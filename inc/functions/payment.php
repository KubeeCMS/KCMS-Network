<?php
/**
 * Payment Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Payment;
use \WP_Ultimo\Database\Payments\Payment_Status;

/**
 * Returns a payment.
 *
 * @since 2.0.0
 *
 * @param int $payment_id The ID of the payment.
 * @return \WP_Ultimo\Models\Payment|false
 */
function wu_get_payment($payment_id) {

	return \WP_Ultimo\Models\Payment::get_by_id($payment_id);

} // end wu_get_payment;

/**
 * Queries payments.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Payment[]
 */
function wu_get_payments($query = array()) {

	return \WP_Ultimo\Models\Payment::query($query);

} // end wu_get_payments;

/**
 * Returns a line-item.
 *
 * @since 2.0.0
 *
 * @param int $line_item_id The ID of the line item id.
 * @param int $payment_id The ID of the payment.
 * @return \WP_Ultimo\Checkout\Line_Item|false
 */
function wu_get_line_item($line_item_id, $payment_id) {

	$payment = wu_get_payment($payment_id);

	if (!$payment) {

		return false;

	} // end if;

	$line_items = $payment->get_line_items();

	return wu_get_isset($line_items, $line_item_id, false);

} // end wu_get_line_item;

/**
 * Gets a payment based on the hash.
 *
 * @since 2.0.0
 *
 * @param string $hash The hash for the payment.
 * @return \WP_Ultimo\Models\Payment|false
 */
function wu_get_payment_by_hash($hash) {

	return \WP_Ultimo\Models\Payment::get_by_hash($hash);

} // end wu_get_payment_by_hash;

/**
 * Returns a single payment defined by a particular column and value.
 *
 * @since 2.0.0
 *
 * @param string $column The column name.
 * @param mixed  $value The column value.
 * @return \WP_Ultimo\Models\Payment|false
 */
function wu_get_payment_by($column, $value) {

	return \WP_Ultimo\Models\Payment::get_by($column, $value);

} // end wu_get_payment_by;

/**
 * Creates a new payment.
 *
 * @since 2.0.0
 *
 * @param array $payment_data Payment data.
 * @return \WP_Error|\WP_Ultimo\Models\Payment
 */
function wu_create_payment($payment_data) {
	/*
	* Why do we use shortcode atts here?
	* Shortcode atts clean the array from not-allowed keys, so we don't need to worry much.
	*/
	$payment_data = shortcode_atts(array(
		'line_items'         => array(),
		'meta'               => array(),
		'customer_id'        => false,
		'membership_id'      => false,
		'parent_id'          => '',
		'product_id'         => false,
		'currency'           => 'USD',
		'discount_code'      => '',
		'subtotal'           => 0.00,
		'discount_total'     => 0.00,
		'tax_total'          => 0.00,
		'total'              => 0.00,
		'status'             => Payment_Status::COMPLETED,
		'gateway'            => '',
		'gateway_payment_id' => '',
		'date_created'       => wu_get_current_time('mysql', true),
		'date_modified'      => wu_get_current_time('mysql', true),
		'migrated_from_id'   => 0,
		'skip_validation'    => false,
	), $payment_data);

	$payment = new Payment($payment_data);

	$saved = $payment->save();

	return is_wp_error($saved) ? $saved : $payment;

} // end wu_create_payment;

/**
 * Returns a list of the refundable payment types.
 *
 * Can be filtered if new payment types that
 * can be refunded are added by developers.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_refundable_payment_types() {

	$refundable_payment_types = array(
		Payment_Status::COMPLETED,
		Payment_Status::PARTIAL_REFUND,
	);

	return apply_filters('wu_get_refundable_payment_type', $refundable_payment_types);

} // end wu_get_refundable_payment_types;

/**
 * Returns the icon classes for a payment status.
 *
 * @since 2.0.0
 *
 * @param string $payment_status The payment status.
 * @return string
 */
function wu_get_payment_icon_classes($payment_status) {

	$payment_status_instance = new Payment_Status($payment_status);

	return $payment_status_instance->get_icon_classes();

} // end wu_get_payment_icon_classes;
