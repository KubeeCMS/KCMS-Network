<?php
/**
 * Base Gateway.
 *
 * Base Gateway class. Should be extended to add new payment gateways.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Site_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Gateways;

use WP_Ultimo\Gateways\Base_Gateway;
use \WP_Ultimo\Database\Memberships\Membership_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base Gateway class. Should be extended to add new payment gateways.
 *
 * @since 2.0.0
 */
class Free_Gateway extends Base_Gateway {

	/**
	 * Holds the ID of a given gateway.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'free';

	/**
	 * Process a checkout.
	 *
	 * It takes the data concerning
	 * a new checkout and process it.
	 *
	 * Here's where you will want to send
	 * API calls to the gateway server,
	 * set up recurring payment profiles, etc.
	 *
	 * This method is required and MUST
	 * be implemented by gateways extending the
	 * Base_Gateway class.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @param \WP_Ultimo\Checkout\Cart     $cart The cart object.
	 * @param string                       $type The checkout type. Can be 'new', 'retry', 'upgrade', 'downgrade', 'addon'.
	 * @return void
	 */
	public function process_checkout($payment, $membership, $customer, $cart, $type) {
		/*
		 * Handles downgrades to free plans
		 */
		if ($type === 'downgrade') {
			/*
			 * When downgrading, we need to schedule a swap for the end of the
			 * current expiration date.
			 */
			$membership->schedule_swap($cart);

			/*
			 * Mark the membership as active,
			 * as this is a downgrade to free.
			 */
			$membership->set_status(Membership_Status::ACTIVE);

			/*
			 * Saves the membership with the changes.
			 */
			$status = $membership->save();

		} // end if;

	} // end process_checkout;

	/**
	 * Process a cancellation.
	 *
	 * It takes the data concerning
	 * a membership cancellation and process it.
	 *
	 * Here's where you will want to send
	 * API calls to the gateway server,
	 * to cancel a recurring profile, etc.
	 *
	 * This method is required and MUST
	 * be implemented by gateways extending the
	 * Base_Gateway class.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @return void
	 */
	public function process_cancellation($membership, $customer) {} // end process_cancellation;

	/**
	 * Process a refund.
	 *
	 * It takes the data concerning
	 * a refund and process it.
	 *
	 * Here's where you will want to send
	 * API calls to the gateway server,
	 * to issue a refund.
	 *
	 * This method is required and MUST
	 * be implemented by gateways extending the
	 * Base_Gateway class.
	 *
	 * @since 2.0.0
	 *
	 * @param float                        $amount The amount to refund.
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @return void
	 */
	public function process_refund($amount, $payment, $membership, $customer) {} // end process_refund;

} // end class Free_Gateway;
