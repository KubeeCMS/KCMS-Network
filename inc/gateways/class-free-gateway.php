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
	 * Checks if we should hide this option on the front-end.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hidden = true;

	/**
	 * Initialize the gateway configuration
	 *
	 * This is used to populate the $supports property, setup any API keys, and set the API endpoint.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		parent::init();

	} // end init;

	/**
	 * Process registration
	 *
	 * This is where you process the actual payment. If non-recurring, you'll want to use
	 * the $this->initial_amount value. If recurring, you'll want to use $this->initial_amount
	 * for the first payment and $this->amount for the recurring amount.
	 *
	 * After a successful payment, redirect to $this->return_url.
	 *
	 * @access public
	 * @return void
	 */
	public function process_signup() {

		parent::process_signup();

	} // end process_signup;

	/**
	 * Adds additional fields to the checkout form for a particular gateway.
	 *
	 * In this method, you can either return an array of fields (that we will display
	 * using our form display methods) or you can return plain HTML in a string,
	 * which will get outputted to the gateway section of the checkout.
	 *
	 * @since 2.0.0
	 * @return array|string
	 */
	public function fields() {

		return sprintf('<p class="wu-p-4 wu-bg-yellow-200">%s</p>', __('After you finish signing up, you will need to confirm your account at your email address.', 'wp-ultimo'));

	} // end fields;

} // end class Free_Gateway;
