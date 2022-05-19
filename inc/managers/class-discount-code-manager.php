<?php
/**
 * Discount_Codes Manager
 *
 * Handles processes related to events.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Discount_Code_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to events.
 *
 * @since 2.0.0
 */
class Discount_Code_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'discount_code';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Discount_Code';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		add_action('wu_gateway_payment_processed', array($this, 'maybe_add_use_on_payment_received'));

	} // end init;

	/**
	 * Listens for payments received in order to increase the discount code uses.
	 *
	 * @since 2.0.4
	 *
	 * @param \WP_Ultimo\Models\Payment $payment The payment received.
	 * @return void
	 */
	public function maybe_add_use_on_payment_received($payment) {

		if (!$payment) {

			return;

		} // end if;

		/*
		 * Try to fetch the original cart of the payment.
		 * We want only to increase the number of uses
		 * for the first time payments are done.
		 */
		$original_cart = $payment->get_meta('wu_original_cart');

		if (is_a($original_cart, \WP_Ultimo\Checkout\Cart::class) === false) {

			return;

		} // end if;

		$discount_code = $original_cart->get_discount_code();

		if (!$discount_code) {

			return;

		} // end if;

		/*
		 * Refetch the object, as the original version
		 * might be too old and out-of-date by now.
		 */
		$discount_code = wu_get_discount_code($discount_code->get_id());

		if ($discount_code) {

			$discount_code->add_use();

			$discount_code->save();

		} // end if;

	} // end maybe_add_use_on_payment_received;

} // end class Discount_Code_Manager;
