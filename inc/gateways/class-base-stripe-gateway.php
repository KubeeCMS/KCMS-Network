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

use \WP_Ultimo\Gateways\Base_Gateway;
use \WP_Ultimo\Gateways\Ignorable_Exception;
use \WP_Ultimo\Dependencies\Stripe;
use \WP_Ultimo\Models\Membership;
use \WP_Ultimo\Database\Payments\Payment_Status;
use \WP_Ultimo\Checkout\Cart;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base Gateway class. Should be extended to add new payment gateways.
 *
 * @since 2.0.0
 */
class Base_Stripe_Gateway extends Base_Gateway {

	/**
	 * Allow gateways to declare multiple additional ids.
	 *
	 * These ids can be retrieved alongside the main id,
	 * via the method get_all_ids().
	 *
	 * @since 2.0.7
	 * @var array
	 */
	protected $other_ids = array('stripe', 'stripe-checkout');

	/**
	 * Backwards compatibility for the old notify ajax url.
	 *
	 * @since 2.0.4
	 * @var bool|string
	 */
	protected $backwards_compatibility_v1_id = 'stripe';

	/**
	 * Holds the publishable API key provided by Stripe.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $publishable_key;

	/**
	 * Holds the secret API key provided by Stripe.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $secret_key;

	/**
	 * Declares support to recurring payments.
	 *
	 * @since 2.0.0
	 * @return true
	 */
	public function supports_recurring() {

		return true;

	} // end supports_recurring;

	/**
	 * Get things going
	 *
	 * @access public
	 * @since  2.1
	 * @return void
	 */
	public function init() {

		$id = wu_replace_dashes($this->get_id());

		$this->request_billing_address = true;

		$this->test_mode = wu_get_setting("{$id}_sandbox_mode", true);

		$this->setup_api_keys($id);

		if (method_exists('Stripe', 'setAppInfo')) {

			Stripe\Stripe::setAppInfo('WordPress WP Ultimo', wu_get_version(), esc_url(site_url()));

		} // end if;

	} // end init;

	/**
	 * Setup api keys for stripe.
	 *
	 * @since 2.0.7
	 *
	 * @param string $id The gateway stripe id.
	 * @return void
	 */
	public function setup_api_keys($id) {

		$id = wu_replace_dashes($this->get_id());

		if ($this->test_mode) {

			$this->publishable_key = wu_get_setting("{$id}_test_pk_key", '');
			$this->secret_key      = wu_get_setting("{$id}_test_sk_key", '');

		} else {

			$this->publishable_key = wu_get_setting("{$id}_live_pk_key", '');
			$this->secret_key      = wu_get_setting("{$id}_live_sk_key", '');

		} // end if;

		if ($this->secret_key) {

			Stripe\Stripe::setApiKey($this->secret_key);

			Stripe\Stripe::setApiVersion('2019-05-16');

		} // end if;

	} // end setup_api_keys;

	/**
	 * Adds additional hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks() {

		add_action('wu_after_save_settings', array($this, 'install_webhook'), 10, 3);

	} // end hooks;

	/**
	 * Allows Gateways to override the gateway title.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_public_title() {

		$gateway_id = wu_replace_dashes($this->id);

		return wu_get_setting("{$gateway_id}_public_title", __('Credit Card', 'wp-ultimo'));

	} // end get_public_title;

	/**
	 * Adds the Stripe Gateway settings to the settings screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function settings() {} // end settings;

	/**
	 * Checks if we already have a webhook listener installed.
	 *
	 * @since 2.0.0
	 * @return WebhookEndpoint|\WP_Error|false
	 */
	public function has_webhook_installed() {

		try {

			$webhook_url = $this->get_webhook_listener_url();

			$search_webhook = \WP_Ultimo\Dependencies\Stripe\WebhookEndpoint::all(array(
				'limit' => 100,
			));

			$set_webhook_endpoint = false;

			foreach ($search_webhook as $webhook_endpoint) {

				if ($webhook_endpoint->url === $webhook_url) {

					return $webhook_endpoint;

				} // end if;

			} // end foreach;

		} catch (\Throwable $e) {

			return new \WP_Error($e->getCode(), $e->getMessage());

		} // end try;

		return false;

	} // end has_webhook_installed;

	/**
	 * Installs webhook urls onto Stripe.
	 *
	 * WP Ultimo will call this whenever settings for this api changes.
	 * That being said, it might be a good idea to check if the webhook already exists
	 * before trying to re-create it.
	 *
	 * Return true for success, or a \WP_Error instance in case of failure.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings The final settings array being saved, containing ALL options.
	 * @param array $settings_to_save Array containing just the options being updated.
	 * @param array $saved_settings Array containing the original settings.
	 * @return true|\WP_Error
	 */
	public function install_webhook($settings, $settings_to_save, $saved_settings) {

		if (!isset($settings_to_save['stripe_sandbox_mode'])) {

			return;

		} // end if;

		/*
		 * Checked if the Stripe Settings changed, so we can install webhooks.
		 */
		$changed_settings = array(
			$settings_to_save['stripe_sandbox_mode'],
			$settings_to_save['stripe_test_pk_key'],
			$settings_to_save['stripe_test_sk_key'],
			$settings_to_save['stripe_live_pk_key'],
			$settings_to_save['stripe_live_sk_key'],
		);

		$original_settings = array(
			$saved_settings['stripe_sandbox_mode'],
			$saved_settings['stripe_test_pk_key'],
			$saved_settings['stripe_test_sk_key'],
			$saved_settings['stripe_live_pk_key'],
			$saved_settings['stripe_live_sk_key'],
		);

		if ($changed_settings == $original_settings) { // phpcs:ignore

			return false;

		} // end if;

		$webhook_url = $this->get_webhook_listener_url();

		$existing_webhook = $this->has_webhook_installed();

		if (is_wp_error($existing_webhook)) {

			return $existing_webhook;

		} // end if;

		try {
			/*
			 * If already exists, checks for status
			 */
			if ($existing_webhook) {

				if ($existing_webhook->status === 'disabled') {

					$status = \WP_Ultimo\Dependencies\Stripe\WebhookEndpoint::update($existing_webhook->id, array(
						'status' => 'enabled',
					));

				} // end if;

				return true;

			} // end if;

			/*
			 * Otherwise, create it.
			 */
			\WP_Ultimo\Dependencies\Stripe\WebhookEndpoint::create(array(
				'enabled_events' => array('*'),
				'url'            => $webhook_url,
				'description'    => 'Added by WP Ultimo. Required to correctly handle changes in subscription status.',
			));

			return true;

		} catch (\Throwable $e) {

			return new \WP_Error($e->getCode(), $e->getMessage());

		} // end try;

	} // end install_webhook;

	/**
	 * Run preparations before checkout processing.
	 *
	 * This runs during the checkout form validation
	 * and it is a great chance to do preflight stuff
	 * if the gateway requires it.
	 *
	 * If you return an array here, Ultimo
	 * will append the key => value of that array
	 * as hidden fields to the checkout field,
	 * and those get submitted with the rest of the form.
	 *
	 * As an example, this is how we create payment
	 * intents for Stripe to make the experience more
	 * streamlined.
	 *
	 * @since 2.0.0
	 * @return void|array
	 */
	public function run_preflight() {} // end run_preflight;

	/**
	 * Get or create Stripe Customer.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $customer_id WP Ultimo customer ID.
	 * @param integer $user_id The WordPress user ID.
	 * @param integer $stripe_customer_id The Stripe Customer ID.
	 * @return Stripe\Customer|\WP_Error
	 */
	public function get_or_create_customer($customer_id = 0, $user_id = 0, $stripe_customer_id = 0) {
		/*
		 * Sets flag to control if we need
		 * to create a new customer or not.
		 */
		$customer_exists = false;

		/*
		 * Use the WP Ultimo customer ID to search on the
		 * database for an existing Stripe customer id.
		 */
		if (empty($stripe_customer_id)) {

			$stripe_customer_id = wu_get_customer_gateway_id($customer_id, array('stripe', 'stripe_checkout'));

		} // end if;

		/*
		 * We found a Stripe Customer ID!
		 *
		 * Now we have to use it to try and retrieve a
		 * stripe customer object.
		 */
		if ($stripe_customer_id) {

			try {

				$stripe_customer = Stripe\Customer::retrieve($stripe_customer_id);

				/*
				 * If the customer was deleted, we
				 * cannot use it again...
				 */
				if (!isset($stripe_customer->deleted) || !$stripe_customer->deleted) {

					$customer_exists = true;

				} // end if;

			} catch (\Exception $e) {

				/**
				 * Silence is golden.
				 */

			} // end try;

		} // end if;

		/*
		 * No customer found.
		 *
		 * In this scenario, we'll need to create a new one.
		 */
		if (empty($customer_exists)) {

			try {
				/*
				 * Pass the name and email to stripe.
				 */
				$customer_args = array(
					'email' => $this->customer->get_email_address(),
					'name'  => $this->customer->get_display_name(),
				);

				/*
				 * Filters the customer creation arguments.
				 */
				$customer_args = apply_filters('wu_stripe_customer_create_args', $customer_args, $this);

				/*
				 * Finally, try to create it.
				 */
				$stripe_customer = Stripe\Customer::create($customer_args);

			} catch (\Exception $e) {

				return new \WP_Error($e->getCode(), $e->getMessage());

			} // end try;

		} // end if;

		return $stripe_customer;

	} // end get_or_create_customer;

	/**
	 * Returns an array with customer meta data.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_customer_metadata() {

		$meta_data = array(
			'key'           => $this->membership->get_id(),
			'email'         => $this->customer->get_email_address(),
			'membership_id' => $this->membership->get_id(),
			'customer_id'   => $this->customer->get_id(),
			'payment_id'    => $this->payment->get_id(),
		);

		return $meta_data;

	} // end get_customer_metadata;

	/**
	 * Process a checkout.
	 *
	 * It takes the data concerning
	 * a new checkout and process it.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @param \WP_Ultimo\Checkout\Cart     $cart The cart object.
	 * @param string                       $type The checkout type. Can be 'new', 'retry', 'upgrade', 'downgrade', 'addon'.
	 *
	 * @throws \Exception When a stripe API error is caught.
	 *
	 * @return void
	 */
	public function process_checkout($payment, $membership, $customer, $cart, $type) {} // end process_checkout;

	/**
	 * Create a recurring subscription in Stripe.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @param \WP_Ultimo\Checkout\Cart     $cart The cart object.
	 * @param string                       $type The checkout type. Can be 'new', 'retry', 'upgrade', 'downgrade', 'addon'.
	 * @param Stripe\PaymentMethod         $payment_method The save payment method on Stripe.
	 * @param Stripe\Customer              $s_customer The Stripe customer.
	 * @param Stripe\PaymentIntent         $payment_intent The payment intent.
	 *
	 * @return void
	 */
	protected function create_recurring_payment($payment, $membership, $customer, $cart, $type, $payment_method, $s_customer, $payment_intent) {
		/*
		 * First, we need to create
		 * a cart description that Stripe understands...
		 */
		$stripe_cart = $this->build_stripe_cart($cart);

		/*
		 * The cart creation process might run into
		 * errors, and in that case, it will
		 * return a WP_Error object.
		 */
		if (is_object($stripe_cart) && is_wp_error($stripe_cart)) {

			throw new \Exception($stripe_cart->get_error_message());

		} // end if;

		// Otherwise, use the calculated expiration date of the membership, modified to current time instead of 23:59.
		$billing_date = $cart->get_billing_start_date();
		$base_date    = $billing_date ? $billing_date : $cart->get_billing_next_charge_date();
		$datetime     = \DateTime::createFromFormat('U', $base_date);
		$current_time = getdate();

		$datetime->setTime($current_time['hours'], $current_time['minutes'], $current_time['seconds']);

		$start_date = $datetime->getTimestamp() - HOUR_IN_SECONDS; // Reduce by 60 seconds to account for inaccurate server times.

		if (empty($payment_method)) {

			throw new \Exception(__('Invalid payment method', 'wp-ultimo'));

		} // end if;

		/*
		 * Subscription arguments for Stripe
		 */
		$sub_args = array(
			'items'                  => array_values($stripe_cart),
			'default_payment_method' => $payment_method->id,
			'prorate'                => false,
			'metadata'               => $this->get_customer_metadata(),
		);

		/*
		 * Now determine if we use `trial_end` or `billing_cycle_anchor` to schedule the start of the
		 * subscription.
		 *
		 * If this is an actual trial, then we use `trial_end`.
		 *
		 * Otherwise, billing cycle anchor is preferable because that works with Stripe MRR.
		 * However, the anchor date cannot be further in the future than a normal billing cycle duration.
		 * If that's the case, then we have to use trial end instead.
		 */
		$stripe_max_anchor = $this->get_stripe_max_billing_cycle_anchor($cart->get_duration(), $cart->get_duration_unit(), 'now');

		if ($cart->has_trial() || $start_date > $stripe_max_anchor->getTimestamp()) {

			$sub_args['trial_end'] = $start_date;

		} else {

			$sub_args['billing_cycle_anchor'] = $start_date;

		} // end if;

		/*
		 * Sets the billing anchor.
		 */
		$set_anchor = isset($sub_args['billing_cycle_anchor']);

		$sub_options = apply_filters('wu_stripe_create_subscription_options', array(
			'idempotency_key' => wu_stripe_generate_idempotency_key($sub_args),
		));

		/*
		 * Filters the Stripe subscription arguments.
		 */
		$sub_args = apply_filters('wu_stripe_create_subscription_args', $sub_args, $this);

		/*
		 * If we have a `billing_cycle_anchor` AND a `trial_end`, then we need to unset whichever one
		 * we set, and leave the customer's custom one in tact.
		 *
		 * This is done to account for people who filter the arguments to customize the next bill
		 * date. If `trial_end` is used in conjunction with `billing_cycle_anchor` then it will create
		 * unexpected results and the next bill date will not be what they want.
		 *
		 * This may not be completely perfect but it's the best way to try to account for any errors.
		 */
		if (!empty($sub_args['trial_end']) && !empty($sub_args['billing_cycle_anchor'])) {
			/*
			 * If we set an anchor, remove that, because
			 * this means the customer has set their own `trial_end`.
			 */
			if ($set_anchor) {

				unset($sub_args['billing_cycle_anchor']);

			} else {
				/*
				 * We set a trial, which means the customer
				 * has set their own `billing_cycle_anchor`.
				 */
				unset($sub_args['trial_end']);

			} // end if;

		} // end if;

		/*
		 * Tries to create the subscription
		 * on Stripe!
		 */
		$subscription = $s_customer->subscriptions->create($sub_args, $sub_options);

		/*
		 * Modify the membership accordingly.
		 */
		$membership->set_gateway_subscription_id($subscription->id);
		$membership->save();

		/*
		 * If this all started with a Setup Intent then
		 * let's use the subscription ID as the transaction ID.
		 */
		if ('setup_intent' === $payment_intent->object) {

			$this->payment->attributes(array(
				'gateway_payment_id' => sanitize_text_field($subscription->id),
			));

			$this->payment->save();

		} // end if;

	} // end create_recurring_payment;

	/**
	 * Checks if we need to create a pro-rate/credit coupon based on the cart data.
	 *
	 * Will return an array with coupon arguments for stripe if
	 * there is credit to be added and false if not.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Cart $cart The current cart.
	 * @return array|false
	 */
	protected function generate_credit_coupon_data($cart) {

		$amount = 0;

		foreach ($cart->get_line_items() as $line_item) {

			if ($line_item->get_total() < 0) {

				$amount += $line_item->get_total();

			} // end if;

		} // end foreach;

		if (empty($amount)) {

			return false;

		} // end if;

		return array(
			'name'       => __('Account credit and other discounts', 'wp-ultimo'),
			'amount_off' => - round($amount * wu_stripe_get_currency_multiplier()),
			'duration'   => 'once',
			'currency'   => $cart->get_currency(),
		);

	} // end generate_credit_coupon_data;

	/**
	 * Builds the non-recurring list of items to be paid on Stripe.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Cart $cart The cart/order object.
	 * @param bool                     $include_recurring_products If we should include recurring items as non-recurring.
	 * @return array
	 */
	protected function build_non_recurring_cart($cart, $include_recurring_products = false) {

		$cart_items = array();

		foreach ($cart->get_line_items() as $line_item) {
			/*
			 * Skip recurring items
			 */
			if ($line_item->is_recurring() && $include_recurring_products === false) {

				continue;

			} // end if;

			/*
			 * Skip negative items.
			 * In cases like this, we need to generate a coupon code.
			 */
			if ($line_item->get_unit_price() < 0) {

				continue;

			} // end if;

			$cart_items[$line_item->get_id()] = array(
				'name'     => $line_item->get_title(),
				'quantity' => $line_item->get_quantity(),
				'amount'   => $line_item->get_unit_price() * wu_stripe_get_currency_multiplier(),
				'currency' => strtolower($cart->get_currency()),
			);

			$description = $line_item->get_description();

			if (!empty($description)) {

				$cart_items[$line_item->get_id()]['description'] = $description;

			} // end if;

			/*
			 * Now, we handle the taxable status
			 * of the payment.
			 *
			 * We might need to create tax rates on
			 * Stripe and apply it on the subscription cart.
			 */
			if ($line_item->is_taxable()) {

				$tax_rates_for_plan = array();

				$product = $line_item->get_product();

				if (!$product) {

					continue;

				} // end if;

				$tax_category = $product->get_tax_category();

				$tax_rates = wu_get_applicable_tax_rates($cart->get_country(), $tax_category);

				if (!empty($tax_rates)) {

					foreach ($tax_rates as $applicable_tax_rate) {

						$stripe_tax_rate = $this->maybe_create_tax_rate($applicable_tax_rate);

						$tax_rates_for_plan = array($stripe_tax_rate);

						continue;

					} // end foreach;

				} // end if;

				if (!empty($tax_rates_for_plan)) {

					$cart_items[$line_item->get_id()]['tax_rates'] = $tax_rates_for_plan;

				} // end if;

			} // end if;

		} // end foreach;

		return array_values($cart_items);

	} // end build_non_recurring_cart;

	/**
	 * Converts the WP Ultimo cart into Stripe Sub arguments.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Cart $cart The cart object.
	 * @return array
	 */
	protected function build_stripe_cart($cart) {
		/*
		 * Set up a recurring subscription in Stripe with
		 * a delayed start date.
		 *
		 * All start dates are delayed one cycle because we use a
		 * one-time payment for the first charge.
		 */
		$plans = array();

		$all_products = $cart->get_all_products();

		foreach ($all_products as $product) {
			/*
			 * Exclude products that are not recurring.
			 */
			if (!$product->is_recurring()) {

				continue;

			} // end if;

			$amount = $product->get_amount();

			$discount_code = $cart->get_discount_code();

			if ($discount_code) {

				if ($discount_code->should_apply_to_renewals() && $cart->get_cart_type() !== 'renewal') {

					$amount = wu_get_discounted_price($amount, $discount_code->get_value(), $discount_code->get_type(), false);

				} // end if;

			} // end if;

			try {
				/*
				 * We might need to create the plan on Stripe.
				 * Otherwise, we'll get the stripe plan id in here.
				 */
				$plan_id = $this->maybe_create_plan(array(
					'name'           => $product->get_name(),
					'price'          => $amount,
					'interval'       => $product->get_duration_unit(),
					'interval_count' => $product->get_duration(),
				));

				if (is_wp_error($plan_id)) {

					return $plan_id;

				} // end if;

				/*
				 * Adds the new plan ID to the subscription cart.
				 */
				$plans[$plan_id] = array(
					'plan' => $plan_id,
				);

			} catch (\Exception $e) {

				$error_message = sprintf('Failed to create subscription for membership #%d. Message: %s', $this->membership->get_id(), $e->getMessage());

				return new \WP_Error('plan-creation-failed', $error_message);

			} // end try;

			/*
			 * Now, we handle the taxable status
			 * of the payment.
			 *
			 * We might need to create tax rates on
			 * Stripe and apply it on the subscription cart.
			 */
			if ($product->is_taxable()) {

				$tax_rates_for_plan = array();

				$tax_category = $product->get_tax_category();

				$tax_rates = wu_get_applicable_tax_rates($this->customer->get_country(), $tax_category);

				if (!empty($tax_rates)) {

					foreach ($tax_rates as $applicable_tax_rate) {

						$stripe_tax_rate = $this->maybe_create_tax_rate($applicable_tax_rate);

						$tax_rates_for_plan = array($stripe_tax_rate);

						continue;

					} // end foreach;

				} // end if;

				if (!empty($tax_rates_for_plan)) {

					$plans[$plan_id]['tax_rates'] = $tax_rates_for_plan;

				} // end if;

			} // end if;

		} // end foreach;

		return $plans;

	} // end build_stripe_cart;

	/**
	 * Saves a payment method to a customer on Stripe.
	 *
	 * @since 2.0.0
	 *
	 * @param  Stripe\Payment_Intent $payment_intent The payment intent.
	 * @param  Stripe\Customer       $s_customer The stripe customer.
	 * @return Stripe\Payment_Method
	 */
	protected function save_payment_method($payment_intent, $s_customer) {

		$payment_method = false;

		try {

			$payment_method = Stripe\PaymentMethod::retrieve($payment_intent->payment_method);

			if (empty($payment_method->customer)) {

				$payment_method->attach(array(
					'customer' => $s_customer->id
				));

			} // end if;

			/*
			 * Update remote payment methods.
			 */
			Stripe\Customer::update($s_customer->id, array(
				'invoice_settings' => array(
					'default_payment_method' => $payment_intent->payment_method,
				),
			));

			/*
			 * De-dupe payment methods.
			 *
			 * If someone re-registers with the same card details they've used in the past, Stripe
			 * will actually create a whole new payment method object with the same fingerprint.
			 * This could result in the same card being added to the customer's payment methods in
			 * Stripe, which is kind of annoying. So we de-dupe them to make sure one customer only
			 * has each payment method listed once. Hopefully Stripe will handle this automatically
			 * in the future.
			 */
			$customer_payment_methods = Stripe\PaymentMethod::all(array(
				'customer' => $s_customer->id,
				'type'     => 'card'
			));

			if (!empty($customer_payment_methods->data)) {

				foreach ($customer_payment_methods->data as $existing_method) {
					/*
					 * Detach if the fingerprint matches but payment method ID is different.
					 */
					if ($existing_method->card->fingerprint === $payment_method->card->fingerprint && $existing_method->id !== $payment_method->id) {

						$existing_method->detach();

					} // end if;

				} // end foreach;

			} // end if;

		} catch (\Exception $e) {

			$error = sprintf('Stripe Gateway: Failed to attach payment method to customer while activating membership #%d. Message: %s', 0, $e->getMessage());

			wu_log_add('stripe', $error);

		} // end try;

		return $payment_method;

	} // end save_payment_method;

	/**
	 * Maybe cancel old subscriptions.
	 *
	 * @since 2.0.0
	 *
	 * @param Stripe\Customer $s_customer The stripe customer.
	 * @return void
	 */
	public function maybe_cancel_old_subscriptions($s_customer) {
		/*
		 * Clean up any past due or unpaid subscription.
		 *
		 * We only do this if multiple memberships is not enabled, otherwise we can't be
		 * completely sure which ones we need to keep.
		 */
		if (false && !wu_multiple_memberships_enabled()) {

			try {

				// Set up array of subscriptions we cancel below so we don't try to cancel the same one twice.
				$cancelled_subscriptions = array();

				// Clean up any past due or unpaid subscriptions. We do this to ensure we don't end up with duplicates.
				$subscriptions = $s_customer->subscriptions->all( array(
					'expand' => array( 'data.plan.product' )
				) );

				foreach ( $subscriptions->data as $subscription ) {

					// Cancel subscriptions with the RCP metadata present and matching member ID.
					if ( !empty( $subscription->metadata ) && !empty( $subscription->metadata['wu_subscription_level_id'] ) && $this->user_id === $subscription->metadata['wu_member_id'] ) {

						$subscription->cancel();

						$cancelled_subscriptions[] = $subscription->id;

						wu_log_add('stripe', sprintf('Stripe Gateway: Cancelled Stripe subscription %s.', $subscription->id));

						continue;

					} // end if;

					/*
					 * This handles subscriptions from before metadata was added. We check the plan name against the
					 * RCP membership level database. If the Stripe plan name matches a sub level name then we cancel it.
					 */
					if ( !empty( $subscription->plan->product->name ) ) {

						global $wu_levels_db;

						$level = $wu_levels_db->get_level_by( 'name', $subscription->plan->product->name );

						// Cancel if this plan name matches an RCP membership level.
						if ( !empty( $level ) ) {

							$subscription->cancel();

							$cancelled_subscriptions[] = $subscription->id;

							wu_log_add('stripe', sprintf( 'Stripe Gateway: Cancelled Stripe subscription %s.', $subscription->id ));

						} // end if;

					} // end if;

				} // end foreach;

			} catch ( \Exception $e ) {

				wu_log_add('stripe', sprintf( 'Stripe Gateway: Subscription cleanup failed for user #%d. Message: %s', $this->user_id, $e->getMessage()), true);

			} // end try;

		} // end if;

	} // end maybe_cancel_old_subscriptions;

	/**
	 * Process a refund.
	 *
	 * It takes the data concerning
	 * a refund and process it.
	 *
	 * @since 2.0.0
	 *
	 * @param float                        $amount The amount to refund.
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @return void|bool
	 */
	public function process_refund($amount, $payment, $membership, $customer) {

		$gateway_payment_id = $payment->get_gateway_payment_id();

		if (empty($gateway_payment_id)) {

			throw new \Exception(__('Gateway payment ID not found. Cannot process refund automatically.', 'wp-ultimo'));

		} // end if;

		/*
		 * Check if we have an invoice,
		 * or a charge at hand.
		 */
		if (strpos($gateway_payment_id, 'ch_') === 0) {

			$charge_id = $gateway_payment_id;

		} elseif (strpos($gateway_payment_id, 'in_') === 0) {

			$invoice = Stripe\Invoice::retrieve($gateway_payment_id);

			$gateway_payment_id = $invoice->charge;

		} else {

			throw new Exception(__('Gateway payment ID not valid.', 'wp-ultimo'));

		} // end if;

		/*
		 * We need to normalize the value
		 * for Stripe, which usually works
		 * in cents.
		 */
		$normalize_amount = $amount * wu_stripe_get_currency_multiplier();

		Stripe\Refund::create(array(
			'charge' => $charge_id,
			'amount' => $normalize_amount,
		));

		/*
		 * You might be asking why we are not
		 * calling $payment->refund($amount) to
		 * update the payment status.
		 *
		 * We will do that once Stripe tells us
		 * that the refund was successful.
		 */
		return true;

	} // end process_refund;

	/**
	 * Process a cancellation.
	 *
	 * It takes the data concerning
	 * a membership cancellation and process it.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @return void|bool
	 */
	public function process_cancellation($membership, $customer) {

		$subscription_id = $membership->get_gateway_subscription_id();

		if (!empty($subscription_id)) {

			$subscription = Stripe\Subscription::retrieve($subscription_id);

			$subscription->cancel();

		} // end if;

	} // end process_cancellation;

	/**
	 * Attempt to guess the maximum `billing_cycle_anchor` Stripe will allow us to set, given a signup date
	 * and billing cycle interval.
	 *
	 * @param int    $interval      Billing cycle interval.
	 * @param string $interval_unit Billing cycle interval unit.
	 * @param string $signup_date   Signup date that can be parsed by `strtotime()`. Will almost always be
	 *                              `now`, but can be overridden for help in unit tests.
	 *
	 * @since 2.0.0
	 * @return DateTime
	 */
	public function get_stripe_max_billing_cycle_anchor($interval, $interval_unit, $signup_date = 'now') {

		try {

			$signup_date = new \DateTimeImmutable($signup_date);

		} catch (Exception $e) {

			$signup_date = new \DateTimeImmutable();

		} // end try;

		$stripe_max_anchor = $signup_date->modify(sprintf('+%d %s', $interval, $interval_unit));

		$proposed_next_bill_date = new \DateTime();

		$proposed_next_bill_date->setTimestamp($signup_date->getTimestamp());

		// Set to first day of the month so we're not dealing with mismatching days of the month.
		$proposed_next_bill_date->setDate($proposed_next_bill_date->format('Y'), $proposed_next_bill_date->format('m'), 1);

		// Now we can safely add 1 interval and still be in the expected month.
		$proposed_next_bill_date->modify(sprintf('+ %d %s', $interval, $interval_unit));

		/*
		 * If the day of the month in the signup date exceeds the total number of days in the proposed month,
		 * set the anchor to the last day of the proposed month - whatever that is.
		 */
		if (date('j', $signup_date->getTimestamp()) > date('t', $proposed_next_bill_date->getTimestamp())) { // phpcs:ignore

			try {

				$stripe_max_anchor = new \DateTime(date('Y-m-t H:i:s', $proposed_next_bill_date->getTimestamp())); // phpcs:ignore

			} catch (\Exception $e) {

				// Silence is golden

			} // end try;

		} // end if;

		return $stripe_max_anchor;

	} // end get_stripe_max_billing_cycle_anchor;

	/**
	 * Get Stripe error from exception
	 *
	 * This converts the exception into a WP_Error object with a localized error message.
	 *
	 * @param Error\Base $e The stripe error object.
	 *
	 * @since 2.0.0
	 * @return WP_Error
	 */
	protected function get_stripe_error($e) {

		$wp_error = new \WP_Error();

		if (method_exists($e, 'getJsonBody')) {

			$body  = $e->getJsonBody();
			$error = $body['error'];

			$wp_error->add($error['code'], $this->get_localized_error_message($error['code'], $e->getMessage()));

		} else {

			$wp_error->add('unknown_error', __('An unknown error has occurred.', 'wp-ultimo'));

		} // end if;

		return $wp_error;

	} // end get_stripe_error;

	/**
	 * Localize common Stripe error messages so they're available for translation.
	 *
	 * @link https://stripe.com/docs/error-codes
	 *
	 * @param string $error_code    Stripe error code.
	 * @param string $error_message Original Stripe error message. This will be returned if we don't have a localized version of
	 *                              the error code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_localized_error_message($error_code, $error_message = '') {

		$errors = wu_stripe_get_localized_error_messages();

		if (!empty($errors[$error_code])) {

			return $errors[$error_code];

		} else {

			// translators: 1 is the error code and 2 the message.
			return sprintf(__('An error has occurred (code: %1$s; message: %2$s).', 'wp-ultimo'), $error_code, $error_message);

		} // end if;

	} // end get_localized_error_message;

	/**
	 * Gives gateways a chance to run things before backwards compatible webhooks are run.
	 *
	 * @since 2.0.8
	 * @return void
	 */
	public function before_backwards_compatible_webhook() {

		if (empty($this->secret_key)) {

			$other_id = $this->get_id() === 'stripe' ? 'stripe-checkout' : 'stripe';

			/*
			 * If we don't have stripe anymore, and only stripe checkout,
			 * We might want to use the keys from stripe checkout here
			 * or vice-versa.
			 */
			$this->setup_api_keys($other_id);

		} // end if;

	} // end before_backwards_compatible_webhook;

	/**
	 * Process webhooks
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function process_webhooks() {

		wu_log_add('stripe', 'Receiving Stripe webhook...');

		/*
		 * PHP Input as object
		 */
		$received_event = wu_get_input();

		// for extra security, retrieve from the Stripe API
		if (!isset($received_event->id)) {

			throw new \Exception(__('Event ID not found.', 'wp-ultimo'));

		} // end if;

		$event_id = $received_event->id;

		$event         = Stripe\Event::retrieve($event_id);
		$payment_event = $event->data->object;

		$membership   = false;
		$payment      = false;
		$customer     = false;
		$invoice      = false;
		$subscription = false;

		/*
		 * Check if we have a customer present.
		 */
		if (empty($payment_event->customer)) {

			throw new Ignorable_Exception(__('Exiting Stripe webhook - no customer attached to event.', 'wp-ultimo'));

		} // end if;

		/*
		 * Try to get an invoice object from the payment event.
		 */
		if (!empty($payment_event->object) && 'invoice' === $payment_event->object) {

			$invoice = $payment_event;

		} elseif (!empty($payment_event->invoice)) {

			$invoice = Stripe\Invoice::retrieve($payment_event->invoice);

		} // end if;

		/*
		 * Now try to get a subscription from the invoice object.
		 */
		if (!empty($invoice->subscription)) {

			$subscription = Stripe\Subscription::retrieve($invoice->subscription);

		} // end if;

		/*
		 * We can also get the subscription by the
		 * object ID in some circumstances.
		 */
		if (empty($subscription) && strpos($payment_event->id, 'sub_') !== false) {

			$subscription = Stripe\Subscription::retrieve($payment_event->id);

		} // end if;

		/*
		 * Retrieve the membership by subscription ID.
		 */
		if (!empty($subscription)) {

			$membership = wu_get_membership_by('gateway_subscription_id', $subscription->id);

		} // end if;

		// Retrieve the membership by payment meta (one-time charges only).
		if (!empty($payment_event->metadata->membership_id)) {

			$membership = wu_get_membership($payment_event->metadata->membership_id);

		} // end if;

    /*
     * Last ditch effort to retrieve a valid membership.
     */
		if (empty($membership) && !empty($invoice)) {

			$amount = $invoice->amount_paid / wu_stripe_get_currency_multiplier();

			$membership = wu_get_membership_by_customer_gateway_id($payment_event->customer, array('stripe', 'stripe-checkout'), $amount);

		} // end if;

		/**
		 * Filters the membership record associated with this webhook.
		 *
		 * This filter was introduced due to conflicts that may arise when the same Stripe customer may
		 * be used on different sites.
		 *
		 * @param \WP_Ultimo\Models\Membership|false $membership The membership object.
		 * @param Stripe\Event                       $event The event received.
		 */
		$membership = apply_filters('wu_stripe_webhook_membership', $membership, $event);

		if (!is_a($membership, '\WP_Ultimo\Models\Membership')) {

			// translators: %s is the customer ID.
			throw new Ignorable_Exception(sprintf(__('Exiting Stripe webhook - membership not found. Customer ID: %s.', 'wp-ultimo'), $payment_event->customer));

		} // end if;

		/*
		 * Set the WP Ultimo customer.
		 */
		$customer = $membership->get_customer();

		/*
		 * Now, we actually get to handle
		 * webhook messages.
		 *
		 * We'll handle 4 cases:
		 *
		 * 1. Customer subscription created - For Stripe Gateway;
		 * 2. Charge Succeeded & Invoice Payment Succeed;
		 * 3. Payment failed;
		 * 4. Subscription deleted.
		 *
		 * First, we'll start customer created.
		 */
		if ($event->type === 'customer.subscription.created') {

			do_action('wu_webhook_recurring_payment_profile_created', $membership, $this);

		} // end if;

    /*
     * Deal with Stripe Checkouts case.
     *
     * On Stripe Checkout, we rely entirely on
     * the webhook call to change the status of things.
     */
		if ($event->type === 'checkout.session.completed') {

			$membership->set_gateway_customer_id($payment_event->customer);

			$membership->set_gateway_subscription_id($payment_event->subscription);

			$membership->set_gateway($this->id);

			$membership->save();

			return true;

		} // end if;

		/*
		 * Next, let's deal with charges that went through!
		 */
		if ($event->type === 'charge.succeeded' || $event->type === 'invoice.payment_succeeded') {

			$payment_data = array(
				'status'  => Payment_Status::COMPLETED,
				'gateway' => 'stripe',
			);

			if ($event->type === 'charge.succeeded') {
				/*
				 * Successful one-time payment
				 */
				if (empty($payment_event->invoice)) {

					$payment_data['total']              = $payment_event->amount / wu_stripe_get_currency_multiplier();
					$payment_data['gateway_payment_id'] = $payment_event->id;

				/*
				 * Subscription payment received.
				 */
				} else {

					$payment_data['total']              = $invoice->amount_due / wu_stripe_get_currency_multiplier();
					$payment_data['gateway_payment_id'] = $payment_event->id;

					if (!empty($payment_event->discount)) {

						$payment_data['discount_code'] = $payment_event->discount->coupon_id;

					} // end if;

				} // end if;

			} // end if;

			/*
			 * Let's check if we have the payment
			 * created already. We only want to create a new
			 * one if we don't have one already.
			 */
			$gateway_payment_id = $payment_event->id;

			$payment = wu_get_payment_by('gateway_payment_id', $gateway_payment_id);

			$expiration = false;

			/*
			 * Payment does not exist.
			 */
			if (!empty($gateway_payment_id) && !$payment) {
				/*
				 * Checks if we have the data about a subscription.
				 */
				if (!empty($subscription)) {

					$membership->set_recurring(true);

					$membership->set_gateway_subscription_id($subscription->id);

					/*
						* Set the new expiration date.
						* We use the `current_period_end` as our base and force the time to be 23:59:59 that day.
						* However, this must be at least two hours after `current_period_end` to ensure there's
						* plenty of time between the next invoice being generated and actually being paid/finalized.
						* Stripe usually does this within 1 hour, but we're using 2 to be on the safe side and
						* account for delays.
						*/
					$renewal_date = new \DateTime();
					$renewal_date->setTimestamp($subscription->current_period_end);
					$renewal_date->setTime(23, 59, 59);

					/*
					 * Estimated charge date is 2 hours
					 * after `current_period_end`.
					 */
					$stripe_estimated_charge_timestamp = $subscription->current_period_end + (2 * HOUR_IN_SECONDS);

					if ($stripe_estimated_charge_timestamp > $renewal_date->getTimestamp()) {

						$renewal_date->setTimestamp($stripe_estimated_charge_timestamp);

					} // end if;

					/*
					 * Set the expiration.
					 */
					$expiration = $renewal_date->format('Y-m-d H:i:s');

				} // end if;

				/*
				 * Checks for a pending payment on the membership.
				 */
				$pending_payment = $membership->get_last_pending_payment();

				if (!empty($pending_payment)) {
					/*
					 * Completing a pending payment.
					 */
					$pending_payment->attributes($payment_data);

					$pending_payment->save();

					$payment = $pending_payment;

				} else {
					/*
					 * These must be retrieved after the status
					 * is set to active in order for upgrades to work properly
					 */
					$payment_data['transaction_type'] = 'renewal';
					$payment                          = wu_create_payment($payment_data);

				} // end if;

				/*
				 * Tell the gateway to do their stuff.
				 */
				$this->trigger_payment_processed($payment, $membership);

        /*
				 * Renewals the membership
				 */
				$membership->add_to_times_billed(1);
				$membership->renew($membership->is_recurring(), 'active', $expiration);

				return true;

			} elseif (!empty($gateway_payment_id) && $payment) {
				/*
				 * The payment already exists.
				 *
				 * Throws to inform that
				 * we have a duplicate payment.
				 */
				throw new Ignorable_Exception(__('Duplicate payment.', 'wp-ultimo'));

			} // end if;

		} // end if;

		/*
		 * Next, let's deal with charges that went through!
		 */
		if ($event->type === 'charge.refunded') {

			$payment_data = array(
				'gateway' => 'stripe',
			);

			$payment_id = $payment_event->metadata->payment_id;

			$payment = wu_get_payment($payment_id);

			if (empty($payment)) {

				throw new Ignorable_Exception(__('Payment not found on refund webhook call.', 'wp-ultimo'));

			} // end if;

			$is_refundable = in_array($payment->get_status(), wu_get_refundable_payment_types(), true);

			if (!$is_refundable) {

				throw new Ignorable_Exception(__('Payment is not refundable.', 'wp-ultimo'));

			} // end if;

			/*
			 * Let's address the type.
			 */
			$amount = $payment_event->amount_refunded * wu_stripe_get_currency_multiplier();

			/*
			 * Actually process the refund
			 * using the helper method.
			 */
			$status = $payment->refund($amount);

			return $status;

		} // end if;

		/*
		 * Failed payments.
		 */
		if ($event->type === 'invoice.payment_failed') {

			$this->webhook_event_id = $event->id;

			// Make sure this invoice is tied to a subscription and is the user's current subscription.
			if (!empty($event->data->object->subscription) && $event->data->object->subscription === $membership->get_gateway_subscription_id()) {

				do_action('wu_recurring_payment_failed', $membership, $this);

			} // end if;

			do_action('wu_stripe_charge_failed', $payment_event, $event, $membership);

			return true;

		} // end if;

		/*
		 * Cancelled / failed subscription.
		 */
		if ($event->type === 'customer.subscription.deleted') {

			wu_log_add('stripe', 'Processing Stripe customer.subscription.deleted webhook.');

			if ($payment_event->id === $membership->get_gateway_subscription_id()) {
				/*
				 * If this is a completed payment plan,
				 * we can skip any cancellation actions.
				 */
				if (!$membership->is_forever_recurring() && $membership->at_maximum_renewals()) {

					return;

				} // end if;

				if ($membership->is_active()) {

					$membership->cancel();

					$membership->add_note(__('Membership cancelled via Stripe webhook.', 'wp-ultimo'));

				} else {

					wu_log_add('stripe', sprintf('Membership #%d is not active - not cancelling account.', $membership->get_id()));

				} // end if;

				return true;

			} else {

				wu_log_add('stripe', sprintf('Payment event ID (%s) doesn\'t match membership\'s merchant subscription ID (%s).', $payment_event->id, $membership->get_gateway_subscription_id()), true);

			} // end if;

		} // end if;

	} // end process_webhooks;

	/**
	 * Get saved card options for this customers.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_saved_card_options() {

		if (!is_user_logged_in()) {

			return array();

		} // end if;

		$options = array();

		$user_id = isset($this->customer) && $this->customer ? $this->customer->get_user_id() : false;

		$saved_payment_methods = $this->get_user_saved_payment_methods($user_id);

		foreach ($saved_payment_methods as $saved_payment_method) {

			$options[$saved_payment_method->id] = sprintf(
				// translators: 1 is the card brand (e.g. VISA), and 2 is the last 4 digits.
				__('%1$s ending in %2$s', 'wp-ultimo'),
				strtoupper($saved_payment_method->card->brand),
				$saved_payment_method->card->last4
			);

		} // end foreach;

		return $options;

	} // end get_saved_card_options;

	/**
	 * Add credit card fields.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function fields() {

		return '';

	} // end fields;

	/**
	 * Load fields for the Update Billing Card form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function update_card_fields() { // phpcs:disable ?>

		<div class="wu-gateway-new-card-fields">

			<fieldset id="wu-card-name-wrapper" class="wu_card_fieldset">
				<p id="wu_card_name_wrap">
					<label for="wu-update-card-name"><?php _e('Name on Card', 'wp-ultimo'); ?></label>
					<input type="text" size="20" id="wu-update-card-name" name="wu_card_name" class="wu_card_name card-name" />
				</p>
			</fieldset>

			<fieldset id="wu-card-wrapper" class="wu_card_fieldset">
				<div id="wu_card_wrap">
					<div id="wu-card-element"></div>
				</div>
			</fieldset>

		</div>

		<div id="wu-card-element-errors"></div>

		<?php // phpcs:enable

	} // end update_card_fields;

	/**
	 * Register stripe scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		if (empty($this->publishable_key)) {

			return;

		} // end if;

		wp_register_script('wu-stripe-sdk', 'https://js.stripe.com/v3/', false, 'v3');

		wp_register_script('wu-stripe', wu_get_asset('gateways/stripe.js', 'js'), array('wu-checkout', 'wu-stripe-sdk'), wu_get_version(), true);

		$saved_cards = $this->get_saved_card_options();

		wp_localize_script('wu-stripe', 'wu_stripe', array(
			'pk_key'                  => $this->publishable_key,
			'request_billing_address' => $this->request_billing_address,
			'add_new_card'            => empty($saved_cards),
			'payment_method'          => empty($saved_cards) ? 'add-new' : current(array_keys($saved_cards)),
		));

		wp_enqueue_script('wu-stripe');

	} // end register_scripts;

	/**
	 * Maybe create a new tax rate on Stripe
	 *
	 * @since 2.0.0
	 *
	 * @param array $args The tax rate arguments.
	 * @return string
	 */
	public function maybe_create_tax_rate($args) {

		$slug = strtolower(sprintf('%s-%s-%s', $args['country'], $args['tax_rate'], $args['type']));

		static $cache = array();

		if (wu_get_isset($cache, $slug)) {

			return wu_get_isset($cache, $slug);

		} // end if;

		$stripe_tax_rates = Stripe\TaxRate::all();

		foreach ($stripe_tax_rates as $stripe_tax_rate) {

			if (isset($stripe_tax_rate->metadata->tax_rate_id) && $stripe_tax_rate->metadata->tax_rate_id === $slug) {

				$cache[$slug] = $stripe_tax_rate->id;

				return $stripe_tax_rate->id;

			} // end if;

		} // end foreach;

		$args = array(
			'display_name' => $args['title'],
			'description'  => $args['title'],
			'jurisdiction' => $args['country'],
			'percentage'   => absint($args['tax_rate']),
			'inclusive'    => false,
			'metadata'     => array(
				'tax_rate_id' => $slug,
			),
		);

		try {

			$tax_rate = Stripe\TaxRate::create($args);

			return $tax_rate->id;

		} catch (Exception $e) {

			// Silence is golden.
			return '';

		} // end try;

	} // end maybe_create_tax_rate;

	/**
	 * Checks to see if a plan exists with the provided arguments, and if so, returns the ID of
	 * that plan. If not, a new plan is created.
	 *
	 * This method differs from create_plan() and plan_exists() because it doesn't expect
	 * a membership level ID number. This allows for the creation of plans that may not be
	 * exactly based on a membership level's parameters.
	 *
	 * @param array $args           {
	 *                              Array of arguments.
	 *
	 * @type string $name           Required. Name of the plan.
	 * @type float  $price          Required. Price each interval.
	 * @type string $interval       Optional. Billing interval (i.e ."day", "month", "year"). Default is "month".
	 * @type int    $interval_count Optional. Interval count. Default is "1".
	 * @type string $currency       Optional. Currency. Defaults to site currency.
	 * @type string $id             Optional. Plan ID. Automatically generated based on arguments.
	 *                    }
	 *
	 * @since 2.0.0
	 * @return string|WP_Error Plan ID on success or WP_Error on failure.
	 */
	public function maybe_create_plan($args) {

		$args = wp_parse_args($args, array(
			'name'           => '',
			'price'          => 0.00,
			'interval'       => 'month',
			'interval_count' => 1,
			'currency'       => strtolower(wu_get_setting('currency_symbol', 'USD')),
			'id'             => '',
		));

		// Name and price are required.
		if (empty($args['name']) || empty($args['price'])) {

			return new \WP_Error('missing_name_price', __('Missing plan name or price.', 'wp-ultimo'));

		} // end if;

		/*
		 * Create a new object that looks like a membership level object.
		 * We do this because generate_plan_id() expects a membership level object but we
		 * don't actually have one.
		 */
		if (empty($args['id'])) {

			$plan_level                = new \stdClass();
			$plan_level->name          = $args['name'];
			$plan_level->price         = $args['price'];
			$plan_level->duration      = $args['interval_count'];
			$plan_level->duration_unit = $args['interval'];
			$plan_id                   = $this->generate_plan_id($plan_level);

		} else {

			$plan_id = $args['id'];

		} // end if;

		if (empty($plan_id)) {

			return new \WP_Error('empty_plan_id', __('Empty plan ID.', 'wp-ultimo'));

		} // end if;

		// Convert price to Stripe format.
		$price = round($args['price'] * wu_stripe_get_currency_multiplier(), 0);

		// First check to see if a plan exists with this ID. If so, return that.
		try {

			$membership_level = isset($plan_level) ? $plan_level : new \stdClass();

			/**
			 * Filters the ID of the plan to check for. If this exists, the new subscription will
			 * use this plan.
			 *
			 * @param string $plan_id          ID of the Stripe plan to check for.
			 * @param object $membership_level Membership level object.
			 */
			$existing_plan_id = apply_filters('wu_stripe_existing_plan_id', $plan_id, $membership_level);

			$plan = Stripe\Plan::retrieve($existing_plan_id);

			return $plan->id;

		} catch (\Exception $e) {

			// silence is golden

		} // end try;

		// Otherwise, create a new plan.
		try {

			$product = Stripe\Product::create(array(
				'name' => $args['name'],
				'type' => 'service'
			) );

			$plan = Stripe\Plan::create(array(
				'amount'         => $price,
				'interval'       => $args['interval'],
				'interval_count' => $args['interval_count'],
				'currency'       => $args['currency'],
				'id'             => $plan_id,
				'product'        => $product->id
			) );

			// plan successfully created
			return $plan->id;

		} catch (\Exception $e) {

			wu_log_add('stripe', sprintf('Error creating Stripe plan. Code: %s; Message: %s', $e->getCode(), $e->getMessage()));

			return new \WP_Error('stripe_exception', sprintf('Error creating Stripe plan. Code: %s; Message: %s', $e->getCode(), $e->getMessage()));

		} // end try;

	} // end maybe_create_plan;

	/**
	 * Generate a Stripe plan ID string based on a membership level
	 *
	 * The plan name is set to {levelname}-{price}-{duration}{duration unit}
	 * Strip out invalid characters such as '@', '.', and '()'.
	 * Similar to WP core's sanitize_html_class() & sanitize_key() functions.
	 *
	 * @param object $product_info The product info object.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function generate_plan_id($product_info) {

		$product_name = strtolower(str_replace(' ', '', sanitize_title_with_dashes($product_info->name)));

		$plan_id = sprintf('%s-%s-%s', $product_name, $product_info->price, $product_info->duration . $product_info->duration_unit);

		$plan_id = preg_replace('/[^a-z0-9_\-]/', '-', $plan_id);

		return $plan_id;

	} // end generate_plan_id;

	/**
	 * Get the saved Stripe payment methods for a given user ID.
	 *
	 * @since 2.0.0
	 *
	 * @throws \Exception, When info is wrong.
	 * @throws \Exception When info is wrong 2.
	 * @return PaymentMethod[]|array
	 */
	public function get_user_saved_payment_methods() {

		$customer = wu_get_current_customer();

		if (!$customer) {

			return array();

		} // end if;

		$customer_id = $customer->get_id();

		try {
			/*
			 * Declare static to prevent multiple calls.
			 */
			static $existing_payment_methods;

			if (!is_null($existing_payment_methods) && array_key_exists($customer_id, $existing_payment_methods)) {

				return $existing_payment_methods[$customer_id];

			} // end if;

			$customer_payment_methods = array();

			$stripe_customer_id = \WP_Ultimo\Models\Membership::query(array(
				'customer_id' => $customer_id,
				'search'      => 'cus_*',
				'fields'      => array('gateway_customer_id'),
			));

			$stripe_customer_id = current(array_column($stripe_customer_id, 'gateway_customer_id'));

			$payment_methods = Stripe\PaymentMethod::all(array(
				'customer' => $stripe_customer_id,
				'type'     => 'card'
			));

			foreach ($payment_methods->data as $payment_method) {

				$customer_payment_methods[$payment_method->id] = $payment_method;

			} // end foreach;

			$existing_payment_methods[$customer_id] = $customer_payment_methods;

			return $existing_payment_methods[$customer_id];

		} catch (\Throwable $e) {

			return array();

		} // end try;

	} // end get_user_saved_payment_methods;

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

		$route = $this->test_mode ? '/test' : '/';

		$path = 'payments';

		if (strpos($gateway_payment_id, 'in_') === 0) {

			$path = 'invoices';

		} // end if;

		return sprintf('https://dashboard.stripe.com%s/%s/%s', $route, $path, $gateway_payment_id);

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

		$route = $this->test_mode ? '/test' : '/';

		return sprintf('https://dashboard.stripe.com%s/subscriptions/%s', $route, $gateway_subscription_id);

	} // end get_subscription_url_on_gateway;

	/**
	 * Returns the external link to view the customer on the gateway.
	 *
	 * Return an empty string to hide the link element.
	 *
	 * @since 2.0.7
	 *
	 * @param string $gateway_customer_id The gateway customer id.
	 * @return string.
	 */
	public function get_customer_url_on_gateway($gateway_customer_id) {

		$route = $this->test_mode ? '/test' : '/';

		return sprintf('https://dashboard.stripe.com%s/customers/%s', $route, $gateway_customer_id);

	} // end get_customer_url_on_gateway;

} // end class Base_Stripe_Gateway;
