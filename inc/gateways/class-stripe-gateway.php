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
use \WP_Ultimo\Dependencies\Stripe\Stripe;
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
class Stripe_Gateway extends Base_Gateway {

	/**
	 * Holds the ID of a given gateway.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'stripe';

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
	 * Get things going
	 *
	 * @access public
	 * @since  2.1
	 * @return void
	 */
	public function init() {

		$this->supports[] = 'one-time';
		$this->supports[] = 'recurring';
		$this->supports[] = 'fees';
		$this->supports[] = 'gateway-submits-form';
		$this->supports[] = 'trial';
		$this->supports[] = 'ajax-payment';
		$this->supports[] = 'card-updates';

		$this->request_billing_address = wu_get_setting('stripe_should_collect_billing_address', true);

		$this->test_mode = wu_get_setting('stripe_sandbox_mode', true);

		if ($this->test_mode) {

			$this->publishable_key = wu_get_setting('stripe_test_pk_key', '');
			$this->secret_key      = wu_get_setting('stripe_test_sk_key', '');

		} else {

			$this->publishable_key = wu_get_setting('stripe_live_pk_key', '');
			$this->secret_key      = wu_get_setting('stripe_live_sk_key', '');

		} // end if;

		Stripe::setApiKey($this->secret_key);

		Stripe::setApiVersion('2019-05-16');

		if (method_exists('Stripe', 'setAppInfo')) {

			Stripe::setAppInfo('WordPress WP Ultimo', wu_get_version(), esc_url(site_url()));

		} // end if;

	} // end init;

	/**
	 * Allows Gateways to override the gateway title.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_public_title() {

		return wu_get_setting('stripe_public_title', __('Credit Card', 'wp-ultimo'));

	} // end get_public_title;

	/**
	 * Adds the Stripe Gateway settings to the settings screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function settings() {

		wu_register_settings_field('payment-gateways', 'stripe_header', array(
			'title'           => __('Stripe', 'wp-ultimo'),
			'desc'            => __('Use the settings section below to configure Stripe as a payment method.', 'wp-ultimo'),
			'type'            => 'header',
			'show_as_submenu' => true,
			'require'         => array(
				'active_gateways' => 'stripe',
			),
		));

		wu_register_settings_field('payment-gateways', 'stripe_public_title', array(
			'title'   => __('Stripe Public Name', 'wp-ultimo'),
			'tooltip' => __('The name to display on the payment method selection field. By default, "Credit Card" is used.', 'wp-ultimo'),
			'type'    => 'text',
			'default' => __('Credit Card', 'wp-ultimo'),
			'require' => array(
				'active_gateways' => 'stripe',
			),
		));

		wu_register_settings_field('payment-gateways', 'stripe_sandbox_mode', array(
			'title'     => __('Stripe Sandbox Mode', 'wp-ultimo'),
			'desc'      => __('Toggle this to put Stripe on sandbox mode. This is useful for testing and making sure Stripe is correctly setup to handle your payments.', 'wp-ultimo'),
			'type'      => 'toggle',
			'default'   => 1,
			'html_attr' => array(
				'v-model' => 'stripe_sandbox_mode',
			),
			'require'   => array(
				'active_gateways' => 'stripe',
			),
		));

		wu_register_settings_field('payment-gateways', 'stripe_test_pk_key', array(
			'title'       => __('Stripe Test Publishable Key', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the TEST keys, not the live ones.', 'wp-ultimo'),
			'placeholder' => __('pk_test_***********', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'stripe',
				'stripe_sandbox_mode' => 1,
			),
		));

		wu_register_settings_field('payment-gateways', 'stripe_test_sk_key', array(
			'title'       => __('Stripe Test Secret Key', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the TEST keys, not the live ones.', 'wp-ultimo'),
			'placeholder' => __('sk_test_***********', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'stripe',
				'stripe_sandbox_mode' => 1,
			),
		));

		wu_register_settings_field('payment-gateways', 'stripe_live_pk_key', array(
			'title'       => __('Stripe Live Publishable Key', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the LIVE keys, not the test ones.', 'wp-ultimo'),
			'placeholder' => __('pk_live_***********', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'stripe',
				'stripe_sandbox_mode' => 0,
			),
		));

		wu_register_settings_field('payment-gateways', 'stripe_live_sk_key', array(
			'title'       => __('Stripe Live Secret Key', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the LIVE keys, not the test ones.', 'wp-ultimo'),
			'placeholder' => __('sk_live_***********', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'stripe',
				'stripe_sandbox_mode' => 0,
			),
		));

		wu_register_settings_field('payment-gateways', 'stripe_should_collect_billing_address', array(
			'title'   => __('Collect Billing Address', 'wp-ultimo'),
			'desc'    => __('Enabling this option will add the Billing Address step to the Stripe checkout form. This information will also be saved by WP Ultimo.', 'wp-ultimo'),
			'type'    => 'toggle',
			'default' => 0,
			'require' => array(
				'active_gateways' => 'stripe',
			),
		));

		wu_register_settings_field('payment-gateways', 'stripe_webhook_note', array(
			'type'            => 'html',
			'wrapper_classes' => 'wu-m-0 wu-p-0',
			'content'         => function() {

				$message = __('Whenever you change your Stripe settings, WP Ultimo will automatically check the webhook URLs on your Stripe account to make sure we get notified about changes in subscriptions and payments.', 'wp-ultimo');

				return sprintf('<div class="wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded wu--mt-4">%s</div>', $message);

			},
			'require'         => array(
				'active_gateways' => 'stripe',
			),
		));

	} // end settings;

	/**
	 * Checks if we already have a webhook listener installed.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Dependencies\Stripe\WebhookEndpoint|\WP_Error|false
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
	 * Create a payment intent or setup intent.
	 *
	 * @since 2.0.0
	 * @return array|WP_Error
	 */
	public function process_ajax_signup() {

		$intent          = null;
		$stripe_customer = $this->get_or_create_customer($this->customer->get_id(), $this->customer->get_user_id());

		if (is_wp_error($stripe_customer)) {

			return new \WP_Error($stripe_customer->get_error_code(), sprintf(__('Error creating Stripe customer: %s', 'wp-ultimo'), $stripe_customer->get_error_message()));

		} // end if;

		$intent_args = array(
			'customer' => $stripe_customer->id,
			'metadata' => $this->get_customer_metadata(),
		);

		// Maybe add saved card.
		if (!empty($_POST['payment_method']) && 'new' !== $_POST['payment_method']) {

			$intent_args['payment_method'] = sanitize_text_field($_POST['payment_method']);

		} // end if;

		$intent_options         = array();
		$stripe_connect_user_id = ''; // @todo implement connect support
		$payment_intent_id      = $this->payment->get_meta('stripe_payment_intent_id');
		$existing_intent        = false;

		if (!empty($stripe_connect_user_id)) {

			$options['stripe_account'] = $stripe_connect_user_id;

		} // end if;

		try {

			if (!empty($payment_intent_id) && 'pi_' === substr($payment_intent_id, 0, 3)) {

				$existing_intent = \WP_Ultimo\Dependencies\Stripe\PaymentIntent::retrieve($payment_intent_id);

			} elseif (!empty($payment_intent_id) && 'seti_' === substr($payment_intent_id, 0, 5)) {

				$existing_intent = \WP_Ultimo\Dependencies\Stripe\SetupIntent::retrieve($payment_intent_id);

			} // end if;

			// We can't update canceled intents.
			if (!empty($existing_intent) && 'canceled' === $existing_intent->status) {

				$existing_intent = false;

			} // end if;

			if (!empty($this->initial_amount)) {

				// Create a payment intent.
				$intent_args = wp_parse_args($intent_args, array(
					'amount'              => $this->initial_amount * wu_stripe_get_currency_multiplier(),
					'confirmation_method' => 'automatic',
					'confirm'             => false,
					'currency'            => strtolower(wu_get_setting('currency_symbol', 'USD')),
					'setup_future_usage'  => 'off_session',
				));

				/**
				 * Filters the payment intent arguments.
				 *
				 * @since 2.0
				 */
				$intent_args = apply_filters('wu_stripe_create_payment_intent_args', $intent_args, $this);

				if (!empty($existing_intent) && 'payment_intent' === $existing_intent->object) {

					$idempotency_args                  = $intent_args;
					$idempotency_args['update']        = true;
					$intent_options['idempotency_key'] = wu_stripe_generate_idempotency_key($idempotency_args);

					// Unset some options we can't update.
					$unset_args = array('confirmation_method', 'confirm');

					foreach ($unset_args as $unset_arg) {

						if (isset($intent_args[$unset_arg])) {

							unset($intent_args[$unset_arg]);

						} // end if;

					} // end foreach;

					$intent = \WP_Ultimo\Dependencies\Stripe\PaymentIntent::update($existing_intent->id, $intent_args, $intent_options);

				} else {

					$intent_options['idempotency_key'] = wu_stripe_generate_idempotency_key($intent_args);

					$intent = \WP_Ultimo\Dependencies\Stripe\PaymentIntent::create($intent_args, $intent_options);

				} // end if;

			} else {

				// Create a setup intent.
				$intent_args = wp_parse_args($intent_args, array(
					'usage' => 'off_session'
				));

				if (empty($existing_intent) || 'setup_intent' !== $existing_intent->object) {

					$intent_options['idempotency_key'] = wu_stripe_generate_idempotency_key($intent_args);
					$intent                            = \WP_Ultimo\Dependencies\Stripe\SetupIntent::create($intent_args, $intent_options);
				} // end if;

			} // end if;

		} catch (\WP_Ultimo\Dependencies\Stripe\Error\Base $e) {

			return $this->get_stripe_error($e);

		} catch (\Exception $e) {

			return new \WP_Error($e->getCode(), $e->getMessage());

		} // end try;

		// Store the payment intent ID with the payment.
		$this->payment->update_meta('stripe_payment_intent_id', sanitize_text_field($intent->id));

		// Add the client secret to the JSON success data.
		return array(
			'stripe_client_secret' => sanitize_text_field($intent->client_secret),
			'stripe_intent_type'   => sanitize_text_field($intent->object),
		);

	} // end process_ajax_signup;

	/**
	 * Process registration
	 *
	 * @since  3.0.0
	 * @throws \Exception When something goes wrong.
	 * @return void
	 */
	public function process_signup() {

		$payment_intent_id = $this->payment->get_meta('stripe_payment_intent_id');

		if (empty($payment_intent_id)) {

			$this->handle_processing_error(new \WP_Error('missing_stripe_payment_intent', __('Missing Stripe payment intent, please try again or contact support if the issue persists.', 'wp-ultimo')));

		} // end if;

		try {

			if (empty($this->initial_amount)) {

				$payment_intent = \WP_Ultimo\Dependencies\Stripe\SetupIntent::retrieve($payment_intent_id);

			} else {

				$payment_intent = \WP_Ultimo\Dependencies\Stripe\PaymentIntent::retrieve($payment_intent_id);

			} // end if;

		} catch (\Exception $e) {

			$this->handle_processing_error($e);

		} // end try;

		/**
		 * Set up Stripe customer record and attach it to the membership.
		 */
		$customer = $this->get_or_create_customer($this->customer->get_id(), $this->user_id, $payment_intent->customer);

		/**
		 * Update customer record to set description and metadata.
		 */
		try {

			$description = sprintf(__('User ID: %1$d - User Email: %2$s', 'wp-ultimo'), $this->user_id, $this->email);

			if (strlen($description) > 350) {

				$description = substr($description, 0, 350);

			} // end if;

			\WP_Ultimo\Dependencies\Stripe\Customer::update($customer->id, array(
				'description' => sanitize_text_field($description),
				'metadata'    => array(
					'user_id'     => absint($this->user_id),
					'email'       => sanitize_text_field($this->email),
					'customer_id' => absint($this->customer->get_id())
				)
			));

		} catch (\Exception $e) {

			$error = sprintf('Stripe Gateway: Failed to update Stripe customer metadata while activating membership #%d. Message: %s', $this->membership->get_id(), $e->getMessage());

			wu_log_add('stripe', $error);

		} // end try;

		/**
		 * Initial payment / authorization was successful. Let's get some post-payment stuff done.
		 */

		/**
		 * Complete the payment record if we have a confirmed payment. This activates the membership.
		 * We also attempt to get the transaction ID from the payment intent charge.
		 *
		 * If the charge isn't actually complete now, the webhook will pick it up later.
		 */
		$payment_data = array(
			'payment_type'       => 'Credit Card',
			'gateway_payment_id' => ''
		);

		if ('payment_intent' == $payment_intent->object && !empty($payment_intent->charges->data[0]['id']) && 'succeeded' === $payment_intent->charges->data[0]['status']) {

			// Set the transaction ID from the charge.
			$payment_data['gateway_payment_id'] = sanitize_text_field($payment_intent->charges->data[0]['id']);
			$payment_data['status']             = 'completed';

		} elseif ('setup_intent' == $payment_intent->object && empty($this->initial_amount)) {

			// We'll get the transaction ID from the subscription later.
			$payment_data['status'] = 'completed';

		} else {

			wu_log_add('stripe', sprintf('Stripe Gateway: payment not immediately verified for intent ID %s - waiting on webhook.', $payment_intent->id));

		} // end if;

		$this->payment->attributes($payment_data);

		$this->payment->save();

		/**
		 * Get the current gateway subscription ID, wipe that value from the membership record, then cancel the
		 * subscription. in Stripe.
		 *
		 * We do this to account for cases where someone might be able to renew but they still have an "active"
		 * subscription in Stripe. One example might be if their previous subscription becomes past-due (but not
		 * cancelled) due to the renewal payment failing. Instead of the customer updating their payment method,
		 * they've manually renewed.
		 *
		 * @link https://github.com/restrictcontentpro/restrict-content-pro/issues/2530
		 */
		// $current_sub_id = $this->membership->get_gateway_subscription_id();

		// if ( !empty( $current_sub_id ) && false !== strpos( $this->membership->get_gateway(), 'stripe' ) && 'sub_' === substr( $current_sub_id, 0, 4 ) ) {

		// try {

		// $subscription = \WP_Ultimo\Dependencies\Stripe\Subscription::retrieve( $current_sub_id );

		// $this->membership->update( array(
		// 'gateway_subscription_id' => ''
		// ) );

		// $subscription->cancel();

		// } catch ( \Exception $e ) {

		// wu_log( sprintf( 'Stripe Gateway: Subscription cleanup failed for user #%d. Subscription ID: %s. Message: %s', $this->user_id, $current_sub_id, $e->getMessage() ), true );

		// } // end try;

		// } // end if;

		/*
		 * We need to refresh our local membership variable because updating the payment record above will have modified it.
		 * This feels hacky AF but I'm doing it for now.
		 */
		// $this->membership = wu_get_membership( $this->membership->get_id() );

		/**
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
				$subscriptions = $customer->subscriptions->all( array(
					'expand' => array( 'data.plan.product' )
				) );

				foreach ( $subscriptions->data as $subscription ) {

					// Cancel subscriptions with the RCP metadata present and matching member ID.
					if ( !empty( $subscription->metadata ) && !empty( $subscription->metadata['wu_subscription_level_id'] ) && $this->user_id == $subscription->metadata['wu_member_id'] ) {

						$subscription->cancel();

						$cancelled_subscriptions[] = $subscription->id;

						wu_log( sprintf( 'Stripe Gateway: Cancelled Stripe subscription %s.', $subscription->id ) );

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

							wu_log( sprintf( 'Stripe Gateway: Cancelled Stripe subscription %s.', $subscription->id ) );

						} // end if;

					} // end if;

				} // end foreach;

			} catch ( \Exception $e ) {

				wu_log( sprintf( 'Stripe Gateway: Subscription cleanup failed for user #%d. Message: %s', $this->user_id, $e->getMessage() ), true );

			} // end try;

		} // end if;

		/**
		 * Attach the payment method to the customer so we can use it again.
		 */
		try {

			$payment_method = \WP_Ultimo\Dependencies\Stripe\PaymentMethod::retrieve($payment_intent->payment_method);

			if (empty($payment_method->customer)) {

				$payment_method->attach(array(
					'customer' => $customer->id
				));

			} // end if;

			\WP_Ultimo\Dependencies\Stripe\Customer::update($customer->id, array(
				'invoice_settings' => array(
					'default_payment_method' => $payment_intent->payment_method,
				),
			));

			/*
			 * Dedupe payment methods.
			 * If someone re-registers with the same card details they've used in the past, Stripe
			 * will actually create a whole new payment method object with the same fingerprint.
			 * This could result in the same card being added to the customer's payment methods in
			 * Stripe, which is kind of annoying. So we dedupe them to make sure one customer only
			 * has each payment method listed once. Hopefully Stripe will handle this automatically
			 * in the future.
			 *
			 * @link https://github.com/stripe/stripe-payments-demo/issues/45
			 */
			$customer_payment_methods = \WP_Ultimo\Dependencies\Stripe\PaymentMethod::all(array(
				'customer' => $customer->id,
				'type'     => 'card'
			));

			if (!empty($customer_payment_methods->data)) {

				foreach ($customer_payment_methods->data as $existing_method) {

					// Detach if the fingerprint matches but payment method ID is different.
					if ($existing_method->card->fingerprint === $payment_method->card->fingerprint && $existing_method->id != $payment_method->id) { // phpcs:ignore

						$existing_method->detach();

					} // end if;

				} // end foreach;

			} // end if;

		} catch (\Exception $e) {

			$error = sprintf('Stripe Gateway: Failed to attach payment method to customer while activating membership #%d. Message: %s', 0, $e->getMessage());

			wu_log_add('stripe', $error);

		} // end try;

		if ($this->auto_renew) {

			/**
			 * Set up a recurring subscription in Stripe with a delayed start date.
			 *
			 * All start dates are delayed one cycle because we use a one-time payment for the first charge.
			 */

			$plans = array();

			$all_products = $this->cart->get_all_products();

			foreach ($all_products as $product) {

				$amount = $product->get_amount();

				if ($this->discount_codes) {

					$discount_code = current($this->discount_codes);

					if ($discount_code->get_apply_to_renewals()) {

						$amount = wu_get_discounted_price($amount, $discount_code->get_value(), $discount_code->get_type(), false);

					} // end if;

				} // end if;

				try {

					// Retrieve or create the plan.
					$plan_id = $this->maybe_create_plan(array(
						'name'           => $product->get_name(),
						'price'          => $amount,
						'interval'       => $product->get_duration_unit(),
						'interval_count' => $product->get_duration(),
					));

					if (is_wp_error($plan_id)) {

						throw new \Exception($plan_id->get_error_message());

					} // end if;

					$plans[$plan_id] = array(
						'plan' => $plan_id,
					);

				} catch (\Exception $e) {

					$error = sprintf('Stripe Gateway: Failed to create subscription for membership #%d. Message: %s', $this->membership->get_id(), $e->getMessage());

					wu_log_add('stripe', $error);

					$this->membership->set_recurring(false);

				} // end try;

				/* Handles Taxes */
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

			try {

				// Set up base subscription args.
				if (!empty($this->subscription_start_date)) {

					// Use subscription start date if provided. This will be a free trial.
					wu_log_add('stripe', sprintf('Stripe Gateway: Using subscription start date for subscription: %s', $this->subscription_start_date));

					$start_date = strtotime($this->subscription_start_date, current_time('timestamp'));

				} else {

					// Otherwise, use the calculated expiration date of the membership, modified to current time instead of 23:59.
					$base_date = $this->memberships[0]->calculate_expiration(true);

					wu_log_add('stripe', sprintf('Stripe Gateway: Using newly calculated expiration for subscription start date: %s', $base_date));

					$timezone     = get_option('timezone_string');
					$timezone     = !empty( $timezone ) ? $timezone : 'UTC';
					$datetime     = new \DateTime($base_date, new \DateTimeZone($timezone));
					$current_time = getdate();
					$datetime->setTime($current_time['hours'], $current_time['minutes'], $current_time['seconds']);
					$start_date = $datetime->getTimestamp() - HOUR_IN_SECONDS; // Reduce by 60 seconds to account for inaccurate server times.

				} // end if;

				$sub_args = array(
					'default_payment_method' => $payment_method->id,
					'items'                  => array_values($plans),
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
				 * @link https://github.com/restrictcontentpro/restrict-content-pro/issues/2503
				 */

				$stripe_max_anchor = $this->get_stripe_max_billing_cycle_anchor($this->length, $this->length_unit, 'now');

				if ($this->is_trial() || $start_date > $stripe_max_anchor->getTimestamp()) {

					$sub_args['trial_end'] = $start_date;

					wu_log_add('stripe', sprintf('Stripe Gateway: Creating subscription with %s start date via trial_end.', $start_date));

				} else {

					$sub_args['billing_cycle_anchor'] = $start_date;

					$sub_args['prorate'] = false;

					wu_log_add('stripe', sprintf('Stripe Gateway: Creating subscription with %s start date via billing_cycle_anchor.', $start_date));

				} // end if;

				$set_anchor = isset($sub_args['billing_cycle_anchor']);

				$sub_options = array(
					'idempotency_key' => wu_stripe_generate_idempotency_key($sub_args)
				);

				$stripe_connect_user_id = false;

				if (!empty($stripe_connect_user_id)) {

					$sub_options['stripe_account'] = $stripe_connect_user_id;

				} // end if;

				/**
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

					// If we set an anchor, remove that, because this means the customer has set their own `trial_end`.
					if ($set_anchor) {

						unset($sub_args['billing_cycle_anchor']);

					} else {

						// We set a trial, which means the customer has set their own `billing_cycle_anchor`.
						unset($sub_args['trial_end']);

					} // end if;

				} // end if;

				// Create the subscription.
				$subscription = $customer->subscriptions->create($sub_args, $sub_options);

				// Link the Stripe subscription to the RCP membership.
				foreach ($this->memberships as $membership) {

					$membership->set_gateway_subscription_id($subscription->id);

					$membership->set_gateway_customer_id($customer->id);

					$membership->set_gateway('stripe');

					$membership->renew(true, 'active');

					$times_billed = $membership->get_times_billed();

					$membership->set_times_billed($times_billed + 1);

					$membership->save();

				} // end foreach;

				// If this all started with a Setup Intent then let's use the subscription ID as the transaction ID.
				if ('setup_intent' === $payment_intent->object) {

					$this->payment->attributes(array(
						'gateway_payment_id' => sanitize_text_field($subscription->id),
					));

					$this->payment->save();

				} // end if;

			} catch (\Exception $e) {

				$error = sprintf( 'Stripe Gateway: Failed to create subscription for membership #%d. Message: %s', $this->membership->get_id(), $e->getMessage());

				wu_log_add('stripe', $error);

				$this->membership->set_recurring(false);

				$this->membership->save();

			} // end try;

		} // end if;

	} // end process_signup;

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
	 * Get or create Stripe Customer
	 *
	 * Get the Stripe customer record based on the user registering. This scans their
	 * `gateway_customer_id` fields for a Stripe customer ID and retrieves that if found.
	 *
	 * If no Stripe customer ID is located, a new customer record is created.
	 *
	 * @param int    $wu_customer_id RCP customer ID - used for checking the gateway customer ID field.
	 * @param int    $user_id         WordPress user ID number.
	 * @param string $customer_id     Stripe customer ID - if you already have one.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Dependencies\Stripe\Customer|WP_Error
	 */
	public function get_or_create_customer($wu_customer_id = 0, $user_id = 0, $customer_id = '') {

		$customer_exists = false;
		$user_id         = !empty($user_id) ? $user_id : $this->user_id;
		$user            = get_userdata($user_id);
		$wu_customer_id  = !empty($wu_customer_id) ? $wu_customer_id : $this->membership->get_customer()->get_id();
		$customer_id     = !empty($customer_id) ? $customer_id : wu_get_customer_gateway_id($wu_customer_id, array('stripe', 'stripe_checkout'));

		if ($customer_id) {

			$customer_exists = true;

			try {

				// Update the customer to ensure their card data is up to date
				$customer = \WP_Ultimo\Dependencies\Stripe\Customer::retrieve($customer_id);

				if (isset($customer->deleted) && $customer->deleted) {

					// This customer was deleted
					$customer_exists = false;

				} // end if;

				// No customer found
			} catch (\Exception $e) {

				$customer_exists = false;

			} // end try;

		} // end if;

		if (empty($customer_exists)) {

			try {

				$customer_args = array(
					'email' => $user->user_email,
					'name'  => sanitize_text_field(trim($user->first_name . ' ' . $user->last_name))
				);

				/**
				 * Filters the customer creation arguments.
				 */
				$customer_args = apply_filters('wu_stripe_customer_create_args', $customer_args, $this);

				$customer = \WP_Ultimo\Dependencies\Stripe\Customer::create($customer_args);

			} catch (\Exception $e) {

				return new \WP_Error($e->getCode(), $e->getMessage());

			} // end try;

		} // end if;

		return $customer;

	} // end get_or_create_customer;

	/**
	 * Get Stripe error from exception
	 *
	 * This converts the exception into a WP_Error object with a localized error message.
	 *
	 * @param \WP_Ultimo\Dependencies\Stripe\Error\Base $e The stripe error object.
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
	protected function get_localized_error_message($error_code, $error_message = '' ) {

		$errors = wu_stripe_get_localized_error_messages();

		if ( !empty( $errors[ $error_code ] ) ) {

			return $errors[ $error_code ];

		} else {

			return sprintf( __( 'An error has occurred (code: %1$s; message: %2$s).', 'wp-ultimo' ), $error_code, $error_message );

		} // end if;

	} // end get_localized_error_message;

	/**
	 * Handle Stripe processing error
	 *
	 * @param \WP_Ultimo\Dependencies\Stripe\Error\Base|Exception|WP_Error $e The stripe error.
	 *
	 * @access protected
	 * @since  2.5
	 * @return void
	 */
	protected function handle_processing_error($e) {

		if (method_exists($e, 'getJsonBody')) {

			$body                = $e->getJsonBody();
			$err                 = $body['error'];
			$error_code          = !empty($err['code']) ? $err['code'] : false;
			$this->error_message = $this->get_localized_error_message($error_code, $e->getMessage());

		} else {

			$error_code          = is_wp_error($e) ? $e->get_error_code() : $e->getCode();
			$this->error_message = is_wp_error($e) ? $e->get_error_message() : $e->getMessage();

		} // end if;

		do_action('wu_registration_failed', $this);

		$error = '<h4>' . __( 'An error occurred', 'wp-ultimo' ) . '</h4>';

		if (!empty($error_code)) {

			$error .= '<p>' . sprintf(__('Error code: %s', 'wp-ultimo'), $error_code) . '</p>';

		} // end if;

		if (method_exists($e, 'getHttpStatus')) {

			$error .= '<p>Status: ' . $e->getHttpStatus() . '</p>';

		} // end if;

		$error .= '<p>Message: ' . $this->error_message . '</p>';

		wp_die($error, __( 'Error', 'wp-ultimo' ), array('response' => 401));

	} // end handle_processing_error;

	/**
	 * Process webhooks
	 *
	 * @access public
	 * @return void
	 */
	public function process_webhooks() {
		/*
		 * Do not cache this!
		 */
		!defined('DONOTCACHEPAGE') && define('DONOTCACHEPAGE', true); // phpcs:ignore

		wu_log_add('stripe', 'Receiving Stripe webhook...');

		/*
		 * PHP Input as object
		 */
		$received_event = wu_get_input();

		// for extra security, retrieve from the Stripe API
		if (!isset($received_event->id)) {

			wu_log_add('stripe', 'Exiting Stripe webhook processing - event ID not found.');

			die('No Event ID found.');

		} // end if;

		/*
		 * Set the expiration date.
		 */
		$expiration = '';

		$event_id = $received_event->id;

		try {

			$event         = \WP_Ultimo\Dependencies\Stripe\Event::retrieve($event_id);
			$payment_event = $event->data->object;
			$membership    = false;

			wu_log_add('stripe', sprintf('Event ID: %s; Event Type: %s', $event->id, $event->type));

			if (empty($payment_event->customer)) {

				wu_log_add('stripe', 'Exiting Stripe webhook - no customer attached to event.');

				die('no customer attached');

			} // end if;

			/*
			 * Initialize important variables.
			 */
			$invoice      = false;
			$customer     = false;
			$subscription = false;

			// Try to get an invoice object from the payment event.
			if (!empty($payment_event->object) && 'invoice' === $payment_event->object) {

				$invoice = $payment_event;

			} elseif (!empty($payment_event->invoice)) {

				$invoice = \WP_Ultimo\Dependencies\Stripe\Invoice::retrieve($payment_event->invoice);

			} // end if;

			// Now try to get a subscription from the invoice.
			if ($invoice instanceof \WP_Ultimo\Dependencies\Stripe\Invoice && !empty($invoice->subscription)) {

				$subscription = \WP_Ultimo\Dependencies\Stripe\Subscription::retrieve($invoice->subscription);

			} // end if;

			// We can also get the subscription by the object ID in some circumstances.
			if (empty($subscription) && false !== strpos($payment_event->id, 'sub_')) {

				$subscription = \WP_Ultimo\Dependencies\Stripe\Subscription::retrieve($payment_event->id);

			} // end if;

			// Retrieve the membership by subscription ID.
			if (!empty($subscription)) {

				$membership = wu_get_membership_by('gateway_subscription_id', $subscription->id);

			} // end if;

			// Retrieve the membership by payment meta (one-time charges only).

			if (!empty($payment_event->metadata->key)) {

				wu_log_add('stripe', sprintf('Stripe Webhook: Getting membership by subscription key: %s', $payment_event->metadata->key));

				$membership = wu_get_membership_by('subscription_key', $payment_event->metadata->key);

			} // end if;

			/**
			 * Filters the membership record associated with this webhook.
			 *
			 * @param Membership|false       $membership
			 * @param Stripe_Gateway $this
			 * @param \WP_Ultimo\Dependencies\Stripe\Event              $event
			 *
			 * @since 2.0.0
			 */
			$membership = apply_filters('wu_stripe_webhook_membership', $membership, $this, $event);

			$membership = wu_get_membership(102);

			if (!$membership instanceof Membership) {

				wu_log_add('stripe', sprintf('Exiting Stripe webhook - membership not found. Customer ID: %s.', $payment_event->customer));

				die('no membership ID found');

			} // end if;

			$this->membership = $membership;
			$customer         = $membership->get_customer();

			wu_log_add('stripe', sprintf('Processing webhook for membership #%d.', $membership->get_id()));

			$plan_id = $membership->get_plan_id();

			if (!$plan_id) {

				wu_log_add('stripe', 'Exiting Stripe webhook - no membership level ID for membership.');

				die('no membership level ID for member');

			} // end if;

			if ($event->type === 'customer.subscription.created') {

				wu_log_add('stripe', sprintf('New recurring profile created for for membership #%d.', $membership->get_id()));

			} // end if;

			if ($event->type === 'charge.succeeded' || $event->type === 'invoice.payment_succeeded') {

				wu_log_add('stripe', sprintf('Processing Stripe %s webhook.', $event->type));

				$order = new Cart(array(
					'memberships' => array($membership->get_id()),
					'cart_type'   => 'renewal',
					'country'     => $customer->get_country(),
				));

				$payment_data = $order->to_payment_data();

				$payment_data['status']        = Payment_Status::COMPLETED;
				$payment_data['customer_id']   = $customer->get_id();
				$payment_data['membership_id'] = $membership->get_id();
				$payment_data['gateway']       = 'stripe';

				// Successful one-time payment
				if (empty($payment_event->invoice)) {

					$payment_data['amount']             = $payment_event->amount / wu_stripe_get_currency_multiplier();
					$payment_data['gateway_payment_id'] = $payment_event->charge;

					// Successful subscription payment
				} else {

					$payment_data['amount']             = $invoice->amount_due / wu_stripe_get_currency_multiplier();
					$payment_data['gateway_payment_id'] = $payment_event->charge;

					if (!empty($payment_event->discount)) {

						$payment_data['discount_code'] = $payment_event->discount->coupon_id;

					} // end if;

				} // end if;

				if (!empty($payment_data['gateway_payment_id']) && !wu_get_payment_by('gateway_payment_id', $payment_data['gateway_payment_id'])) {

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
						 *
						 * @link https://github.com/restrictcontentpro/restrict-content-pro/issues/2671
						 */
						$renewal_date = new \DateTime();
						$renewal_date->setTimestamp($subscription->current_period_end);
						$renewal_date->setTime(23, 59, 59);

						// Estimated charge date is 2 hours after `current_period_end`.
						$stripe_estimated_charge_timestamp = $subscription->current_period_end + (2 * HOUR_IN_SECONDS);

						if ($stripe_estimated_charge_timestamp > $renewal_date->getTimestamp()) {

							$renewal_date->setTimestamp($stripe_estimated_charge_timestamp);

						} // end if;

						$expiration = $renewal_date->format('Y-m-d H:i:s');

					} // end if;

					$pending_payment_id = $this->membership->get_meta('pending_payment_id');

					if (!empty($pending_payment_id)) {

						// Completing a pending payment. Account activation is handled in wu_complete_registration()
						$pending_payment = wu_get_payment($pending_payment_id);

						$pending_payment->attributes($payment_data);

						$pending_payment->save();

						$payment_id = $pending_payment_id;

					} else {

						// Inserting a new payment and renewing.
						$membership->renew($membership->is_recurring(), 'active', $expiration);

						$payment = wu_create_payment($payment_data);

						$payment_id = $payment->get_id();

					} // end if;

					do_action('wu_stripe_charge_succeeded', $customer->get_user_id(), $payment_data, $event);

					do_action('wu_gateway_payment_processed', $membership, $payment, $this);

					die('Charged processed successfully! Thanks, Stripe!');

				} elseif (!empty($payment_data['gateway_payment_id']) && wu_get_payment_by('gateway_payment_id', $payment_data['gateway_payment_id'])) {

					do_action('wu_ipn_duplicate_payment', $payment_data['gateway_payment_id'], $membership, $this);

					die('duplicate payment found');

				} // end if;

			} // end if;

			// failed payment
			if ($event->type === 'invoice.payment_failed') {

				wu_log_add('stripe', 'Processing Stripe invoice.payment_failed webhook.');

				$this->webhook_event_id = $event->id;

				// Make sure this invoice is tied to a subscription and is the user's current subscription.
				if (!empty($event->data->object->subscription ) && $event->data->object->subscription == $membership->get_gateway_subscription_id()) {

					do_action('wu_recurring_payment_failed', $member, $this);

				} else {

					wu_log_add('stripe', sprintf('Stripe subscription ID %s doesn\'t match membership\'s merchant subscription ID %s. Skipping wu_recurring_payment_failed hook.', $event->data->object->subscription, $member->get_merchant_subscription_id()), true);

				} // end if;

				do_action( 'wu_stripe_charge_failed', $payment_event, $event, $member );

				die( 'wu_stripe_charge_failed action fired successfully' );

			} // end if;

			// Cancelled / failed subscription
			if ($event->type == 'customer.subscription.deleted') {

				wu_log('Processing Stripe customer.subscription.deleted webhook.');

				if ($payment_event->id == $membership->get_gateway_subscription_id()) {

					// If this is a completed payment plan, we can skip any cancellation actions. This is handled in renewals.
					if ( $membership->has_payment_plan() && $membership->at_maximum_renewals() ) {
						wu_log( sprintf( 'Membership #%d has completed its payment plan - not cancelling.', $membership->get_id() ) );
						die( 'membership payment plan completed' );
					} // end if;

					if ( $membership->is_active() ) {
						$membership->cancel();
						$membership->add_note( __( 'Membership cancelled via Stripe webhook.', 'wp-ultimo' ) );
					} else {
						wu_log( sprintf( 'Membership #%d is not active - not cancelling account.', $membership->get_id() ) );
					} // end if;

					do_action( 'wu_webhook_cancel', $member, $this );

					die( 'member cancelled successfully' );

				} else {
					wu_log( sprintf( 'Payment event ID (%s) doesn\'t match membership\'s merchant subscription ID (%s).', $payment_event->id, $membership->get_gateway_subscription_id() ), true );
				} // end if;

			} // end if;

			do_action('wu_stripe_' . $event->type, $payment_event, $event);

		} catch (\Exception $e) {
			/*
			 * Something went wrong. Help!
			 */
			wu_log_add('stripe', sprintf('Exiting Stripe webhook due to PHP exception: %s.', $e->getMessage()), true);

			die('PHP exception: ' . $e->getMessage());

		} // end try;

		die('Thanks, Stripe!');

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

		$saved_payment_methods = $this->stripe_get_user_saved_payment_methods($user_id);

		foreach ($saved_payment_methods as $saved_payment_method) {

			$options[$saved_payment_method->id] = sprintf(
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

		$fields = array();

		$card_options = $this->get_saved_card_options();

		if ($card_options) {

			$card_options['add-new'] = __('Add new card', 'wp-ultimo');

			$fields = array(
				'payment_method' => array(
					'type'      => 'radio',
					'title'     => __('Saved Cards', 'wp-ultimo'),
					'value'     => wu_request('payment_method'),
					'options'   => $card_options,
					'html_attr' => array(
						'v-model' => 'payment_method',
					),
				)
			);

		} // end if;

		$stripe_form = new \WP_Ultimo\UI\Form('billing-address-fields', $fields, array('views' => 'checkout/fields'));

		ob_start();

		$stripe_form->render();

		// phpcs:disable

		?>

		<div v-if="payment_method == 'add-new'">

			<div id="card-element" class="wu-mb-4">
        <!-- A Stripe Element will be inserted here. -->
      </div>

      <div class="" id="ideal-bank-element">
        <!-- A Stripe iDEAL Element will be inserted here. -->
      </div>

      <!-- Used to display Element errors. -->
			<div id="card-errors" role="alert"></div>

		</div>

		<?php

		// phpcs:enable

		return ob_get_clean();

	} // end fields;

	/**
	 * Returns the payment methods.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function payment_methods() {

		$fields = array();

		$card_options = $this->get_saved_card_options();

		if ($card_options) {

			foreach ($card_options as $payment_method => $card) {

				$fields = array(
					"payment_method_{$payment_method}" => array(
						'type'          => 'text-display',
						'title'         => __('Saved Cards', 'wp-ultimo'),
						'display_value' => $card,
					)
				);

			} // end foreach;

		} // end if;

		return $fields;

	} // end payment_methods;

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
	 * Returns the membership meta
	 *
	 * @since 2.0.0
	 * @return array
	 */
	private function get_membership_meta() {

		$meta_data = array();

		foreach ($this->memberships as $product_id => $membership) {

			$product = wu_get_product($product_id);

			if ($product) {

				$slug = $product->get_slug();

				$key = "membership_{$slug}_id";

				$meta_data[$key] = $membership->get_id();

			} // end if;

		} // end foreach;

		return $meta_data;

	} // end get_membership_meta;

	/**
	 * Returns the customer metadata to add to Stripe's customer.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	private function get_customer_metadata() {

		$meta_data = array(
			'key'        => $this->memberships[0]->get_id(),
			'email'      => $this->email,
			'user_id'    => absint($this->user_id),
			'payment_id' => absint($this->payment->get_id()),
		);

		$meta_data = array_merge(
			$meta_data,
			$this->get_membership_meta()
		);

		return $meta_data;

	} // end get_customer_metadata;

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

		$stripe_tax_rates = \WP_Ultimo\Dependencies\Stripe\TaxRate::all();

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

			$tax_rate = \WP_Ultimo\Dependencies\Stripe\TaxRate::create($args);

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

			$plan = \WP_Ultimo\Dependencies\Stripe\Plan::retrieve($existing_plan_id);

			return $plan->id;

		} catch (\Exception $e) {

			// silence is golden

		} // end try;

		// Otherwise, create a new plan.
		try {

			$product = \WP_Ultimo\Dependencies\Stripe\Product::create(array(
				'name' => $args['name'],
				'type' => 'service'
			) );

			$plan = \WP_Ultimo\Dependencies\Stripe\Plan::create(array(
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
	 * @param int $user_id ID of the user to get the payment methods for. Use 0 for currently logged in user.
	 *
	 * @since 2.0.0
	 * @throws \Exception, When info is wrong.
	 * @throws \Exception When info is wrong 2.
	 * @return \WP_Ultimo\Dependencies\Stripe\PaymentMethod[]|array
	 */
	public function stripe_get_user_saved_payment_methods($user_id = 0) {

		if (empty($user_id)) {

			$user_id = get_current_user_id();

		} // end if;

		static $existing_payment_methods;

		if (!is_null($existing_payment_methods) && array_key_exists($user_id, $existing_payment_methods)) {

			// Payment methods have already been retrieved for this user -- return them now.
			return $existing_payment_methods[$user_id];

		} // end if;

		$customer_payment_methods = array();

		$customer = wu_get_customer_by_user_id($user_id);

		try {

			if (empty($customer)) {

				throw new \Exception(__('User is not a customer.', 'wp-ultimo'));

			} // end if;

			$stripe_customer_id = \WP_Ultimo\Models\Membership::query(array(
				'customer_id' => $customer->get_id(),
				'search'      => 'cus_*',
				'fields'      => array('gateway_customer_id'),
			));

			$stripe_customer_id = current(array_column($stripe_customer_id, 'gateway_customer_id'));

			if (empty($stripe_customer_id)) {

				throw new \Exception(__('User is not a Stripe customer.', 'wp-ultimo'));

			} // end if;

			if ( empty($this->secret_key)) {

				throw new \Exception(__('Missing Stripe secret key.', 'wp-ultimo'));

			} // end if;

			Stripe::setApiKey($this->secret_key);

			$payment_methods = \WP_Ultimo\Dependencies\Stripe\PaymentMethod::all(array(
				'customer' => $stripe_customer_id,
				'type'     => 'card'
			));

			if (empty($payment_methods)) {

				throw new Exception(__('User does not have any saved payment methods.', 'wp-ultimo'));

			} // end if;

			foreach ($payment_methods->data as $payment_method) {

				$customer_payment_methods[$payment_method->id] = $payment_method;

			} // end foreach;

		} catch (\Exception $e) {

			wu_log_add('stripe', sprintf(__('Stripe Error: User ID: %1$s, %2$s', 'wp-ultimo'), $user_id, $e->getMessage()));

			return array();

		} // end try;

		$existing_payment_methods[$user_id] = $customer_payment_methods;

		return $existing_payment_methods[$user_id];

	} // end stripe_get_user_saved_payment_methods;

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

		return sprintf('https://dashboard.stripe.com%s/payments/%s', $route, $gateway_payment_id);

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

} // end class Stripe_Gateway;
