<?php
/**
 * Payments Functions
 *
 * Public APIs to load and deal with WP Ultimo payment.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Payment
 * @version     2.0.0
 */

use \WP_Ultimo\Models\Payment;
use \WP_Ultimo\Database\Payments\Payment_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

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
		'date_created'       => current_time('mysql'),
		'date_modified'      => current_time('mysql'),
		'migrated_from_id'   => 0,
	), $payment_data);

	$payment = new Payment($payment_data);

	$saved = $payment->save();

	return is_wp_error($saved) ? $saved : $payment;

} // end wu_create_payment;
