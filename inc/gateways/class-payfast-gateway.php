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
class PayFast_Gateway extends Base_Gateway {

	/**
	 * Holds the ID of a given gateway.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'payfast';

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
		$this->supports[] = 'ajax-payment';

    /*
     * Set PayFast Credentials
     */
		$this->test_mode    = wu_get_setting('payfast_sandbox_mode', true);
		$this->merchant_id  = wu_get_setting('payfast_merchant_id', '');
		$this->merchant_key = wu_get_setting('payfast_merchant_key', '');
		$this->passphrase   = wu_get_setting('payfast_passphrase', '');

	} // end init;

	/**
	 * Adds the necessary hooks for the payfast gateway.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks() {} // end hooks;

	/**
	 * Adds the Stripe Gateway settings to the settings screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function settings() {

		wu_register_settings_field('payment-gateways', 'payfast_header', array(
			'title'           => __('PayFast', 'wp-ultimo'),
			'desc'            => __('Use the settings section below to configure the PayFast payment method.', 'wp-ultimo'),
			'type'            => 'header',
			'show_as_submenu' => true,
			'require'         => array(
				'active_gateways' => 'payfast',
			),
		));

		wu_register_settings_field('payment-gateways', 'payfast_merchant_id', array(
			'title'      => __('Merchant ID', 'wp-ultimo'),
			'desc'       => __('The PayFast Merchant ID.', 'wp-ultimo'),
			'type'       => 'text',
			'allow_html' => true,
			'default'    => '',
			'require'    => array(
				'active_gateways' => 'payfast',
			),
		));

		wu_register_settings_field('payment-gateways', 'payfast_merchant_key', array(
			'title'      => __('Merchant Key', 'wp-ultimo'),
			'desc'       => __('The PayFast Merchant Key.', 'wp-ultimo'),
			'type'       => 'text',
			'allow_html' => true,
			'default'    => '',
			'require'    => array(
				'active_gateways' => 'payfast',
			),
		));

		wu_register_settings_field('payment-gateways', 'payfast_passphrase', array(
			'title'      => __('Passphrase', 'wp-ultimo'),
			'desc'       => __('PayFast Passphrase.', 'wp-ultimo'),
			'type'       => 'text',
			'allow_html' => true,
			'default'    => '',
			'require'    => array(
				'active_gateways' => 'payfast',
			),
		));

		wu_register_settings_field('payment-gateways', 'payfast_redirect_text', array(
			'title'      => __('Redirect Text', 'wp-ultimo'),
			'desc'       => __('This text is displayed right before we redirect the customer to PayFast to complete the payment.', 'wp-ultimo'),
			'type'       => 'wp_editor',
			'allow_html' => true,
			'default'    => __('Redirecting to PayFast to finish the payment... Click the button if not automatically redirected.', 'wp-ultimo'),
			'require'    => array(
				'active_gateways' => 'payfast',
			),
		));

	} // end settings;

	/**
	 * Validates the payment settings.
	 *
	 * @since 2.0.0
	 * @return array|WP_Error
	 */
	public function process_ajax_signup() {

		$this->membership = $this->memberships[0];

		if (strtoupper($this->membership->get_currency()) !== 'ZAR') {

			return new \WP_Error('currency_not_supported', __('PayFast only supports ZAR as a currency.', 'wp-ultimo'));

		} // end if;

		$duration      = $this->membership->get_duration();
		$duration_unit = $this->membership->get_duration_unit();

		if ($this->get_membership_recurring_type($duration, $duration_unit) === false) {

			return new \WP_Error('billing_frequency_not_supported', __('Payfast payment gateway does not support this billing cycle.', 'wp-ultimo'));

		} // end if;

		if ($this->cart->has_trial()) {

			return new \WP_Error('empty_no_free_trial', __('Payfast payment gateway does not support free trial.', 'wp-ultimo'));

		} // end if;

		/*
		 * All set!
		 */
		return array();

	} // end process_ajax_signup;

	/**
	 * Returns the PayFast formatted frequency number.
	 *
	 * PayFast has a different kind of passing frequency for memberships.
	 *
	 * The cycle period.
	 * 3 - Monthly
	 * 4 - Quarterly
	 * 5 - Biannually
	 * 6 - Annual
	 *
	 * @since 2.0.0
	 *
	 * @param int    $duration The membership duration.
	 * @param string $duration_unit The membership duration unit.
	 * @return int|false
	 */
	public function get_membership_recurring_type($duration, $duration_unit) {

		if ($duration_unit === 'year') {

			return 6;

		} elseif ($duration_unit === 'month' && $duration === 1) {

			return 3; // Monthly

		} elseif ($duration_unit === 'month' && $duration === 3) {

			return 4; // Quarterly

		} elseif ($duration_unit === 'month' && $duration === 6) {

			return 5; // Bi-annualy

		} // end if;

		return false;

	} // end get_membership_recurring_type;

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
	 * @throws \Exception When the error is not processed.
	 * @return void
	 */
	public function process_signup() {

		$this->membership = $this->memberships[0];

		if (!empty($this->membership) && $this->merchant_id && $this->merchant_key) {

			$return_url = $this->get_return_url();

			$cancel_url = wu_get_current_url();

			$notify_url = $this->get_webhook_listener_url() . 'lol';

			$customer = $this->membership->get_customer();

			$params = array(
				'merchant_id'      => $this->merchant_id,
				'merchant_key'     => $this->merchant_key,
				'return_url'       => $return_url,
				'cancel_url'       => $cancel_url,
				'notify_url'       => $notify_url,
				'name_first'       => $this->prepare_string($customer->get_display_name()),
				'name_last'        => $this->prepare_string($customer->get_display_name()),
				'email_address'    => $customer->get_email_address(),
				'm_payment_id'     => $this->payment->get_id(),
				'amount'           => $this->membership->get_initial_amount(),
				'item_name'        => $this->prepare_string($this->membership->get_plan()->get_name()),
				'item_description' => $this->prepare_string($this->membership->get_plan()->get_description()),
				'custom_int1'      => $this->payment->get_id(),
			);

			$is_recurring = $this->membership->is_recurring();

			if ($is_recurring) {

				$params['subscription_type'] = 1;
				$params['recurring_amount']  = $this->membership->get_amount();

				$duration      = $this->membership->get_duration();
				$duration_unit = $this->membership->get_duration_unit();

				$payfast_duration_type = $this->get_membership_recurring_type($duration, $duration_unit);

				if ($payfast_duration_type) {

					$params['frequency'] = $payfast_duration_type;

				} // end if;

				$params['cycles'] = (int) $this->membership->get_billing_cycles();

			} // end if;

			if (!empty($this->passphrase)) {

				$params['passphrase'] = $this->passphrase;

			} // end if;

			$query_string = array();

			foreach ($params as $param => $value) {

				if (!empty($value)) {

					$query_string[] = $param . '=' . urlencode(trim($value));

				} // end if;

			} // end foreach;

			$query_string = implode('&', $query_string);

			$params['signature'] = md5($query_string);

			/*
			 * Passphrase is used just to salt the SIGNATURE.
			 * it should not be passed to payfast server for security reason.
			 */
			unset($params['passphrase']);

			/*
			 * Print the redirect page.
			 */
			wp_head();

			$params = apply_filters('wu_payfast_form_extra_parameters', $params, $this);

			$this->render_payfast_redirect_form($params);

			die;

		} else {

			throw new \Exception('error', __('There is an error in processing PayFast payment. Make sure PayFast payment gateway settings are provided.', 'wp-ultimo'));

		} // end if;

	} // end process_signup;

	/**
	 * Prepare string to send as a parameter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $string The string to prepare.
	 * @return string
	 */
	public function prepare_string($string) {

		return stripslashes(html_entity_decode($string, ENT_COMPAT, 'UTF-8'));

	} // end prepare_string;

	/**
	 * Returns the payfast form payment URL.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function payfast_payment_url() {

		$sandbox = $this->test_mode ? 'sandbox' : 'www';

		$redirect = "https://{$sandbox}.payfast.co.za/eng/process";

		return apply_filters('wu_payfast_redirect', $redirect);

	} // end payfast_payment_url;

  // phpcs:disable

  /**
   * Renders the PayFast redirect form.
   *
   * @since 2.0.0
   *
   * @param array $params The form parameters.
   * @return void
   */
  public function render_payfast_redirect_form($params) { ?>

    <style>
      body {
        text-align: center;
        max-width: 450px;
        margin: 200px auto;
      }
    </style>

    <div class="wu-payfast-form">

      <?php do_action('wu_payfast_form_before', $this); ?>

      <h3>

        <?php 
        
          /**
           * Redirect text
           */
          echo apply_filters('wu_payfast_redirect_text', wu_get_setting('payfast_redirect_text'), $this);
          
        ?>

      </h3>

      <form 
        action="<?php echo $this->payfast_payment_url(); ?>" 
        name="wu_payfast_form" 
        method="POST"
      >

        <?php foreach ($params as $param => $value) : ?>

          <input 
            type="hidden" 
            value="<?php echo esc_attr($value); ?>" 
            name="<?php echo esc_attr($param); ?>"
          >

        <?php endforeach; ?>

        <?php do_action('wu_payfast_form_parameters', $this); ?>

        <input type="submit" style="margin-top: 20px;" value="<?php esc_attr_e('Proceed to PayFast &rarr;', 'wp-ultimo'); ?>">

      </form>

      <script type="text/javascript">

        setTimeout(function() {

          /**
           * Automatically redirect to PayFast...
           */
          document.wu_payfast_form.submit();

        }, 2000)
        
      </script>

      <?php do_action('wu_payfast_form_after', $this); ?>
    
    </div>

  <?php } // end render_payfast_redirect_form;

  // phpcs:enable

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
			You will be redirected to PayFast to complete the payment.
		</p>
		';

	} // end fields;

	/**
	 * Process PayFast IPN
	 *
	 * Listen for webhooks and take appropriate action to insert payments, renew the member's
	 * account, or cancel the membership.
	 *
	 * @access public
	 * @return void
	 */
	public function process_webhooks() {

		$sandbox_mode = $this->test_mode;

		$user_agent = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';

		$error_messages = array(
			'PF_MSG_OK'                => __( 'Payment was successful', 'wp-ultimo'),
			'PF_MSG_FAILED'            => __( 'Payment has failed', 'wp-ultimo'),
			'PF_ERR_AMOUNT_MISMATCH'   => __('Amount mismatch', 'wp-ultimo'),
			'PF_ERR_BAD_SOURCE_IP'     => __('Bad source IP address', 'wp-ultimo'),
			'PF_ERR_CONNECT_FAILED'    => __('Failed to connect to PayFast', 'wp-ultimo'),
			'PF_ERR_BAD_ACCESS'        => __('Bad access of page', 'wp-ultimo'),
			'PF_ERR_INVALID_SIGNATURE' => __('Security signature mismatch', 'wp-ultimo'),
			'PF_ERR_CURL_ERROR'        => __('An error occurred executing cURL', 'wp-ultimo'),
			'PF_ERR_INVALID_DATA'      => __('The data received is invalid', 'wp-ultimo'),
			'PF_ERR_UNKNOWN'           => __('Unknown error occurred', 'wp-ultimo'),
		);

		// Notify PayFast that information has been received
		header('HTTP/1.0 200 OK');

		flush();

		// Variable initialization
		$payfast_error         = false;
		$payfast_error_message = '';

		$output = ''; // DEBUG

		$payfast_param_string = '';
		$payfast_host         = $sandbox_mode ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
		$payfast_data         = array();
		$res                  = '';
		$lines                = array();

		// Dump the submitted variables and calculate security signature
		if (!$payfast_error) {

			$output = "Posted Variables:\n\n"; // DEBUG

			// Strip any slashes in data
			foreach ($_POST as $key => $val) {

				$payfast_data[$key] = stripslashes($val);

			} // end foreach;

			// Dump the submitted variables and calculate security signature
			foreach ($payfast_data as $key => $val) {

				if ($key !== 'signature') {

					$payfast_param_string .= sanitize_text_field($key) . '=' . urlencode($val) . '&';

				} // end if;

			} // end foreach;

			// Remove the last '&' from the parameter string
			$payfast_param_string      = substr($payfast_param_string, 0, -1);
			$payfast_temp_param_string = $payfast_param_string;
			$passphrase                = wu_get_setting('payfast_passphrase', '');

			// If a passphrase has been set in the PayFast Settings, then it needs to be included in the signature string.
			if (!empty($passphrase)) {

				$payfast_temp_param_string .= '&passphrase=' . urlencode($passphrase);

			} // end if;

			$signature = md5($payfast_temp_param_string);

			$result = (wu_request('signature') === $signature);

			$output .= "Security Signature:\n\n"; // DEBUG
			$output .= '- posted     = ' . wu_request('signature') . "\n"; // DEBUG
			$output .= '- calculated = ' . $signature . "\n"; // DEBUG
			$output .= '- result     = ' . ($result ? 'SUCCESS' : 'FAILURE') . "\n"; // DEBUG

		} // end if;

		// Verify source IP
		if (!$payfast_error) {

			$valid_hosts = array(
				'www.payfast.co.za',
				'sandbox.payfast.co.za',
				'w1w.payfast.co.za',
				'w2w.payfast.co.za',
			);

			$valid_ips = array();

			foreach ($valid_hosts as $payfast_hostname) {

				$ips = gethostbynamel($payfast_hostname);

				if ($ips !== false) {

					$valid_ips = array_merge($valid_ips, $ips);

				} // end if;

			} // end foreach;

			// Remove duplicates
			$valid_ips = array_unique($valid_ips);

			if (!in_array($_SERVER['REMOTE_ADDR'], $valid_ips, true)) {

				$payfast_error = true;

				$payfast_error_message = wu_get_isset($error_messages, 'PF_ERR_BAD_SOURCE_IP', 'Error');

			} // end if;

		} // end if;

		$payment_info = wu_get_payment(wu_request('m_payment_id'));

		$cart_total = $payment_info->get_total();

		if (wu_request('payment_status') === 'COMPLETE' && abs(floatval($cart_total) - floatval(wu_request('amount_gross'))) > 0.01) {

			$payfast_error = true;

			$payfast_error_message = PF_ERR_AMOUNT_MISMATCH;

		} // end if;

		// Connect to server to validate data received
		if (!$payfast_error) {

			// Use cURL (If it's available)
			if (function_exists('curl_init')) {

				$output .= "\n\nUsing cURL\n\n"; // DEBUG

				// Create default cURL object
				$ch = curl_init();

				// Base settings
				$curl_opts = array(
					// Base options
					CURLOPT_USERAGENT      => $user_agent, // Set user agent
					CURLOPT_RETURNTRANSFER => true,  // Return output as string rather than outputting it
					CURLOPT_HEADER         => false,         // Don't include header in output
					CURLOPT_SSL_VERIFYHOST => 2,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_URL            => 'https://' . $payfast_host . '/eng/query/validate',
					CURLOPT_POST           => true,
					CURLOPT_POSTFIELDS     => $payfast_param_string,
				);

				curl_setopt_array($ch, $curl_opts);

				// Execute CURL
				$res = curl_exec($ch);

				curl_close($ch);

				if ($res === false) {

					$payfast_error = true;

					$payfast_error_message = wu_get_isset($error_messages, 'PF_ERR_CURL_ERROR', 'Error');

				} // end if;

			} else { // Use fsockopen

				$output .= "\n\nUsing fsockopen\n\n"; // DEBUG

				// Construct Header
				$header  = "POST /eng/query/validate HTTP/1.0\r\n";
				$header .= 'Host: ' . $payfast_host . "\r\n";
				$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$header .= 'Content-Length: ' . strlen($payfast_param_string) . "\r\n\r\n";

				// Connect to server
				$socket = fsockopen('ssl://' . $payfast_host, 443, $errno, $errstr, 10);

				// Send command to server
				fputs($socket, $header . $payfast_param_string);

				// Read the response from the server
				$res         = '';
				$header_done = false;

				while (!feof($socket)) {

					$line = fgets($socket, 1024);

					// Check if we are finished reading the header yet
					if (strcmp($line, PHP_EOL) === 0) {

						// read the header
						$header_done = true;

					} elseif ($header_done) {

						// Read the main response
						$res .= $line;

					} // end if;

				} // end while;

			} // end if;

		} // end if;

		// Get data from server
		if (!$payfast_error) {

			// Parse the returned data
			$lines = explode("\n", $res);

			$output .= "\n\nValidate response from server:\n\n"; // DEBUG

			foreach ($lines as $line) { // DEBUG

				$output .= $line . "\n"; // DEBUG

			} // end foreach;

		} // end if;

		wu_log_add('payfast-ipn', json_encode($_REQUEST, JSON_PRETTY_PRINT));

		// Interpret the response from server
		if (!$payfast_error || $sandbox_mode) {

			// Get the response from PayFast (VALID or INVALID)
			$result = trim($lines[0]);

			$output .= "\nResult = " . $result; // DEBUG

			// If the transaction was valid
			if (strcmp($result, 'VALID') === 0 || $sandbox_mode) {

					// Process as required
				$itn = $_REQUEST;

				// This will send receipts on successful invoices
				$event_type     = $itn['payment_status'];
				$transaction_id = $itn['pf_payment_id'];
				$payment_id     = $itn['m_payment_id'];

				$payment = wu_get_payment($payment_id);

				$membership = false;

				if ($payment) {

					$membership = $payment->get_membership();

				} // end if;

				if ($membership === false) {

					return;

				} // end if;

				if ($event_type === 'COMPLETE') {

					wu_log_add('payfast', sprintf(__('PayFast Subscription ID: %s', 'wp-ultimo'), $transaction_id), '', '', true);

					if ($membership->get_times_billed() < 1) {

						$payment->set_gateway_payment_id($transaction_id);

						$payment->set_status('completed');

						$payment->save();

						// do_action('wpinv_payfast_subscription_create', $subscription->id, $itn, $payment_id);

					} else {

						$customer = $membership->get_customer();

						$order = new \WP_Ultimo\Checkout\Cart(array(
							'memberships' => array($membership->get_id()),
							'cart_type'   => 'renewal',
							'country'     => $customer->get_country(),
						));

						$payment_data = $order->to_payment_data();

						$payment_data['status']        = 'completed';
						$payment_data['gateway']       = 'payfast';
						$payment_data['customer_id']   = $customer->get_id();
						$payment_data['membership_id'] = $membership->get_id();

						$new_payment = wu_create_payment($payment_data);

						// do_action('wpinv_payfast_subscription_renew', $subscription->id, $itn, $payment_id, $new_invoice_id);

					} // end if;

					/**
					 * Renew Membership and Save.
					 */
					$membership->renew(true, 'active');

					$times_billed = $membership->get_times_billed();

					$membership->set_times_billed($times_billed + 1);

					$membership->save();

				} elseif ($event_type === 'FAILED') {

					$payment->set_status('failed');

					$payment->save();

					wu_log_add('payfast', sprintf(__('PayFast Transaction Failed. ITN data: %s', 'wp-ultimo'), $output));

					// do_action('wpinv_payfast_subscription_failed', $subscription->id, $itn, $payment_id);

				} elseif ($event_type === 'CANCEL') {

					$payment->set_status('failed');

					$payment->save();

					$membership->cancel();

					wu_log_add('payfast', sprintf(__('PayFast Transaction Cancelled. ITN data: %s', 'wp-ultimo'), $output));

					// do_action('wpinv_payfast_subscription_cancel', $subscription->id, $itn, $payment_id);

				} else {

					$payment->set_status('on-hold');

					$payment->save();

					$membership->set_status('pending');

					$membership->save();

					// do_action('wpinv_payfast_subscription_pending', $subscription->id, $itn, $payment_id);

				} // end if;

			} else {

				// Log for investigation
				$payfast_error = true;

				$payfast_error_message = wu_get_isset($error_messages, 'PF_ERR_INVALID_DATA', 'Error');

			} // end if;

		} // end if;

		wu_log_add('payfast', $output);

		// If an error occurred
		if ($payfast_error) {

			$output .= "\n\nAn error occurred!";

			$output .= "\nError = " . $payfast_error_message;

			wu_log_add('payfast', sprintf(__('Invalid ITN verification response. ITN data: %s', 'wp-ultimo'), $output));

		} // end if;

		die('Thanks, PayFast!');

	} // end process_webhooks;

} // end class PayFast_Gateway;
