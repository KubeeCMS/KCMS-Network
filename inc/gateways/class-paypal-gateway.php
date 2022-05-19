<?php
/**
 * PayPal Gateway.
 *
 * @package WP_Ultimo
 * @subpackage Gateways
 * @since 2.0.0
 */

namespace WP_Ultimo\Gateways;

use \WP_Ultimo\Gateways\Base_Gateway;
use \WP_Ultimo\Database\Payments\Payment_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * PayPal Payments Gateway
 *
 * @since 2.0.0
 */
class PayPal_Gateway extends Base_Gateway {

	/**
	 * Holds the ID of a given gateway.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'paypal';

	/**
	 * Holds if we are in test mode.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $test_mode = true;

	/**
	 * The API endpoint. Depends on the test mode.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $api_endpoint;

	/**
	 * Checkout URL.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $checkout_url;

	/**
	 * PayPal username.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $username;

	/**
	 * PayPal password.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $password;

	/**
	 * PayPal signature.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $signature;

	/**
	 * Backwards compatibility for the old notify ajax url.
	 *
	 * @since 2.0.4
	 * @var bool|string
	 */
	protected $backwards_compatibility_v1_id = 'paypal';

	/**
	 * Declares support to recurring payments.
	 *
	 * Manual payments need to be manually paid,
	 * so we return false here.
	 *
	 * @since 2.0.0
	 * @return false
	 */
	public function supports_recurring() {

		return true;

	} // end supports_recurring;

	/**
	 * Adds the necessary hooks for the manual gateway.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks() {} // end hooks;

	/**
	 * Initialization code.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		/*
		 * Checks if we are in test mode or not,
		 * based on the PayPal Setting.
		 */
		$this->test_mode = wu_get_setting('paypal_sandbox_mode', true);

		/*
		 * If we are in test mode
		 * use test mode keys.
		 */
		if ($this->test_mode) {

			$this->api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
			$this->checkout_url = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';

			$this->username  = wu_get_setting('paypal_test_username', '');
			$this->password  = wu_get_setting('paypal_test_password', '');
			$this->signature = wu_get_setting('paypal_test_signature', '');

			return;

		} // end if;

		/*
		 * Otherwise, set
		 * PayPal live keys.
		 */
		$this->api_endpoint = 'https://api-3t.paypal.com/nvp';
		$this->checkout_url = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';

		$this->username  = wu_get_setting('paypal_live_username', '');
		$this->password  = wu_get_setting('paypal_live_password', '');
		$this->signature = wu_get_setting('paypal_live_signature', '');

	} // end init;

	/**
	 * Adds the PayPal Gateway settings to the settings screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function settings() {

		wu_register_settings_field('payment-gateways', 'paypal_header', array(
			'title'           => __('PayPal', 'wp-ultimo'),
			'desc'            => __('Use the settings section below to configure PayPal Express as a payment method.', 'wp-ultimo'),
			'type'            => 'header',
			'show_as_submenu' => true,
			'require'         => array(
				'active_gateways' => 'paypal',
			),
		));

		wu_register_settings_field('payment-gateways', 'paypal_sandbox_mode', array(
			'title'     => __('PayPal Sandbox Mode', 'wp-ultimo'),
			'desc'      => __('Toggle this to put PayPal on sandbox mode. This is useful for testing and making sure PayPal is correctly setup to handle your payments.', 'wp-ultimo'),
			'type'      => 'toggle',
			'default'   => 0,
			'html_attr' => array(
				'v-model' => 'paypal_sandbox_mode',
			),
			'require'   => array(
				'active_gateways' => 'paypal',
			),
		));

		wu_register_settings_field('payment-gateways', 'paypal_test_username', array(
			'title'       => __('PayPal Test Username', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the TEST username, not the live one.', 'wp-ultimo'),
			'placeholder' => __('e.g. username_api1.username.co', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'paypal',
				'paypal_sandbox_mode' => 1,
			),
		));

		wu_register_settings_field('payment-gateways', 'paypal_test_password', array(
			'title'       => __('PayPal Test Password', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the TEST password, not the live one.', 'wp-ultimo'),
			'placeholder' => __('e.g. IUOSABK987HJG88N', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'paypal',
				'paypal_sandbox_mode' => 1,
			),
		));

		wu_register_settings_field('payment-gateways', 'paypal_test_signature', array(
			'title'       => __('PayPal Test Signature', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the TEST signature, not the live one.', 'wp-ultimo'),
			'placeholder' => __('e.g. AFcpSSRl31ADOdqnHNv4KZdVHEQzdMEEsWxV21C7fd0v3bYYYRCwYxqo', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'paypal',
				'paypal_sandbox_mode' => 1,
			),
		));

		wu_register_settings_field('payment-gateways', 'paypal_live_username', array(
			'title'       => __('PayPal Live Username', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the LIVE username, not the test one.', 'wp-ultimo'),
			'placeholder' => __('e.g. username_api1.username.co', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'paypal',
				'paypal_sandbox_mode' => 0,
			),
		));

		wu_register_settings_field('payment-gateways', 'paypal_live_password', array(
			'title'       => __('PayPal Live Password', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the LIVE password, not the test one.', 'wp-ultimo'),
			'placeholder' => __('e.g. IUOSABK987HJG88N', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'paypal',
				'paypal_sandbox_mode' => 0,
			),
		));

		wu_register_settings_field('payment-gateways', 'paypal_live_signature', array(
			'title'       => __('PayPal Live Signature', 'wp-ultimo'),
			'desc'        => '',
			'tooltip'     => __('Make sure you are placing the LIVE signature, not the test one.', 'wp-ultimo'),
			'placeholder' => __('e.g. AFcpSSRl31ADOdqnHNv4KZdVHEQzdMEEsWxV21C7fd0v3bYYYRCwYxqo', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
			'capability'  => 'manage_api_keys',
			'require'     => array(
				'active_gateways'     => 'paypal',
				'paypal_sandbox_mode' => 0,
			),
		));

	} // end settings;

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
	 * @return void
	 */
	public function process_checkout($payment, $membership, $customer, $cart, $type) {
		/*
		 * To make our lives easier, let's
		 * set a couple of variables based on the order.
		 */
		$initial_amount    = $cart->get_total();
		$should_auto_renew = $cart->should_auto_renew();
		$is_recurring      = $cart->has_recurring();

		/*
		 * Get the amount depending on
		 * the auto-renew status.
		 */
		$amount = $should_auto_renew ? $cart->get_total() : $cart->get_recurring_total();

		/*
		 * Sets the cancel URL.
		 */
		$cancel_url = wp_get_referer();

		if (empty($cancel_url)) {

			$cancel_url = add_query_arg('cancel', '1', home_url());

		} // end if;

		/*
		 * Calculates the return URL
		 * for the intermediary return URL.
		 */
		$return_url = add_query_arg(array(
			'wu-confirm' => 'paypal',
			'payment-id' => urlencode($this->payment->get_id()),
		), home_url('register'));

		/*
		 * Setup variables
		 *
		 * PayPal takes a ***load of variables.
		 * Some of them need to be prepped beforehand.
		 */
		$currency    = strtoupper($cart->get_currency());
		$description = $this->get_subscription_description($cart);
		$notify_url  = $this->get_webhook_listener_url();

		/*
		 * This is a special key paypal lets us set.
		 * It contains the payment_id, membership_id and customer_id
		 * in the following format: payment_id|membership_id|customer_id
		 */
		$custom_key = sprintf('%s|%s|%s', $payment->get_id(), $membership->get_id(), $customer->get_id());

		/*
		 * Now we can build the PayPal
		 * request object, and append the products
		 * later.
		 */
		$args = array(
			'USER'                           => $this->username,
			'PWD'                            => $this->password,
			'SIGNATURE'                      => $this->signature,
			'VERSION'                        => '124',
			'METHOD'                         => 'SetExpressCheckout',
			'PAYMENTREQUEST_0_SHIPPINGAMT'   => 0,
			'PAYMENTREQUEST_0_TAXAMT'        => 0,
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'PAYMENTREQUEST_0_AMT'           => 0,
			'PAYMENTREQUEST_0_ITEMAMT'       => 0,
			'PAYMENTREQUEST_0_CURRENCYCODE'  => $currency,
			'PAYMENTREQUEST_0_DESC'          => $description,
			'PAYMENTREQUEST_0_CUSTOM'        => $custom_key,
			'PAYMENTREQUEST_0_NOTIFYURL'     => $notify_url,
			'EMAIL'                          => $customer->get_email_address(),
			'CANCELURL'                      => $cancel_url,
			'NOSHIPPING'                     => 1,
			'REQCONFIRMSHIPPING'             => 0,
			'ALLOWNOTE'                      => 0,
			'ADDROVERRIDE'                   => 0,
			'PAGESTYLE'                      => '',
			'SOLUTIONTYPE'                   => 'Sole',
			'LANDINGPAGE'                    => 'Billing',
			'RETURNURL'                      => $return_url,
		);

		/*
		 * After that, we need to add the additional
		 * products.
		 */
		$product_index = 0;

		/*
		 * Loop products and add them to the paypal
		 */
		foreach ($cart->get_line_items() as $line_item) {

			$total      = $line_item->get_total();
			$sub_total  = $line_item->get_subtotal();
			$tax_amount = $line_item->get_tax_total();

			$product_args = array(
				"L_PAYMENTREQUEST_0_NAME{$product_index}" => $line_item->get_title(),
				"L_PAYMENTREQUEST_0_DESC{$product_index}" => $line_item->get_description(),
				"L_PAYMENTREQUEST_0_AMT{$product_index}"  => $sub_total,
				"L_PAYMENTREQUEST_0_QTY{$product_index}"  => $line_item->get_quantity(),
				"L_PAYMENTREQUEST_0_TAXAMT{$product_index}" => $tax_amount,
			);

			$args['PAYMENTREQUEST_0_ITEMAMT'] = $args['PAYMENTREQUEST_0_ITEMAMT'] + $sub_total;
			$args['PAYMENTREQUEST_0_TAXAMT']  = $args['PAYMENTREQUEST_0_TAXAMT'] + $tax_amount;
			$args['PAYMENTREQUEST_0_AMT']     = $args['PAYMENTREQUEST_0_AMT'] + $sub_total + $tax_amount;

			$args = array_merge($args, $product_args);

			$product_index++;

		} // end foreach;

		if ($should_auto_renew && $is_recurring) {

			$args['L_BILLINGAGREEMENTDESCRIPTION0'] = $description;
			$args['L_BILLINGTYPE0']                 = 'RecurringPayments';

		} // end if;

		$request = wp_remote_post($this->api_endpoint, array(
			'timeout'     => 45,
			'httpversion' => '1.1',
			'body'        => $args
		));

		$body    = wp_remote_retrieve_body($request);
		$code    = wp_remote_retrieve_response_code($request);
		$message = wp_remote_retrieve_response_message($request);

		// Add multiple items: https://stackoverflow.com/questions/31957791/paypal-subscription-for-multiple-product-using-paypal-api

		/*
		 * Check for wp-error on the request call
		 *
		 * This will catch timeouts and similar errors.
		 * Maybe PayPal is out? We can't be sure.
		 */
		if (is_wp_error($request)) {

			throw new \Exception($request->get_error_message(), $request->get_error_code());

		} // end if;

		/*
		 * If we get here, we got a 200.
		 * This means we got a valid response from
		 * PayPal.
		 *
		 * Now we need to check for a valid token to
		 * redirect the customer to the checkout page.
		 */
		if (200 === absint($code) && 'OK' === $message) {
			/*
			 * PayPal gives us a URL-formatted string
			 * Urrrrgh! Let's parse it.
			 */
			if (is_string($body)) {

				wp_parse_str($body, $body);

			} // end if;

			if ('failure' === strtolower($body['ACK'])) {

				throw new \Exception($body['L_LONGMESSAGE0'], $body['L_ERRORCODE0']);

			} else {
				/*
				 * We do have a valid token.
				 *
				 * Redirect to the PayPal checkout URL.
				 */
				wp_redirect($this->checkout_url . $body['TOKEN']);

				exit;

			} // end if;

		} // end if;

		/*
		 * If we get here, something went wrong.
		 */
		throw new \Exception(__('Something has gone wrong, please try again', 'wp-ultimo'));

	} // end process_checkout;

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
	public function process_cancellation($membership, $customer) {} // end process_cancellation;

	/**
	 * Process a checkout.
	 *
	 * It takes the data concerning
	 * a refund and process it.
	 *
	 * @since 2.0.0
	 *
	 * @throws \Exception                  When something goes wrong.
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

		$refund_type = 'Partial';

		if ($amount >= $payment->get_total()) {

			$refund_type = 'Full';

		} // end if;

		$amount_formatted = number_format($amount, 2);

		$args = array(
			'USER'          => $this->username,
			'PWD'           => $this->password,
			'SIGNATURE'     => $this->signature,
			'VERSION'       => '124',
			'METHOD'        => 'RefundTransaction',
			'REFUND_TYPE'   => $refund_type,
			'TRANSACTIONID' => $gateway_payment_id,
			'INVOICEID'     => $payment->get_hash(),
		);

		if ($refund_type === 'Partial') {

			$args['AMT'] = $amount_formatted;

		} // end if;

		$request = wp_remote_post($this->api_endpoint, array(
			'timeout'     => 45,
			'httpversion' => '1.1',
			'body'        => $args,
		));

		$body    = wp_remote_retrieve_body($request);
		$code    = wp_remote_retrieve_response_code($request);
		$message = wp_remote_retrieve_response_message($request);

		if (is_wp_error($request)) {

			throw new \Exception($request->get_error_message());

		} // end if;

		if (200 === absint($code) && 'OK' === $message) {
			/*
			 * PayPal gives us a URL-formatted string
			 * Urrrrgh! Let's parse it.
			 */
			if (is_string($body)) {

				wp_parse_str($body, $body);

			} // end if;

			if ('failure' === strtolower($body['ACK'])) {

				throw new \Exception($body['L_LONGMESSAGE0']);

			} // end if;

			/*
			 * All good.
			 */
			return true;

		} // end if;

		throw new \Exception(__('Something went wrong.', 'wp-ultimo'));

	} // end process_refund;

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

		$message = __('You will be redirected to PayPal to complete the purchase.', 'wp-ultimo');

		return sprintf('<p class="wu-p-4 wu-bg-yellow-200">%s</p>', $message);

	} // end fields;

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
	public function process_confirmation() {
		/*
		 * Tries to retrieve the nonce.
		 */
		$nonce = wu_request('wu_ppe_confirm_nonce', 'no-nonce');

		/*
		 * If the nonce is present and is valid,
		 * we can be sure we have the data we need to process a confirmation
		 * screen. Here we actually finish the payment
		 * and/or create the subscription.
		 */
		if (wp_verify_nonce($nonce, 'wu-ppe-confirm-nonce')) {
			/*
			 * Retrieve the payment details, base on the token.
			 */
			$details = $this->get_checkout_details(wu_request('token'));

			if (empty($details)) {

				$error = new \WP_Error(__('PayPal token no longer valid.', 'wp-ultimo'));

				wp_die($error);

			} // end if;

			/*
			 * Tries to get the payment based on the request
			 */
			$payment_id = wu_request('payment-id');
			$payment    = wu_get_payment($payment_id);

			/*
			 * The pending payment does not exist...
			 * Bail.
			 */
			if (empty($payment)) {

				$error = new \WP_Error(__('Pending payment does not exist.', 'wp-ultimo'));

				wp_die($error);

			} // end if;

			/*
			 * Now we need to original cart.
			 *
			 * The original cart gets saved with the original
			 * payment. Otherwise, we bail.
			 */
			$original_cart = $payment->get_meta('wu_original_cart');

			if (empty($original_cart)) {

				$error = new \WP_Error('no-cart', __('Original cart does not exist.', 'wp-ultimo'));

				wp_die($error);

			} // end if;

			/*
			 * Set the variables
			 */
			$membership        = $payment->get_membership();
			$customer          = $payment->get_customer();
			$should_auto_renew = $original_cart->should_auto_renew();
			$is_recurring      = $original_cart->has_recurring();

			if (empty($membership) || empty($customer)) {

				$error = new \WP_Error('no-membership', __('Missing membership or customer data.', 'wp-ultimo'));

				wp_die($error);

			} // end if;

			if ($should_auto_renew && $is_recurring) {
				/*
				 * We need to create the payment profile.
				 * As this is a recurring payment and the
				 * auto-renew option is active.
				 */
				$this->create_recurring_profile($details, $original_cart, $payment, $membership, $customer);

			} else {
				/*
				 * Otherwise, process
				 * single payment.
				 */
				$this->complete_single_payment($details, $original_cart, $payment, $membership, $customer);

			} // end if;

		/*
		 * If we don't have the valid
		 * parameters we need to process
		 * the confirmation, we
		 * filter the content to display
		 * the confirmation screen.
		 */
		} elseif (!empty(wu_request('token')) && !empty(wu_request('PayerID'))) {

			add_filter('the_content', array($this, 'confirmation_form'), 9999999);

		} // end if;

	} // end process_confirmation;

	/**
	 * Process webhooks
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function process_webhooks() {

		wu_log_add('paypal', 'Receiving PayPal IPN webhook...');

		$posted = apply_filters('wu_ipn_post', $_POST);

		$payment    = false;
		$customer   = false;
		$membership = false;

		$custom = !empty($posted['custom']) ? explode('|', $posted['custom']) : array();

		if (is_array($custom) && !empty($custom)) {

			$payment    = wu_get_payment(absint($custom[0]));
			$membership = wu_get_payment(absint($custom[1]));
			$customer   = wu_get_payment(absint($custom[2]));

		} // end if;

		if (!empty($posted['recurring_payment_id'])) {

			$membership = wu_get_membership_by('gateway_subscription_id', $posted['recurring_payment_id']);

		} // end if;

		if (empty($membership)) {

			throw new \Exception(__('Exiting PayPal Express IPN - membership ID not found.', 'wp-ultimo'));

		} // end if;

		wu_log_add('paypal', sprintf('Processing IPN for membership #%d.', $membership->get_id()));

		/*
		 * Base payment data for update
		 * or insertion.
		 */
		$payment_data = array(
			'status'        => Payment_Status::COMPLETED,
			'payment_type'  => $posted['txn_type'],
			'customer_id'   => $customer->get_id(),
			'membership_id' => $membership->get_id(),
			'gateway'       => $this->id,
		);

		$amount = isset($posted['mc_gross']) ? wu_to_float($posted['mc_gross']) : false;

		if ($amount !== false) {

			$payment_data['amount'] = $amount;

		} // end if;

		if (!empty($posted['payment_date'])) {

			$payment_data['date'] = date('Y-m-d H:i:s', strtotime($posted['payment_date'])); // phpcs:ignore

		} // end if;

		if (!empty($posted['txn_id'])) {

			$payment_data['transaction_id'] = sanitize_text_field($posted['txn_id']);

		} // end if;

		$pending_payment = $membership->get_last_pending_payment();

		/*
		 * Deal with each transaction type
		 * accordingly
		 */
		switch ($posted['txn_type']) :
			/*
			 * New recurring profile, aka paypal subscription created.
			 */
			case 'recurring_payment_profile_created':
				/*
				 * Log
				 */
				wu_log_add('paypal', 'Processing PayPal Express recurring_payment_profile_created IPN.');

				/*
				 * Get the gateway payment ID.
				 *
				 * We'll use this to try to localize the pending
				 * payment and make sure we have a match.
				 */
				if (isset($posted['initial_payment_txn_id'])) {

					$transaction_id = ('Completed' === $posted['initial_payment_status']) ? $posted['initial_payment_txn_id'] : '';

				} else {

					$transaction_id = $posted['ipn_track_id'];

				} // end if;

				if (empty($transaction_id) || $rcp_payments->payment_exists($transaction_id)) {

					throw new \Exception(sprintf('Breaking out of PayPal Express IPN recurring_payment_profile_created. Transaction ID not given or payment already exists. TXN ID: %s', $transaction_id));

				} // end if;

				// setup the payment info in an array for storage
				$payment_data['date']           = date('Y-m-d H:i:s', strtotime($posted['time_created'])); // phpcs:ignore
				$payment_data['amount']         = wu_to_float($posted['initial_payment_amount']);
				$payment_data['transaction_id'] = sanitize_text_field($transaction_id);

				/*
				 * In the case that the payment
				 * already exists, update it.
				 */
				if (!empty($pending_payment)) {

					$pending_payment->attributes($payment_data);

					$pending_payment->save();

				/*
				 * Payment does not exist. Create it and renew the membership.
				 */
				} else {

					/**
					 * Here, we need to create a new payment, based on the IPN.
					 * This can be a challenge...
					 */
					// $payment_data['subtotal'] = $payment_data['amount'];
					// $payment_id = $rcp_payments->insert( $payment_data );

				} // end if;

				$expiration = date('Y-m-d 23:59:59', strtotime($posted['next_payment_date'])); // phpcs:ignore

				$membership->add_to_times_billed(1);

				$membership->renew($membership->should_auto_renew(), 'active', $expiration);

				break;

			case 'recurring_payment':
				wu_log_add('paypal', 'Processing PayPal Express recurring_payment IPN.');

				// when a user makes a recurring payment
				update_user_meta( $user_id, 'rcp_paypal_subscriber', $posted['payer_id'] );

				$membership->set_gateway_subscription_id( $posted['recurring_payment_id'] );

				if ('failed' === strtolower($posted['payment_status'])) {

					// Recurring payment failed.
					$membership->add_note( sprintf( __( 'Transaction ID %s failed in PayPal.', 'wp-ultimo' ), $posted['txn_id'] ) );

					die( 'Subscription payment failed' );

				} elseif ('pending' === strtolower($posted['payment_status'])) {

					// Recurring payment pending (such as echeck).
					$pending_reason = !empty( $posted['pending_reason'] ) ? $posted['pending_reason'] : __( 'unknown', 'wp-ultimo' );

					$membership->add_note( sprintf( __( 'Transaction ID %1$s is pending in PayPal for reason: %2$s', 'wp-ultimo' ), $posted['txn_id'], $pending_reason ) );

					die( 'Subscription payment pending' );

				} // end if;

				$membership->add_to_times_billed(1);

				$membership->renew(true);

				$payment_data['transaction_type'] = 'renewal';

				break;

			case 'recurring_payment_profile_cancel':
				wu_log_add('paypal', 'Processing PayPal Express recurring_payment_profile_cancel IPN.');

				if (!$member->just_upgraded()) {

					if (isset($posted['initial_payment_status']) && 'Failed' === $posted['initial_payment_status']) {

						// Initial payment failed, so set the user back to pending.
						$membership->set_status('pending');

						$membership->add_note(__( 'Initial payment failed in PayPal Express.', 'wp-ultimo'));

						$this->error_message = __('Initial payment failed.', 'wp-ultimo');

						// do_action('rcp_registration_failed', $this);
						// do_action('rcp_paypal_express_initial_payment_failed', $member, $posted, $this);

					} else {

						// If this is a completed payment plan, we can skip any cancellation actions. This is handled in renewals.
						if ($membership->has_payment_plan() && $membership->at_maximum_renewals()) {

							wu_log_add('paypal', sprintf('Membership #%d has completed its payment plan - not cancelling.', $membership->get_id()));

							die('membership payment plan completed');

						} // end if;

						// user is marked as cancelled but retains access until end of term
						$membership->cancel();

						$membership->add_note(__('Membership cancelled via PayPal Express IPN.', 'wp-ultimo'));

						// set the use to no longer be recurring
						delete_user_meta($user_id, 'rcp_paypal_subscriber');

						// do_action('rcp_ipn_subscr_cancel', $user_id);
						// do_action('rcp_webhook_cancel', $member, $this);

					} // end if;

				} // end if;

				break;

			case 'recurring_payment_failed':
			case 'recurring_payment_suspended_due_to_max_failed_payment': // Same case as before
				wu_log_add('paypal', 'Processing PayPal Express recurring_payment_failed or recurring_payment_suspended_due_to_max_failed_payment IPN.');

				if (!in_array($membership->get_status(), array('cancelled', 'expired'))) {

					$membership->set_status('expired');

				} // end if;

				if (!empty($posted['txn_id'])) {

					$this->webhook_event_id = sanitize_text_field($posted['txn_id']);

				} elseif (!empty($posted['ipn_track_id'])) {

					$this->webhook_event_id = sanitize_text_field($posted['ipn_track_id']);
				} // end if;

				// do_action('rcp_ipn_subscr_failed');
				// do_action('rcp_recurring_payment_failed', $member, $this);

				break;

			case 'web_accept':
				wu_log_add('paypal', sprintf('Processing PayPal Express web_accept IPN. Payment status: %s', $posted['payment_status']));

				switch (strtolower($posted['payment_status'])) :

					case 'completed':
						if (empty($payment_data['transaction_id']) || $rcp_payments->payment_exists($payment_data['transaction_id'])) {

							wu_log_add('paypal', sprintf('Not inserting PayPal Express web_accept payment. Transaction ID not given or payment already exists. TXN ID: %s', $payment_data['transaction_id']), true);

						} else {

							$rcp_payments->insert($payment_data);

						} // end if;

						// Membership was already activated.

						break;

					case 'denied':  // all the same case
					case 'expired': // all the same case
					case 'failed':  // all the same case
					case 'voided':  // all the same case
						wu_log_add('paypal', sprintf('Membership #%d is not active - not cancelling account.', $membership->get_id()));

						/*
						 * Cancel active memberships.
						 */
						if ($membership->is_active()) {

							$membership->cancel();

						} else {

							wu_log_add('paypal', sprintf('Membership #%d is not active - not cancelling account.', $membership->get_id()));

						} // end if;

						break;

				endswitch;

				break;

		endswitch;

		return true;

	} // end process_webhooks;

	/**
	 * Create a recurring profile.
	 *
	 * @since 2.0.0
	 *
	 * @param array                        $details The PayPal transaction details.
	 * @param \WP_Ultimo\Checkout\Cart     $cart The cart object.
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @return void
	 */
	protected function create_recurring_profile($details, $cart, $payment, $membership, $customer) {

		$args = array(
			'USER'                => $this->username,
			'PWD'                 => $this->password,
			'SIGNATURE'           => $this->signature,
			'VERSION'             => '124',
			'TOKEN'               => $_POST['token'],
			'METHOD'              => 'CreateRecurringPaymentsProfile',
			'PROFILESTARTDATE'    => date('Y-m-d\TH:i:s', strtotime('+' . $cart->get_duration() . ' ' . $cart->get_duration_unit(), wu_get_current_time('timestamp', true))), // phpcs:ignore
			'BILLINGPERIOD'       => ucwords($cart->get_duration_unit()),
			'BILLINGFREQUENCY'    => $cart->get_duration(),
			'AMT'                 => $cart->get_recurring_total(),
			'INITAMT'             => $cart->get_total(),
			'CURRENCYCODE'        => strtoupper($cart->get_currency()),
			'FAILEDINITAMTACTION' => 'CancelOnFailure',
			'L_BILLINGTYPE0'      => 'RecurringPayments',
			'DESC'                => $this->get_subscription_description($cart),
			'BUTTONSOURCE'        => 'WP_Ultimo',
		);

		if ($args['INITAMT'] < 0) {

			unset($args['INITAMT']);

		} // end if;

		if (false && $membership->get_trial_end_date() && 0 === $membership->get_times_billed()) {
			/*
			 * Set profile start date to the end of the free trial.
			 */
			$args['PROFILESTARTDATE'] = date('Y-m-d\TH:i:s', strtotime( $membership->get_trial_end_date(), wu_get_current_time('timestamp', true))); // phpcs:ignore

			unset($args['INITAMT']);

		} // end if;

		$request = wp_remote_post($this->api_endpoint, array(
			'timeout'     => 45,
			'httpversion' => '1.1',
			'body'        => $args,
		));

		$body    = wp_remote_retrieve_body($request);
		$code    = wp_remote_retrieve_response_code($request);
		$message = wp_remote_retrieve_response_message($request);

		if (is_wp_error($request)) {

			wp_die($request);

		} // end if;

		if (200 === absint($code) && 'OK' === $message) {
			/*
			 * PayPal gives us a URL-formatted string
			 * Urrrrgh! Let's parse it.
			 */
			if (is_string($body)) {

				wp_parse_str($body, $body);

			} // end if;

			if ('failure' === strtolower($body['ACK'])) {

				$error = new \WP_Error($body['L_ERRORCODE0'], $body['L_LONGMESSAGE0']);

				wp_die($error);

			} else {
				/*
				 * We were successful, let's update
				 * the payment.
				 *
				 * First, set the value
				 * and the transaction ID.
				 */
				$transaction_id = $body['PAYMENTINFO_0_TRANSACTIONID'];

				$payment_data = array(
					'gateway_payment_id' => $transaction_id,
					'status'             => Payment_Status::COMPLETED,
				);

				/*
					* Update local payment.
					*
					* This will add the transaction id,
					* if we have it already, and mark it as
					* complete.
					*
					* If we have a pending membership,
					* and a pending site, for example,
					* those will be marked as active.
					*/
				$payment->attributes($payment_data);
				$payment->save();

				$membership = $payment->get_membership();
				$membership->set_gateway_subscription_id($body['PROFILEID']);
				$membership->set_gateway_customer_id($details['PAYERID']);
				$membership->set_gateway('paypal');
				$membership->renew(true);

				$redirect_url = add_query_arg(array(
					'payment' => $payment->get_hash(),
					'status'  => 'done',
				), wu_get_registration_url());

				wp_redirect($redirect_url);

				exit;

			} // end if;

		} else {

			wp_die( __( 'Something has gone wrong, please try again', 'wp-ultimo' ), __( 'Error', 'wp-ultimo' ), array( 'back_link' => true, 'response' => '401' ) );

		} // end if;

	} // end create_recurring_profile;

	/**
	 * Get the subscription description.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Cart $cart The cart object.
	 * @return string
	 */
	protected function get_subscription_description($cart) {

		$descriptor = $cart->get_cart_descriptor();

		$desc = html_entity_decode(substr($descriptor, 0, 127), ENT_COMPAT, 'UTF-8');

		return $desc;

	} // end get_subscription_description;

	/**
	 * Create a single payment on PayPal.
	 *
	 * @since 2.0.0
	 *
	 * @param array                        $details The PayPal transaction details.
	 * @param \WP_Ultimo\Checkout\Cart     $cart The cart object.
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @return void
	 */
	protected function complete_single_payment($details, $cart, $payment, $membership, $customer) {

		// One time payment
		$args = array(
			'USER'                           => $this->username,
			'PWD'                            => $this->password,
			'SIGNATURE'                      => $this->signature,
			'VERSION'                        => '124',
			'METHOD'                         => 'DoExpressCheckoutPayment',
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'TOKEN'                          => wu_request('token'),
			'PAYERID'                        => wu_request('payer_id'),
			'PAYMENTREQUEST_0_AMT'           => $details['AMT'],
			'PAYMENTREQUEST_0_ITEMAMT'       => $details['AMT'],
			'PAYMENTREQUEST_0_SHIPPINGAMT'   => 0,
			'PAYMENTREQUEST_0_TAXAMT'        => 0,
			'PAYMENTREQUEST_0_CURRENCYCODE'  => $details['CURRENCYCODE'],
			'BUTTONSOURCE'                   => 'WP_Ultimo'
		);

		$request = wp_remote_post($this->api_endpoint, array(
			'timeout'     => 45,
			'httpversion' => '1.1',
			'body'        => $args,
		));

		/*
			* Retrieve the results of
			* the API call to PayPal
			*/
		$body    = wp_remote_retrieve_body($request);
		$code    = wp_remote_retrieve_response_code($request);
		$message = wp_remote_retrieve_response_message($request);

		if (is_wp_error($request)) {

			wp_die($request);

		} // end if;

		if (200 === absint($code) && 'OK' === $message) {

			if (is_string($body)) {

				wp_parse_str($body, $body);

			} // end if;

			if ('failure' === strtolower($body['ACK'])) {

				$error = new \WP_Error($body['L_ERRORCODE0'], $body['L_LONGMESSAGE0']);

				wp_die($error);

			} else {
				/*
					* We were successful, let's update
					* the payment.
					*
					* First, set the value
					* and the transaction ID.
					*/
				$transaction_id = $body['PAYMENTINFO_0_TRANSACTIONID'];

				$payment_data = array(
					'gateway_payment_id' => $transaction_id,
					'status'             => Payment_Status::COMPLETED,
				);

				/*
					* Update local payment.
					*
					* This will add the transaction id,
					* if we have it already, and mark it as
					* complete.
					*
					* If we have a pending membership,
					* and a pending site, for example,
					* those will be marked as active.
					*/
				$payment->attributes($payment_data);
				$payment->save();

				$membership = $payment->get_membership();
				$membership->renew(false);

				$redirect_url = add_query_arg(array(
					'payment' => $payment->get_hash(),
					'status'  => 'done',
				), wu_get_registration_url());

				wp_redirect($redirect_url);

				exit;

			} // end if;

		} else {

			wp_die(__('Something has gone wrong, please try again', 'wp-ultimo' ), __('Error', 'wp-ultimo'), array('back_link' => true, 'response' => '401'));

		} // end if;

	} // end complete_single_payment;

	/**
	 * Display the confirmation form.
	 *
	 * @since 2.1
	 * @return string
	 */
	public function confirmation_form() {

		$token = sanitize_text_field(wu_request('token'));

		$checkout_details = $this->get_checkout_details($token);

		if (!is_array($checkout_details)) {

			$error = is_wp_error($checkout_details) ? $checkout_details->get_error_message() : __('Invalid response code from PayPal', 'wp-ultimo');

			// translators: %s is the paypal error message.
			return '<p>' . sprintf(__('An unexpected PayPal error occurred. Error message: %s.', 'wp-ultimo'), $error) . '</p>';

		} // end if;

		/*
		 * Compiles the necessary elements.
		 */
		$customer = $checkout_details['pending_payment']->get_customer(); // current customer

		wu_get_template('checkout/paypal/confirm', array(
			'checkout_details' => $checkout_details,
			'customer'         => $customer,
			'payment'          => $checkout_details['pending_payment'],
			'membership'       => $checkout_details['pending_payment']->get_membership(),
		));

	} // end confirmation_form;

	/**
	 * Get checkout details.
	 *
	 * @param string $token PayPal token.
	 *
	 * @return array|bool|string|WP_Error
	 */
	public function get_checkout_details($token = '') {

		$args = array(
			'TOKEN'     => $token,
			'USER'      => $this->username,
			'PWD'       => $this->password,
			'SIGNATURE' => $this->signature,
			'VERSION'   => '124',
			'METHOD'    => 'GetExpressCheckoutDetails',
		);

		$request = wp_remote_post($this->api_endpoint, array(
			'timeout'     => 45,
			'httpversion' => '1.1',
			'body'        => $args
		));

		$body    = wp_remote_retrieve_body($request);
		$code    = wp_remote_retrieve_response_code($request);
		$message = wp_remote_retrieve_response_message($request);

		if (is_wp_error($request)) {

			return $request;

		} elseif (200 === absint($code) && 'OK' === $message) {

			if (is_string($body)) {

				wp_parse_str($body, $body);

			} // end if;

			$pending_payment = wu_get_payment(absint(wu_request('payment-id')));

			if (!empty($pending_payment)) {

				$pending_amount = $pending_payment->get_total();

			} // end if;

			$body['pending_payment'] = $pending_payment;

			$custom = explode('|', $body['PAYMENTREQUEST_0_CUSTOM']);

			return $body;

		} // end if;

		return false;

	} // end get_checkout_details;

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

		$sandbox_prefix = $this->test_mode ? 'sandbox.' : '';

		$base_url = 'https://www.%spaypal.com/us/cgi-bin/webscr?cmd=_profile-recurring-payments&encrypted_profile_id=%s';

		return sprintf($base_url, $sandbox_prefix, $gateway_subscription_id);

	} // end get_subscription_url_on_gateway;

} // end class PayPal_Gateway;
