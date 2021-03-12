<?php
/**
 * Gateway Manager
 *
 * Manages the registering and activation of gateways.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Gateway
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Manages the registering and activation of gateways.
 *
 * @since 2.0.0
 */
class Gateway_Manager extends Base_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Lists the registered gateways.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $registered_gateways = array();

	/**
	 * Lists the gateways that are enabled.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $enabled_gateways = array();

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('plugins_loaded', array($this, 'on_load'));

	} // end init;

	/**
	 * Runs after all plugins have been loaded to allow for add-ons to hook into it correctly.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function on_load() {
		/*
		 * Adds our own default gateways.
		 */
		add_action('wu_register_gateways', array($this, 'add_default_gateways'), 5);

		/*
		 * Adds the Gateway selection fields
		 */
		add_action('init', array($this, 'add_gateway_selector_field'));

		/*
		 * Handle gateway confirmations.
		 */
		add_action('template_redirect', array($this, 'process_gateway_confirmations'), -99999);

		/*
		 * Waits for webhook signals and deal with them.
		 */
		add_action('init', array($this, 'maybe_process_webhooks'), 1);

		/*
		 * Allow developers to add new gateways.
		 */
		do_action('wu_register_gateways');

	} // end on_load;

	/**
	 * Checks if we need to process webhooks received by gateways.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_process_webhooks() {

		$gateway = wu_request('wu-gateway');

		if ($gateway && !is_admin() && is_main_site()) {

			do_action("wu_{$gateway}_process_webhooks");

		} // end if;

	} // end maybe_process_webhooks;

	/**
	 * Let gateways deal with their confirmation steps.
	 *
	 * This is the case for PayPal Express.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_gateway_confirmations() {

		if (!wu_request('wu-confirm')) {

			return;

		} // end if;

		if (!wu_is_registration_page()) {

			return;

		} // end if;

		$gateway_id = sanitize_text_field(wu_request('wu-confirm'));

		$gateway = wu_get_gateway($gateway_id);

		if (!$gateway) {

			return;

		} // end if;

		$gateway->process_confirmation();

	} // end process_gateway_confirmations;

	/**
	 * Adds the field that enabled and disables Payment Gateways on the settings.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_gateway_selector_field() {

		wu_register_settings_field('payment-gateways', 'active_gateways', array(
			'title'   => __('Active Payment Gateways', 'wp-ultimo'),
			'desc'    => __('Payment gateways are what your customers will use to pay.', 'wp-ultimo'),
			'type'    => 'multiselect',
			'columns' => 2,
			'options' => array($this, 'get_gateways_as_options'),
			'default' => array(),
		));

	} // end add_gateway_selector_field;

	/**
	 * Returns the list of registered gateways as options for the gateway selector setting.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_gateways_as_options() {
		/*
		 * We use this to order the options.
		 */
		$active_gateways = wu_get_setting('active_gateways', array());

		$gateways = $this->get_registered_gateways();

		$gateways = array_filter($gateways, function($item) {

			return $item['hidden'] === false;

		});

		return $gateways;

	} // end get_gateways_as_options;

	/**
	 * Loads the default gateways.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_default_gateways() {
		/*
		 * Free Payments
		 */
		wu_register_gateway('free', __('Free', 'wp-ultimo'), '', '\\WP_Ultimo\\Gateways\\Free_Gateway', true);

		/*
		 * Stripe Payments
		 */
		wu_register_gateway('stripe', __('Stripe', 'wp-ultimo'), __('Stripe is a suite of payment APIs that powers commerce for businesses of all sizes, including subscription management.', 'wp-ultimo'), '\\WP_Ultimo\\Gateways\\Stripe_Gateway');

		/*
		 * PayPal Payments
		 */
		wu_register_gateway('paypal', __('PayPal', 'wp-ultimo'), __('PayPal is the leading provider in checkout solutions and it is the easier way to get your network subscriptions going.', 'wp-ultimo'), '\\WP_Ultimo\\Gateways\\PayPal_Gateway');

		/*
		 * Manual Payments
		 */
		wu_register_gateway('manual', __('Manual', 'wp-ultimo'), __('Use the Manual Gateway to allow users to pay you directly via bank transfers, checks, or other channels.', 'wp-ultimo'), '\\WP_Ultimo\\Gateways\\Manual_Gateway');

	} // end add_default_gateways;

	/**
	 * Checks if a gateway was already registered.
	 *
	 * @since 2.0.0
	 * @param string $id The id of the gateway.
	 * @return boolean
	 */
	public function is_gateway_registered($id) {

		return is_array($this->registered_gateways) && isset($this->registered_gateways[$id]);

	} // end is_gateway_registered;

	/**
	 * Returns a list of all the registered gateways
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_registered_gateways() {

		return $this->registered_gateways;

	} // end get_registered_gateways;

	/**
	 * Returns a particular Gateway registered
	 *
	 * @since 2.0.0
	 * @param string $id The id of the gateway.
	 * @return array
	 */
	public function get_gateway($id) {

		return $this->is_gateway_registered($id) ? $this->registered_gateways[$id] : false;

	} // end get_gateway;

	/**
	 * Adds a new Gateway to the System. Used by gateways to make themselves visible.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id ID of the gateway. This is how we will identify the gateway in the system.
	 * @param string $title Name of the gateway.
	 * @param string $desc A description of the gateway to help super admins understand what services they integrate with.
	 * @param string $class_name Gateway class name.
	 * @param bool   $hidden If we need to hide this gateway publicly.
	 * @return bool
	 */
	public function register_gateway($id, $title, $desc, $class_name, $hidden = false) {

		// Checks if gateway was already added
		if ($this->is_gateway_registered($id)) {

			return;

		} // end if;

		$active_gateways = (array) wu_get_setting('active_gateways', array());

		// Adds to the global
		$this->registered_gateways[$id] = array(
			'id'         => $id,
			'title'      => $title,
			'desc'       => $desc,
			'class_name' => $class_name,
			'active'     => in_array($id, $active_gateways, true),
			'active'     => in_array($id, $active_gateways, true),
			'hidden'     => (bool) $hidden,
			'gateway'    => $class_name, // Deprecated.
		);

		$this->install_hooks($class_name);

		// Return the value
		return true;

	} // end register_gateway;

	/**
	 * Adds additional hooks for each of the gateway registered.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class_name Gateway class name.
	 * @return void
	 */
	public function install_hooks($class_name) {

		$gateway = new $class_name;

		$gateway_id = $gateway->get_id();

		add_action('wu_checkout_scripts', array($gateway, 'register_scripts'));

		add_action('init', array($gateway, 'hooks'));

		add_action('init', array($gateway, 'settings'));

		add_action("wu_{$gateway_id}_process_webhooks", array($gateway, 'process_webhooks'));

		add_action("wu_{$gateway_id}_remote_payment_url", array($gateway, 'get_payment_url_on_gateway'));

		add_action("wu_{$gateway_id}_remote_subscription_url", array($gateway, 'get_subscription_url_on_gateway'));

		add_action('wu_after_save_settings', array($gateway, 'install_webhook'), 10, 3);

		add_filter('wu_customer_payment_methods', function($fields, $customer) use ($gateway) {

			$gateway->customer = $customer;

			$extra_fields = (array) $gateway->payment_methods();

			return array_merge($fields, $extra_fields);

		}, 10, 2);

		add_action('wu_checkout_gateway_fields', function($checkout) use ($gateway) {

			$field_content = call_user_func(array($gateway, 'fields'));

			ob_start();

			?>

			<div v-cloak v-show="gateway == '<?php echo esc_attr($gateway->get_id()); ?>' && !(order && order.is_free)" class="wu-overflow">

				<?php echo $field_content; ?>

			</div>

			<?php

			echo ob_get_clean();

		});

	} // end install_hooks;

} // end class Gateway_Manager;
