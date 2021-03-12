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

// phpcs:disable

namespace WP_Ultimo\Gateways;

use WP_Ultimo\Gateways\Base_Gateway;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base Gateway class. Should be extended to add new payment gateways.
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
	 * Initialize the gateway configuration
	 *
	 * This is used to populate the $supports property, setup any API keys, and set the API endpoint.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		$this->supports[] = 'one-time';
		$this->supports[] = 'recurring';
		$this->supports[] = 'fees';
		$this->supports[] = 'trial';

		$this->test_mode = wu_get_setting('paypal_sandbox_mode', true);

		if ($this->test_mode) {

			$this->api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
			$this->checkout_url = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';

			$this->username  = wu_get_setting('paypal_test_username', '');
			$this->password  = wu_get_setting('paypal_test_password', '');
			$this->signature = wu_get_setting('paypal_test_signature', '');

		} else {

			$this->api_endpoint = 'https://api-3t.paypal.com/nvp';
			$this->checkout_url = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';

			$this->username  = wu_get_setting('paypal_live_username', '');
			$this->password  = wu_get_setting('paypal_live_password', '');
			$this->signature = wu_get_setting('paypal_live_signature', '');

		} // end if;

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
			'title'       => __('PayPal Test Username', 'wp-ultimo'),
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
			'title'       => __('PayPal Live Username', 'wp-ultimo'),
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

		global $wu_options;

		if ($this->auto_renew) {

			$amount = $this->amount;

		} else {

			$amount = $this->initial_amount;

		} // end if;

		$cancel_url = wp_get_referer();

		if (empty($cancel_url)) {

			$cancel_url = add_query_arg('cancel', '1', home_url());

		} // end if;

		$args = array(
			'USER'                           => $this->username,
			'PWD'                            => $this->password,
			'SIGNATURE'                      => $this->signature,
			'VERSION'                        => '124',
			'METHOD'                         => 'SetExpressCheckout',

			'PAYMENTREQUEST_0_SHIPPINGAMT'   => 0,
			'PAYMENTREQUEST_0_TAXAMT'        => 0,
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'PAYMENTREQUEST_0_AMT'           => $amount,
			'PAYMENTREQUEST_0_ITEMAMT'       => $amount,
			'PAYMENTREQUEST_0_CURRENCYCODE'  => strtoupper('USD'),
			'PAYMENTREQUEST_0_DESC'          => html_entity_decode( substr( 'SUBSCRIPTION', 0, 127 ), ENT_COMPAT, 'UTF-8' ),

			// Add multiple items: https://stackoverflow.com/questions/31957791/paypal-subscription-for-multiple-product-using-paypal-api

			'PAYMENTREQUEST_0_CUSTOM'        => $this->user_id . '|' . absint( 1000 ),
			'PAYMENTREQUEST_0_NOTIFYURL'     => add_query_arg( 'listener', 'EIPN', home_url( 'index.php' ) ),

			'EMAIL'                          => $this->email,
			'CANCELURL'                      => $cancel_url,
			'NOSHIPPING'                     => 1,
			'REQCONFIRMSHIPPING'             => 0,
			'ALLOWNOTE'                      => 0,
			'ADDROVERRIDE'                   => 0,
			'PAGESTYLE'                      => '',
			'SOLUTIONTYPE'                   => 'Sole',
			'LANDINGPAGE'                    => 'Billing',

			'RETURNURL'                      => add_query_arg(array(
				'wu-confirm'    => 'paypal',
				'payment-id' => urlencode($this->payment->get_id()),
			), home_url('register')),
		);

		$product_index = 0;

		/**
		 * Loop products and add them to the paypal
		 */
		foreach ($this->cart->get_all_products() as $product) {

			$product_args = array(
				// "L_PAYMENTREQUEST_0_NAME{$product_index}" => $product->get_name(),
				// "L_PAYMENTREQUEST_0_DESC{$product_index}" => $product->get_description(),
				// "L_PAYMENTREQUEST_0_AMT{$product_index}"  => $product->get_amount(),
				// "L_PAYMENTREQUEST_0_TAXAMT{$product_index}" => $product->tax_amount, 2,
				// "L_PAYMENTREQUEST_0_QTY{$product_index}"  => 1,
			);

			$args = array_merge($args, $product_args);

			$product_index++;

		} // end foreach;

		if ($this->auto_renew && !empty($this->length)) {

			$args['L_BILLINGAGREEMENTDESCRIPTION0'] = html_entity_decode(substr('SUBSCRIPTION', 0, 127), ENT_COMPAT, 'UTF-8');

			$args['L_BILLINGTYPE0'] = 'RecurringPayments';

			$args['RETURNURL'] = add_query_arg(array(
				'wu-recurring' => '1'
			), $args['RETURNURL']);

		} // end if;

		$request = wp_remote_post($this->api_endpoint, array(
			'timeout'     => 45,
			'httpversion' => '1.1',
			'body'        => $args
		));

		$body    = wp_remote_retrieve_body($request);
		$code    = wp_remote_retrieve_response_code($request);
		$message = wp_remote_retrieve_response_message($request);

		if (is_wp_error($request)) {

			$this->error_message = $request->get_error_message();

			do_action('wu_registration_failed', $this);

			do_action('wu_paypal_express_signup_payment_failed', $request, $this);

			$error  = '<p>' . __( 'An unidentified error occurred.', 'wp-ultimo' ) . '</p>';
			$error .= '<p>' . $request->get_error_message() . '</p>';

			wp_die( $error, __( 'Error', 'wp-ultimo' ), array( 'response' => '401' ) );

		} elseif (200 == $code && 'OK' == $message) {

			if (is_string($body)) {

				wp_parse_str($body, $body);

			} // end if;

			if ('failure' === strtolower($body['ACK'])) {

				$this->error_message = $body['L_LONGMESSAGE0'];

				do_action( 'wu_registration_failed', $this );

				$error  = '<p>' . __( 'PayPal token creation failed.', 'wp-ultimo' ) . '</p>';
				$error .= '<p>' . __( 'Error message:', 'wp-ultimo' ) . ' ' . $body['L_LONGMESSAGE0'] . '</p>';
				$error .= '<p>' . __( 'Error code:', 'wp-ultimo' ) . ' ' . $body['L_ERRORCODE0'] . '</p>';

				wp_die($error, __('Error', 'wp-ultimo'), array('response' => '401'));

			} else {

				// Successful token
				wp_redirect($this->checkout_url . $body['TOKEN']);

				exit;

			} // end if;

		} else {

			do_action('wu_registration_failed', $this);

			wp_die( __( 'Something has gone wrong, please try again', 'wp-ultimo' ), __( 'Error', 'wp-ultimo' ), array( 'back_link' => true, 'response' => '401' ) );

		} // end if;

	} // end process_signup;

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

		if (wp_verify_nonce(wu_request('wu_ppe_confirm_nonce', 'no-nonce'), 'wu-ppe-confirm-nonce')) {

			$details = $this->get_checkout_details(wu_request('token'));

			$payment = $details['pending_payment'];

			$original_cart = $payment->get_meta('original_cart');

			if (!wu_request('wu-recurring')) {

				// Successful payment, now create the recurring profile

				$args = array(
					'USER'                => $this->username,
					'PWD'                 => $this->password,
					'SIGNATURE'           => $this->signature,
					'VERSION'             => '124',
					'TOKEN'               => $_POST['token'],
					'METHOD'              => 'CreateRecurringPaymentsProfile',
					'PROFILESTARTDATE'    => date( 'Y-m-d\TH:i:s', strtotime( '+' . $original_cart->get_duration() . ' ' . $original_cart->get_duration_unit(), current_time( 'timestamp' ) ) ),
					'BILLINGPERIOD'       => ucwords( $original_cart->get_duration_unit() ),
					'BILLINGFREQUENCY'    => $original_cart->get_duration(),
					'AMT'                 => $original_cart->get_recurring_total(),
					'INITAMT'             => $original_cart->get_total(),
					'CURRENCYCODE'        => 'USD',
					'FAILEDINITAMTACTION' => 'CancelOnFailure',
					'L_BILLINGTYPE0'      => 'RecurringPayments',
					'DESC'                => html_entity_decode( substr( 'SUBSCRIPTION', 0, 127 ), ENT_COMPAT, 'UTF-8' ),
					'BUTTONSOURCE'        => 'WPUltimo'
				);

				if ( $args['INITAMT'] < 0 ) {

					unset( $args['INITAMT'] );

				} // end if;

				// if ( $membership->get_trial_end_date() && 0 === $membership->get_times_billed() ) {

				// 	// Set profile start date to the end of the free trial.
				// 	$args['PROFILESTARTDATE'] = date( 'Y-m-d\TH:i:s', strtotime( $membership->get_trial_end_date(), current_time( 'timestamp' ) ) );

				// 	unset( $args['INITAMT'] );

				// } // end if;

				$request = wp_remote_post( $this->api_endpoint, array(
					'timeout'     => 45,
					'httpversion' => '1.1',
					'body'        => $args
				) );

				$body    = wp_remote_retrieve_body( $request );
				$code    = wp_remote_retrieve_response_code( $request );
				$message = wp_remote_retrieve_response_message( $request );

				if ( is_wp_error( $request ) ) {

					$error  = '<p>' . __( 'An unidentified error occurred.', 'wp-ultimo' ) . '</p>';
					$error .= '<p>' . $request->get_error_message() . '</p>';

					wp_die( $error, __( 'Error', 'wp-ultimo' ), array( 'response' => '401' ) );

				} elseif ( 200 == $code && 'OK' == $message ) {

					if ( is_string( $body ) ) {
						wp_parse_str( $body, $body );
					} // end if;

					if ( 'failure' === strtolower( $body['ACK'] ) ) {

						$error  = '<p>' . __( 'PayPal payment processing failed.', 'wp-ultimo' ) . '</p>';
						$error .= '<p>' . __( 'Error message:', 'wp-ultimo' ) . ' ' . $body['L_LONGMESSAGE0'] . '</p>';
						$error .= '<p>' . __( 'Error code:', 'wp-ultimo' ) . ' ' . $body['L_ERRORCODE0'] . '</p>';

						wp_die( $error, __( 'Error', 'wp-ultimo' ), array( 'response' => '401' ) );

					} else {

						$membership->set_gateway_subscription_id( $body['PROFILEID'] );

						wp_redirect( esc_url_raw( wu_get_return_url() ) );
						exit;

					} // end if;

				} else {

					wp_die( __( 'Something has gone wrong, please try again', 'wp-ultimo' ), __( 'Error', 'wp-ultimo' ), array( 'back_link' => true, 'response' => '401' ) );

				} // end if;

			} else {

				// One time payment

				$args = array(
					'USER'                           => $this->username,
					'PWD'                            => $this->password,
					'SIGNATURE'                      => $this->signature,
					'VERSION'                        => '124',
					'METHOD'                         => 'DoExpressCheckoutPayment',
					'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
					'TOKEN'                          => $_POST['token'],
					'PAYERID'                        => $_POST['payer_id'],
					'PAYMENTREQUEST_0_AMT'           => $details['AMT'],
					'PAYMENTREQUEST_0_ITEMAMT'       => $details['AMT'],
					'PAYMENTREQUEST_0_SHIPPINGAMT'   => 0,
					'PAYMENTREQUEST_0_TAXAMT'        => 0,
					'PAYMENTREQUEST_0_CURRENCYCODE'  => $details['CURRENCYCODE'],
					'BUTTONSOURCE'                   => 'EasyDigitalDownloads_SP'
				);

				$request = wp_remote_post( $this->api_endpoint, array(
					'timeout'     => 45,
					'httpversion' => '1.1',
					'body'        => $args
				) );
				$body    = wp_remote_retrieve_body( $request );
				$code    = wp_remote_retrieve_response_code( $request );
				$message = wp_remote_retrieve_response_message( $request );

				if ( is_wp_error( $request ) ) {

					$error  = '<p>' . __( 'An unidentified error occurred.', 'wp-ultimo' ) . '</p>';
					$error .= '<p>' . $request->get_error_message() . '</p>';

					wp_die( $error, __( 'Error', 'wp-ultimo' ), array( 'response' => '401' ) );

				} elseif ( 200 == $code && 'OK' == $message ) {

					if ( is_string( $body ) ) {
						wp_parse_str( $body, $body );
					} // end if;

					if ( 'failure' === strtolower( $body['ACK'] ) ) {

						$error  = '<p>' . __( 'PayPal payment processing failed.', 'wp-ultimo' ) . '</p>';
						$error .= '<p>' . __( 'Error message:', 'wp-ultimo' ) . ' ' . $body['L_LONGMESSAGE0'] . '</p>';
						$error .= '<p>' . __( 'Error code:', 'wp-ultimo' ) . ' ' . $body['L_ERRORCODE0'] . '</p>';

						wp_die( $error, __( 'Error', 'wp-ultimo' ), array( 'response' => '401' ) );

					} else {

						$payment_data = array(
							'date'             => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
							'subscription'     => $membership->get_membership_level_name(),
							'payment_type'     => 'PayPal Express One Time',
							'subscription_key' => $membership->get_subscription_key(),
							'amount'           => $body['PAYMENTINFO_0_AMT'],
							'user_id'          => $membership->get_user_id(),
							'transaction_id'   => $body['PAYMENTINFO_0_TRANSACTIONID'],
							'status'           => 'complete'
						);

						$wu_payments = new \WP_Ultimo\Models\Payment;

						$pending_payment_id = wu_get_membership_meta( $membership->get_id(), 'pending_payment_id', true );

						if ( !empty( $pending_payment_id ) ) {
							$wu_payments->update( $pending_payment_id, $payment_data );
						} // end if;

						// Membership is activated via wu_complete_registration()

						wp_redirect( esc_url_raw( wu_get_return_url() ) );
						exit;

					} // end if;

				} else {

					wp_die( __( 'Something has gone wrong, please try again', 'wp-ultimo' ), __( 'Error', 'wp-ultimo' ), array( 'back_link' => true, 'response' => '401' ) );

				} // end if;

			} // end if;

		} elseif (!empty($_GET['token']) && !empty($_GET['PayerID'])) {

			add_filter('the_content', array($this, 'confirmation_form'), 9999999);

		} // end if;

	} // end process_confirmation;

	/**
	 * Display the confirmation form
	 *
	 * @since 2.1
	 * @return string
	 */
	public function confirmation_form() {

		$token = sanitize_text_field(wu_request('token'));

		$checkout_details = $this->get_checkout_details($token);

		if (!is_array($checkout_details)) {

			$error = is_wp_error($checkout_details) ? $checkout_details->get_error_message() : __( 'Invalid response code from PayPal', 'wp-ultimo');

			return '<p>' . sprintf(__( 'An unexpected PayPal error occurred. Error message: %s.', 'wp-ultimo'), $error) . '</p>';

		} // end if;

		/*
		 * Compiles the necessary elements
		 */
		$customer = $checkout_details['pending_payment']->get_customer(); // current customer

		// $membership_id = !empty($checkout_details['membership_id']) ? absint($checkout_details['membership_id']) : 0;

		// $membership_level = wu_get_subscription_details( $payment->object_id );

		wu_get_template('checkout/paypal/confirm', array(
			'checkout_details' => $checkout_details,
			'customer'         => $customer,
			'payment'          => $checkout_details['pending_payment'],
			'membership'       => $checkout_details['pending_payment']->get_membership(),
		));

	} // end confirmation_form;

	/**
	 * Get checkout details
	 *
	 * @param string $token
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

		} elseif (200 == $code && 'OK' == $message) {

			if (is_string($body)) {

				wp_parse_str($body, $body);

			} // end if;

			// $membership          = wu_get_membership(absint($_GET['membership_id']));
			// $membership          = wu_get_membership(36);
			// $membership_level_id = $membership->get_plan_id();

			$pending_payment     = wu_get_payment(absint(wu_request('payment-id')));

			if (!empty($pending_payment)) {

				$pending_amount = $pending_payment->get_total();

			} // end if;

			// } elseif (0 == $membership->get_times_billed()) {

			// 	$pending_amount = $membership->get_initial_amount();

			// } else {

			// 	$pending_amount = $membership->get_recurring_amount();

			// } // end if;

			$body['pending_payment']   = $pending_payment;
			// $body['initial_amount'] = $pending_amount;

			$custom = explode('|', $body['PAYMENTREQUEST_0_CUSTOM']);

			// $body['membership_id'] = !empty($custom[1]) ? absint($custom[1]) : 0;

			return $body;

		} // end if;

		return false;

	} // end get_checkout_details;

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

		return '
		<p class="wu-p-4 wu-bg-yellow-200">
			You will be redirected to PayPal to complete the purchase.
		</p>
		';

	} // end fields;

} // end class PayPal_Gateway;
