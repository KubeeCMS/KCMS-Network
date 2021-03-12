<?php
/**
 * Handles the processing of new membership purchases.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Checkout\Cart;
use \WP_Ultimo\Database\Sites\Site_Type;

/**
 * Handles the processing of new membership purchases.
 *
 * @since 2.0.0
 */
class Checkout {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds checkout errors.
	 *
	 * @since 2.0.0
	 * @var \WP_Error|null
	 */
	public $errors;

	/**
	 * Keeps a reference to our order.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Checkout\Cart
	 */
	protected $order;

	// Checkout Progress Info

	/**
	 * Current step of the signup flow.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $step;

	/**
	 * Keeps the name of the step.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $step_name;

	/**
	 * The slug of the current checkout form.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $checkout_form;

	/**
	 * List of steps for the signup flow.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $steps;

	/**
	 * Session object.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Session.
	 */
	protected $session;

	/**
	 * Initializes the Checkout singleton and adds hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_shortcode('wu_confirmation', array($this, 'render_confirmation_page'));

		add_action('wu_setup_checkout', array($this, 'setup_checkout'));

		add_action('wu_setup_checkout', array($this, 'maybe_process_checkout'), 20);

		add_action('wu_checkout_scripts', array($this, 'register_scripts'));

		add_action('wp_ajax_wu_create_order', array($this, 'create_order'));

		add_action('wp_ajax_nopriv_wu_create_order', array($this, 'create_order'));

		add_action('wp_ajax_wu_validate_form', array($this, 'maybe_handle_order_submission'));

		add_action('wp_ajax_nopriv_wu_validate_form', array($this, 'maybe_handle_order_submission'));

		add_filter('request', array($this, 'get_products_from_query'));

		add_filter('display_post_states', array($this, 'add_wp_ultimo_status_annotation'), 10, 2);

		add_action('before_signup_header', array($this, 'redirect_to_registration_page'));

		add_filter('login_url', array($this, 'filter_login_url'), 10, 3);

		add_action('login_head', array($this, 'maybe_obfuscate_login_url'), 9);

		add_action('wu_thank_you_site_block', array($this, 'add_verify_email_notice'), 10, 3);

	} // end init;

	/**
	 * Adds the unverified email account error message.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment The current payment.
	 * @param \WP_Ultimo\Models\Membership $membership the current membership.
	 * @param \WP_Ultimo\Models\Customer   $customer the current customer.
	 * @return void
	 */
	public function add_verify_email_notice($payment, $membership, $customer) {

		if ($payment->get_total() == 0 && $customer->get_email_verification() === 'pending') {

			$html = '<div class="wu-p-4 wu-bg-yellow-200 wu-mb-2 wu-text-yellow-700 wu-rounded">%s</div>';

			$message = __('Your email address is not yet verified. Your site <strong>will only be activated</strong> after your email address is verified. Check your inbox and verify your email address.', 'wp-ultimo');

			printf($html, $message);

		} // end if;

	} // end add_verify_email_notice;

	/**
	 * Check if we should obfuscate the login URL.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_obfuscate_login_url() {

		$use_custom_login = wu_get_setting('enable_custom_login_page', false);

		if ($use_custom_login) {

			return;

		} // end if;

		$new_login_url = $this->get_page_url('login');

		if (!$new_login_url) {

			return;

		} // end if;

		$should_obfuscate = wu_get_setting('obfuscate_original_login_url', 1);

		if ($should_obfuscate) {

			status_header(404);

			nocache_headers();

			include(get_query_template('404'));

			die;

		} else {

			wp_redirect($new_login_url);

			exit;

		} // end if;

	} // end maybe_obfuscate_login_url;

	/**
	 * Redirects the customers to the registration page, when one is used.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function redirect_to_registration_page() {

		$registration_url = $this->get_page_url('register');

		if ($registration_url) {

			wp_redirect($registration_url);

			exit;

		} // end if;

	} // end redirect_to_registration_page;

	/**
	 * Filters the login URL if necessary.
	 *
	 * @since 2.0.0
	 *
	 * @param string $login_url Original login URL.
	 * @param string $redirect URL to redirect to after login.
	 * @param bool   $force_reauth If we need to force reauth.
	 * @return string
	 */
	public function filter_login_url($login_url, $redirect, $force_reauth) {

		$new_login_url = $this->get_page_url('login');

		if (!$new_login_url) {

			return $login_url;

		} // end if;

		$new_login_url = add_query_arg('redirect_to', $redirect, $new_login_url);

		return $login_url;

	} // end filter_login_url;

	/**
	 * Returns the ID of the pages being used for each WP Ultimo purpose.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_signup_pages() {

		return array(
			'register' => wu_get_setting('default_registration_page', false),
			'login'    => wu_get_setting('default_login_page', false),
		);

	} // end get_signup_pages;

	/**
	 * Returns the WP_Post object for one of the pages.
	 *
	 * @since 2.0.0
	 *
	 * @param string $page The slug of the page to retrieve.
	 * @return \WP_Post|false
	 */
	public function get_signup_page($page) {

		$pages = $this->get_signup_pages();

		$page_id = wu_get_isset($pages, $page);

		if (!$page_id) {

			return false;

		} // end if;

		return get_blog_post(wu_get_main_site_id(), $page_id);

	} // end get_signup_page;

	/**
	 * Returns the URL for a particular page type.
	 *
	 * @since 2.0.0
	 *
	 * @param array $page Page to return the URL.
	 * @return string
	 */
	public function get_page_url($page) {

		$pages = $this->get_signup_pages();

		$page_id = wu_get_isset($pages, $page);

		if (!$page_id) {

			return false;

		} // end if;

		return wu_switch_blog_and_run(function() use ($page_id) {

			return get_the_permalink($page_id);

		});

	} // end get_page_url;

	/**
	 * Tags the WP Ultimo pages on the main site.
	 *
	 * @since 2.0.0
	 *
	 * @param array    $states The previous states of that page.
	 * @param \WP_Post $post The current post.
	 * @return array
	 */
	public function add_wp_ultimo_status_annotation($states, $post) {

		if (!is_main_site()) {

			return $states;

		} // end if;

		$labels = array(
			'register' => __('WP Ultimo - Register Page', 'wp-ultimo'),
			'login'    => __('WP Ultimo - Login Page', 'wp-ultimo'),
		);

		$pages = array_map('abs', $this->get_signup_pages());

		if (in_array($post->ID, $pages, true)) {

			$key = array_search($post->ID, $pages, true);

			$states['wp_ultimo_page'] = $labels[$key];

		} // end if;

		return $states;

	} // end add_wp_ultimo_status_annotation;

	/**
	 * Setups the necessary boilerplate code to have checkouts work.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup_checkout() {

		$this->checkout_form = wu_get_checkout_form_by_slug(wu_request('checkout_form'));

		$this->session = wu_get_session('signup');

		if ($this->checkout_form) {

			$this->steps = $this->checkout_form->get_settings();

			$this->step_name = wu_request('checkout_step');

			$this->step = $this->checkout_form->get_step($this->step_name);

			$this->step['fields'] = wu_create_checkout_fields($this->step['fields']);

		} // end if;

		$session = $this->session->get('signup');

		/*
		 * Geolocate customer for taxation purposes.
		 */
		if (!isset($session['country'])) {

			$geolocation = \WP_Ultimo\Geolocation::geolocate_ip('', true);

			$this->session->add_values('signup', $geolocation);

		} // end if;

		if (is_user_logged_in()) {

			$_REQUEST['user_id'] = get_current_user_id();

		} // end if;

	} // end setup_checkout;

	/**
	 * Returns the name of the next step on the flow.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_next_step_name() {

		$steps = $this->steps;

		/*
		 * Filter only available steps
		 */
		$steps = $this->maybe_hide_steps($steps);

		$keys = array_column($steps, 'id');

		$current_step_index = array_search($this->step_name, array_values($keys), true);

		return $keys[$current_step_index + 1];

	} // end get_next_step_name;

	/**
	 * Takes into consideration the visibility of the steps.
	 *
	 * @since 2.0.0
	 *
	 * @param array $steps The steps.
	 * @return array
	 */
	public function maybe_hide_steps($steps) {

		return array_filter($steps, function($step) {

			$logged = wu_get_isset($step, 'logged', 'always');

			if ($logged === 'always') {

				return true;

			} // end if;

			if ($logged === 'guests_only' && !$this->is_existing_user()) {

				return true;

			} // end if;

			if ($logged === 'logged_only' && $this->is_existing_user()) {

				return true;

			} // end if;

			return false;

		});

	} // end maybe_hide_steps;

	/**
	 * Checks if we are in the last step of the signup.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_last_step() {

		$step_names = array_column($this->steps, 'id');

		return $this->step_name === array_pop($step_names);

	} // end is_last_step;

	/**
	 * Checks if we should pre-fill checkout fields based on the request.
	 *
	 * We do a couple of clever things here:
	 * 1. We check for a plan slug right after the checkout/slug of the main page.
	 *
	 * @since 2.0.0
	 *
	 * @param array $request WordPress request.
	 * @return array
	 */
	public function get_products_from_query($request) {

		$checkout_page = $this->get_signup_page('register');

		if (!$checkout_page) {

			return $request;

		} // end if;

		$checkout_page_slug = $checkout_page->post_name;

		$page_name = isset($request['pagename']) ? $request['pagename'] : '';

		if (strpos($page_name, "{$checkout_page_slug}/") === 0) {

			$page = explode('/', $page_name);

			$product_slug = $page[1];

			$product = wu_get_product_by_slug($product_slug);

			if ($product) {

				$_products = wu_request('products', array());

				$product_list = array_merge(
					$_products,
					array($product->get_id())
				);

				$_REQUEST['products'] = array_unique($product_list);

			} // end if;

			$request['pagename'] = $checkout_page_slug;

		} // end if;

		return $request;

	} // end get_products_from_query;

	/**
	 * Decides if we want to handle a step submission or a full checkout submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_handle_order_submission() {

		$this->setup_checkout();

		if ($this->is_last_step()) {

			$this->handle_order_submission();

		} else {

			$validation = $this->validate();

			if (is_wp_error($validation)) {

				wp_send_json_error($validation);

			} // end if;

			wp_send_json_success(array(

			));

		} // end if;

	} // end maybe_handle_order_submission;

	/**
	 * Get thank you page URL.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_thank_you_page() {

		return wu_get_current_url();

	} // end get_thank_you_page;

	/**
	 * Validates the order submission, and then delegates the processing to the gateway.
	 *
	 * We use database transactions in here to prevent failed sign-ups from being
	 * committed to the database. This means that if a \Throwable or a \WP_Error
	 * happens anywhere in the process, we halt it and rollback on writes up to that point.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_order_submission() {

		global $wpdb;

		$wpdb->query('START TRANSACTION');

		try {

			/**
			 * Here's where we actually process the order.
			 *
			 * Throwables are catched and they rollback
			 * any database writes sent up until this point.
			 *
			 * @see process_order below.
			 * @since 2.0.0
			 */
			$results = $this->process_order();

			if (is_wp_error($results)) {

				$this->errors = $results;

			} // end if;

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			$this->errors = new \WP_Error('exception-order-submission', $e->getMessage(), $e->getTrace());

		} // end try;

		if (is_wp_error($this->errors)) {

			$wpdb->query('ROLLBACK');

			wp_send_json_error($this->errors);

		} // end if;

		$wpdb->query('COMMIT');

		wp_send_json_success($results);

	} // end handle_order_submission;

	/**
	 * Handles order processing and delegating to the gateways.
	 *
	 * This method is the heart of our checkout system.
	 * It runs when the checkout form is submitted, then it follows the steps below:
	 *
	 * 1. Creates a Cart object (@see \WP_Ultimo\Checkout\Cart) with the products and discount codes;
	 * 2. Validates the form fields against the validation_rules() method;
	 * 3. Checks if the customer exists or not, and try to create one if needed;
	 * 4. Loops through cart products to create memberships and pending payments for each one;
	 * 5. Sends an array of additional hidden fields to be added to the checkout form.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function process_order() {

		global $current_site, $wpdb;

		/*
		 * Keep track if we have created the site.
		 */
		$created_site = false;

		/*
		 * Reload the order for checks
		 */
		$this->order = new Cart(array(
			'products'      => $this->request_or_session('products', array()),
			'discount_code' => $this->request_or_session('discount_code'),
			'country'       => $this->request_or_session('country'),
		));

		if (!$this->order->get_all_products()) {

			return new \WP_Error('no-product', __('No product was found.', 'wp-ultimo'));

		} // end if;

		$gateway_id = $this->request_or_session('gateway');

		$gateway = wu_get_gateway($gateway_id);

		if (!$gateway) {

			return new \WP_Error('no-gateway', __('Payment gateway not registered.', 'wp-ultimo'));

		} // end if;

		$validation = $this->validate();

		if (is_wp_error($validation)) {

			return $validation;

		} // end if;

		// Check if a customer was passed
		$customer = wu_get_current_customer();

		/*
		 * User is logged in, but has no customer
		 */
		if (!$customer) {

			$customer_data = array(
				'email'              => $this->request_or_session('email_address'),
				'username'           => $this->request_or_session('username'),
				'password'           => $this->request_or_session('password'),
				'email_verification' => $this->get_customer_email_verification_status(),
				'meta'               => array(),
			);

			if ($this->is_existing_user()) {

				$customer_data = array(
					'email'              => wp_get_current_user()->user_email,
					'email_verification' => 'verified',
				);

			} // end if;

			/*
			 * Tries to create the customer
			 */
			$customer = wu_create_customer($customer_data);

			if (is_wp_error($customer)) {

				return $customer;

			} // end if;

			$billing_address = $customer->get_billing_address();

			$billing_address->attributes($_POST);

			$valid_address = $billing_address->validate();

			if (is_wp_error($valid_address)) {

				return $valid_address;

			} // end if;

			$customer->set_billing_address($billing_address);

			$customer->save();

		} // end if;

		$parent_payment = null;

		$membership_data = $this->order->to_membership_data();

		$membership_data['customer_id']   = $customer->get_id();
		$membership_data['user_id']       = $customer->get_user_id();
		$membership_data['signup_method'] = wu_request('signup_method');
		$membership_data['gateway']       = wu_request('gateway', '');

		// $this->create_cart();
		$membership = wu_create_membership($membership_data);

		if (is_wp_error($membership)) {

			return $membership;

		} // end if;

		$this->order->add_membership($membership->get_id(), $this->order->get_plan()->get_id());

		if ($this->order->get_plan()->get_type() === 'plan' && $created_site === false) {

			$d = wu_get_site_domain_and_path($this->request_or_session('site_url'));

			// Create pending site
			$pending_site = $membership->create_pending_site(array(
				'domain'        => $d->domain,
				'path'          => $d->path,
				'title'         => $this->request_or_session('site_title'),
				'template_id'   => $this->request_or_session('template_id'),
				'customer_id'   => $customer->get_id(),
				'membership_id' => $membership->get_id(),
				'type'          => Site_Type::CUSTOMER_OWNED,
				'transient'     => array_filter($this->session->get('signup'), function($item) {

					return strpos($item, 'password') === false;

				}, ARRAY_FILTER_USE_KEY),
			));

			/*
				* Set the flag to true. We just want to create one site.
				*/
			$created_site = true;

		} // end if;

		// Creates the pending payment

		$payment_data = $this->order->to_payment_data();

		$payment_data['customer_id']   = $customer->get_id();
		$payment_data['membership_id'] = $membership->get_id();
		$payment_data['gateway']       = wu_request('gateway', '');

		$payment = wu_create_payment($payment_data);

		if (is_wp_error($payment)) {

			return $payment;

		} // end if;

		/*
		* Set the first pending payment as the parent one.
		*
		* All the other products and fees will be added as line-items.
		*/
		if ($parent_payment === null) {

			$parent_payment = $payment;

		} // end if;

		/*
		 * Recalculate payment totals
		 */
		$parent_payment->recalculate_totals()->save();

		/*
		 * Saves the original cart
		 */
		$parent_payment->update_meta('original_cart', $this->order);

		/*
		 * Now we need to deal with processing the payment
		 */
		$gateway_payment_data = array(
			'customer'    => $customer,
			'payment'     => $parent_payment,
			'cart'        => $this->order,
			'memberships' => $this->order->get_memberships(),
		);

		$gateway->set_payment_data($gateway_payment_data);

		if (!is_user_logged_in()) {

			wp_set_auth_cookie($customer->get_user_id());

		} // end if;

		try {

			$success_data = array(
				'nonce'           => wp_create_nonce('wp-ultimo-register-nonce'),
				'total'           => $this->order->get_total(),
				'memberships'     => $this->order->get_memberships(),
				'recurring_total' => $this->order->get_recurring_total(),
				'auto_renew'      => true,
				'payment_id'      => $parent_payment->get_id(),
				'gateway'         => array(
					'slug'     => $gateway->get_id(),
					'supports' => $gateway->get_support(),
					'data'     => (object) array(),
				),
			);

			// Handle gateway ajax processing.
			if ('free' !== $gateway->get_id() && $gateway->supports('ajax-payment')) {

				// send all of the membership data off for processing by the gateway
				$result = $gateway->process_ajax_signup();

				$success_data['gateway']['data'] = (array) $result;

				if (is_wp_error($result)) {

					return $result;

				} // end if;

			} // end if;

		} catch (\Throwable $e) {

			// Handle error
			return new \WP_Error('exception', $e->getMessage(), $e->getTrace());

		} // end try;

		return $success_data;

	} // end process_order;

	/**
	 * Validates the checkout form to see if it's valid por not.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function validate_form() {

		$validation = $this->validate();

		if (is_wp_error($validation)) {

			wp_send_json_error($validation);

		} // end if;

		wp_send_json_success();

	} // end validate_form;

	/**
	 * Creates an order object to display the order summary tables.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function create_order() {

		$this->setup_checkout();

		$registration = new Cart(array(
			'products'      => $this->request_or_session('products', array()),
			'discount_code' => $this->request_or_session('discount_code'),
			'country'       => $this->request_or_session('country'),
		));

		wp_send_json_success(array(
			'order' => $registration,
		));

	} // end create_order;

	/**
	 * Adds the checkout scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		$custom_css = apply_filters('wu_checkout_custom_css', '');

		wp_add_inline_style('wu-checkout', $custom_css);

		wp_enqueue_style('wu-checkout');

		wp_enqueue_style('wu-admin');

		wp_register_script('wu-checkout', wu_get_asset('checkout.js', 'js'), array('jquery', 'wu-vue', 'wu-moment', 'wu-block-ui', 'wu-functions', 'password-strength-meter', 'underscore'), wu_get_version(), true);

		wp_localize_script('wu-checkout', 'wu_checkout', $this->get_checkout_variables());

		wp_enqueue_script('wu-checkout');

	} // end register_scripts;

	/**
	 * Returns the checkout variables.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_checkout_variables() {
		/*
		 * Localized strings.
		 */
		$i18n = array(
			'added_to_order' => __('The item was added!', 'wp-ultimo'),
		);

		/*
		 * Get the default gateway.
		 */
		$default_gateway = current(array_keys(wu_get_active_gateway_as_options()));

		/*
		 * General Checkout variables.
		 *
		 * These are used to setup the initial state of the checkout form.
		 */
		$variables = array(
			'ajaxurl'            => get_admin_url(1, 'admin-ajax.php'),
			'gateway'            => wu_request('gateway', $default_gateway),
			'plan'               => array_values($this->request_or_session('plan', array())),
			'products'           => array_values($this->request_or_session('products', array())),
			'country'            => $this->request_or_session('country'),
			'needs_billing_info' => true,
			'i18n'               => $i18n,
		);

		/**
		 * Allow plugin developers to filter the pre-sets of a checkout page.
		 *
		 * Be careful, missing keys can completely break the checkout
		 * on the front-end.
		 *
		 * @since 2.0.0
		 * @param array $variables Localized variables.
		 * @param \WP_Ultimo\Checkout\Checkout $this The checkout class.
		 * @return array The new variables array.
		 */
		return apply_filters('wu_get_checkout_variables', $variables, $this);

	} // end get_checkout_variables;

	/**
	 * Filters the password strength required by WordPress.
	 *
	 * @todo: make this work.
	 * @since 2.0.0
	 *
	 * @param int $strength The min number of characters a password must have.
	 * @return int
	 */
	public function filter_password_strength($strength) {

		return 6;

	} // end filter_password_strength;

	/**
	 * Returns the validation rules for the fields.
	 *
	 * @todo The fields needs to declare this themselves.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function validation_rules() {

		return array(
			'email_address' => 'required_without:user_id|email',
			'username'      => 'required_without:user_id|alpha_dash|min:4|lowercase',
			'password'      => 'required_without:user_id|min:6',
			'password_conf' => 'required_without:user_id|same:password',
			'site_title'    => 'required|min:4',
			'site_url'      => 'required|alpha_num|min:4|lowercase',
			'template_id'   => 'integer',
			'gateway'       => '',
		);

	} // end validation_rules;

	/**
	 * Checks if the user already exists.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_existing_user() {

		return is_user_logged_in();

	} // end is_existing_user;

	/**
	 * Renders the confirmation page.
	 *
	 * @since 2.0.0
	 *
	 * @param array       $atts Shortcode attributes.
	 * @param null|string $content The post content.
	 * @return string
	 */
	public function render_confirmation_page($atts, $content = null) {

		return wu_get_template_contents('checkout/confirmation', array(
			'errors'     => $this->errors,
			'membership' => wu_get_membership_by_hash(wu_request('membership')),
		));

	} // end render_confirmation_page;

	/**
	 * Returns the list of validation rules.
	 *
	 * If we are dealing with a step submission, we will return
	 * only the validation rules that refer to the keys sent via POST.
	 *
	 * If this is the submission of the last step, though, we return all
	 * validation rules so we can validate the entire signup.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_validation_rules() {

		$validation_rules = $this->validation_rules();

		if ($this->step_name && $this->is_last_step() === false) {

			$fields_available = array_column($this->step['fields'], 'id');

			$validation_rules = array_filter($validation_rules, function($rule) use ($fields_available) {

				return in_array($rule, $fields_available, true);

			}, ARRAY_FILTER_USE_KEY);

		} // end if;

		// We'll use this to validate product fields
		$product_fields = array(
			'pricing_table',
			'products',
		);

		/**
		 * Add the additional required fields.
		 */
		foreach ($this->step['fields'] as $field) {
			/*
			 * General required fields
			 */
			if (wu_get_isset($field, 'required') && wu_get_isset($field, 'id')) {

				$validation_rules[$field['id']] = 'required';

			} // end if;

			/*
			 * Product fields
			 */
			if (wu_get_isset($field, 'id') && in_array($field['id'], $product_fields, true)) {

				$validation_rules['products'] = 'required';

			} // end if;

		} // end foreach;

		return $validation_rules;

	} // end get_validation_rules;

	/**
	 * Validates the rules and make sure we only save models when necessary.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error
	 */
	public function validate() {

		$validator = new \WP_Ultimo\Helpers\Validator;

		$session = $this->session->get('signup');

		$stack = $_REQUEST;

		if (is_array($session)) {

			$stack = array_merge($session, $_REQUEST);

		} // end if;

		$rules = $this->get_validation_rules();

		$validator->validate($stack, $rules);

		if ($validator->fails()) {

			return $validator->get_errors();

		} // end if;

		return true;

	} // end validate;

	/**
	 * Needs to decide if we are simply putting the customer through the next step
	 * or if we need to actually process the checkout.
	 *
	 * 1. Checks of the current checkout is multi-step;
	 * -- If it is, process info, save into session and send to the next step.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_process_checkout() {

		$this->setup_checkout();

		if (!$this->should_process_checkout()) {

			return;

		} // end if;

		if ($this->is_last_step()) {
			/*
			 * We are in the last step and we can process the checkout normally.
			 */
			$this->process_checkout();

		} else {
			/*
			 * Cleans data and add it to the session.
			 */
			$to_save = array_filter($_POST, function($item) {

				return strpos($item, 'checkout_') !== 0 && strpos($item, '_') !== 0;

			}, ARRAY_FILTER_USE_KEY);

			$this->session->add_values('signup', $to_save);

			$this->session->commit();

			/*
			 * Go to the next step.
			 */

			$next_step = $this->get_next_step_name();

			wp_redirect(add_query_arg('step', $next_step));

			exit;

		} // end if;

	} // end maybe_process_checkout;

	/**
	 * Runs pre-checks to see if we should process the checkout.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function should_process_checkout() {

		// add  && wp_verify_nonce(wu_request('_wpnonce'), 'wu_checkout')
		return wu_request('checkout_action') === 'wu_checkout' && !wp_doing_ajax();

	} // end should_process_checkout;

	/**
	 * Gets the info either from the request or session.
	 *
	 * We try to get the key from the session object, but
	 * if that doesn't work or it doesn't exist, we try
	 * to get it from the request instead.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Key to retrieve the value for.
	 * @param mixed  $default The default value to return, when nothing is found.
	 * @return mixed
	 */
	public function request_or_session($key, $default = false) {

		$value = $default;

		if ($this->session !== null) {

			$session = $this->session->get('signup');

			if (isset($session[$key])) {

				$value = $session[$key];

			} // end if;

		} // end if;

		$value = wu_request($key, $value);

		return $value;

	} // end request_or_session;

	/**
	 * Handles the checkout submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_checkout() {

		global $wpdb;

		$this->setup_checkout();

		$gateway_id = wu_request('gateway');

		$gateway = wu_get_gateway($gateway_id);

		$this->order = new Cart(array(
			'products'          => $this->request_or_session('products', array()),
			'memberships'       => $this->request_or_session('memberships', array()),
			'discount_code'     => $this->request_or_session('discount_code'),
			'country'           => $this->request_or_session('country'),
			'registration_type' => 'new',
		));

		$gateway->set_payment_data(array(
			'return_url'  => $this->get_thank_you_page(),
			'customer'    => wu_get_current_customer(),
			'payment'     => wu_get_payment($this->request_or_session('payment_id')),
			'memberships' => $this->order->get_memberships(),
			'cart'        => $this->order,
		));

		if (!$gateway) {

			$this->errors = new \WP_Error('no-gateway', __('Payment gateway not registered.', 'wp-ultimo'));

			return;

		} // end if;

		try {

			$gateway->process_signup();

			$gateway->redirect();

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			return new \WP_Error('error', $e->getMessage());

		} // end try;

	} // end process_checkout;

	/**
	 * Returns the customer email verification status we want to use depending on the type of checkout.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_customer_email_verification_status() {

		return $this->order->has_trial() || $this->order->is_free() ? 'pending' : 'none';

	} // end get_customer_email_verification_status;

} // end class Checkout;
