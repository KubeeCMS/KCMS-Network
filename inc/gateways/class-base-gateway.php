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

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base Gateway class. Should be extended to add new payment gateways.
 *
 * @since 2.0.0
 */
class Base_Gateway {

	/**
	 * Holds the ID of a given gateway.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = '';

	/**
	 * Holds the environment we are running. True when in sandbox mode.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	public $test_mode = true;

	/**
	 * Flag to determine if we should display the billing address fields or not.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	public $request_billing_address = false;

	/**
	 * Public variable to hold the current order's cart.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Checkout\Cart
	 */
	public $cart;

	/**
	 * Array of features the gateway supports, including:
	 * - one-time (one time payments)
	 * - recurring (recurring payments)
	 * - fees (setup fees)
	 * - trial (free trials)
	 * - ajax-payment (payment processing via ajax)
	 * - card-updates (update billing card for subscriptions)
	 * - sync (sync subscription with gateway info)
	 *
	 * @var array
	 */
	protected $supports = array();

	/**
	 * Gateway constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param array $subscription_data Subscription data passed.
	 */
	public function __construct($subscription_data = array()) {

		$this->test_mode = true;

		$this->init();

		$this->set_payment_data($subscription_data);

	} // end __construct;

	/**
	 * Allow gateway developers to add additional settings to the setting screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function settings() { } // end settings;

	/**
	 * Loads the gateway class with useful data to process payments.
	 *
	 * @since 2.0.0
	 *
	 * @todo add log call to remember this.
	 * @param array $subscription_data List of parameters.
	 * @return void
	 */
	public function set_payment_data($subscription_data = array()) {

		if (!empty($subscription_data)) {

			$this->customer = $subscription_data['customer'];

			$this->user_id   = $this->customer->get_user_id();
			$this->email     = $this->customer->get_email_address();
			$this->user_name = $this->customer->get_username();

			$this->cart = $subscription_data['cart'];

			if ($this->cart->get_discount_code()) {

				$this->discount_codes = array($this->cart->get_discount_code());

			} // end if;

			$this->memberships = $subscription_data['memberships'];

			$this->payment = $subscription_data['payment'];

			$totals = $this->cart->calculate_totals();

			$this->initial_amount = $totals->total;
			$this->amount         = $totals->recurring->total;

			$this->auto_renew = $this->cart->has_recurring();

			$this->length      = $this->cart->get_duration();
			$this->length_unit = $this->cart->get_duration_unit();

			// wu_log( sprintf( 'Registration for user #%d sent to gateway. Level ID: %d; Initial Amount: %.2f; Recurring Amount: %.2f; Auto Renew: %s; Trial: %s; Subscription Start: %s; Membership ID: %d', $this->user_id, $this->subscription_id, $this->initial_amount, $this->amount, var_export( $this->auto_renew, true ), var_export( $this->is_trial(), true ), $this->subscription_start_date, $this->membership->get_id() ) );
		} // end if;

	} // end set_payment_data;

	/**
	 * Initialize the gateway configuration.
	 *
	 * This is used to populate the $supports property, setup any API keys, and set the API endpoint.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {} // end init;

	/**
	 * Initialize the necessary hooks that need to be registered.
	 *
	 * @access public
	 * @return void
	 */
	public function hooks() {} // end hooks;

	/**
	 * Returns the ID of the gateway being used.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_id() {

		return $this->id;

	} // end get_id;

	/**
	 * Checks if this gateway supports a given feature.
	 *
	 * @since 2.0.0
	 *
	 * @param string $item Feature to check. e.g. recurring.
	 * @return bool
	 */
	public function supports($item = '') {

		return in_array($item, $this->supports, true);

	} // end supports;

	/**
	 * Returns an array containing all the supported features of the gateway.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_support() {

		return $this->supports;

	} // end get_support;

	/**
	 * Allows Gateways to override the gateway title.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_public_title() {

		return '';

	} // end get_public_title;

	/**
	 * Returns the webhook url for the listener of this gateway events.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_webhook_listener_url() {

		$site_url = get_site_url(wu_get_main_site_id(), '/');

		return add_query_arg('wu-gateway', $this->get_id(), $site_url);

	} // end get_webhook_listener_url;

	/**
	 * Get the return URL.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_return_url() {

		if (empty($this->return_url)) {

			$this->return_url = wu_get_current_url();

		} // end if;

		return add_query_arg(array(
			'payment' => $this->payment->get_hash(),
			'status'  => 'done',
		), $this->return_url);

	} // end get_return_url;

	/**
	 * Redirects the customer to the redirect URL.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function redirect() {

		$redirect_url = $this->get_return_url();

		wp_redirect($redirect_url);

		exit;

	} // end redirect;

	/**
	 * Installs webhook urls on the remote payment processor (optional).
	 *
	 * WP Ultimo will call this whenever settings change.
	 *
	 * Here you have access to the settings being saved, so you can check if you really
	 * need to try to install the webhooks. Calling an API every time the settings get saved
	 * is BAD!
	 *
	 * That being said, it might also be a good idea to check if the webhook already exists
	 * before trying to re-create it.
	 *
	 * To get the webhook listener url, call $this->get_webhook_listener_url();
	 *
	 * Return true for success, or a \WP_Error instance in case of failure.
	 *
	 * @since 2.0.0
	 * @param array $settings The final settings array being saved, containing ALL options.
	 * @param array $settings_to_save Array containing just the options being updated.
	 * @param array $saved_settings Array containing the original settings.
	 * @return true|\WP_Error
	 */
	public function install_webhook($settings, $settings_to_save, $saved_settings) {

		return true;

	} // end install_webhook;

	/**
	 * Process signup via ajax
	 *
	 * Optionally, payment can be processed (in whole or in part) via ajax.
	 *
	 * If successful, return `true` or an array of field keys/values to add to the registration form as hidden fields.
	 *
	 * If failure, return `WP_Error`.
	 *
	 * @since 2.0.0
	 * @return true|array|WP_Error
	 */
	public function process_ajax_signup() {

		return true;

	} // end process_ajax_signup;

	/**
	 * Process registration.
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
	public function process_signup() {} // end process_signup;

	/**
	 * Process confirmation.
	 *
	 * Some gateways require user confirmation at some point.
	 * It's the case for PayPal Express, for example.
	 * This method implements the necessary things.
	 *
	 * After a successful payment, redirect to $this->return_url.
	 *
	 * @access public
	 * @return void
	 */
	public function process_confirmation() {} // end process_confirmation;

	/**
	 * Process webhooks
	 *
	 * Listen for webhooks and take appropriate action to insert payments, renew the member's
	 * account, or cancel the membership.
	 *
	 * @access public
	 * @return void
	 */
	public function process_webhooks() {} // end process_webhooks;

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

		return '';

	} // end fields;

	/**
	 * Returns the list of payment methods integrated.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function payment_methods() {

		return array();

	} // end payment_methods;

	/**
	 * Allows gateways to add new scripts and styles to the checkout page.
	 *
	 * You can use is_admin() to check if this checkout page is being displayed inside the admin
	 * panel or if it is a front-end checkout.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {} // end register_scripts;

	/**
	 * Activate or renew the membership. If the membership has been billed `0` times then it is activated for the
	 * first time. Otherwise it is renewed.
	 *
	 * @todo This doesn't work and don't support multiple memberships.
	 *
	 * @param bool   $recurring Whether or not it's a recurring subscription.
	 * @param string $status    Status to set the member to, usually 'active'.
	 * @return void
	 */
	public function renew_member($recurring = false, $status = 'active') {

		if (0 === $this->membership->get_times_billed()) {

			$this->membership->activate();

		} else {

			$this->membership->renew($recurring, $status);

		} // end if;

	} // end renew_member;

	/**
	 * Determines if the subscription is eligible for a trial.
	 *
	 * @since 2.0.0
	 * @return bool True if the subscription is eligible for a trial, false if not.
	 */
	public function is_trial() {

		return !empty($this->subscription_data['trial_eligible'])
		&& !empty($this->subscription_data['trial_duration'])
		&& !empty($this->subscription_data['trial_duration_unit']);

	} // end is_trial;

	/**
	 * Returns the external link to view the payment on the payment gateway.
	 *
	 * Return an empty string to hide the link element.
	 *
	 * @since 2.0.0
	 *
	 * @param string $gateway_payment_id The gateway payment id.
	 * @return string.
	 */
	public function get_payment_url_on_gateway($gateway_payment_id) {

		return '';

	} // end get_payment_url_on_gateway;

	/**
	 * Returns the external link to view the membership on the membership gateway.
	 *
	 * Return an empty string to hide the link element.
	 *
	 * @since 2.0.0
	 *
	 * @param string $gateway_subscription_id The gateway subscription id.
	 * @return string.
	 */
	public function get_subscription_url_on_gateway($gateway_subscription_id) {

		return '';

	} // end get_subscription_url_on_gateway;

} // end class Base_Gateway;
