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

use \WP_Ultimo\Database\Sites\Site_Type;
use \WP_Ultimo\Database\Payments\Payment_Status;
use \WP_Ultimo\Database\Memberships\Membership_Status;
use \WP_Ultimo\Checkout\Cart;
use \WP_Ultimo\Checkout\Checkout_Pages;

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

	/*
	 * Checkout progress info
	 */

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
	 * The current checkout form being used.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Models\Checkout_Form
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
	 * @var \WP_Ultimo\Session
	 */
	protected $session;

	/**
	 * Checkout type.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type = 'new';

	/**
	 * Initializes the Checkout singleton and adds hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		/*
		 * Setup and handle checkout
		 */
		add_action('wu_setup_checkout', array($this, 'setup_checkout'));

		add_action('wu_setup_checkout', array($this, 'maybe_process_checkout'), 20);

		/*
		 * Add the rewrite rules.
		 */
		add_action('init', array($this, 'add_rewrite_rules'), 20);

		add_filter('wu_request', array($this, 'get_checkout_from_query_vars'), 10, 2);

		/*
		 * Creates the order object to display to the customer
		 */
		add_action('wu_ajax_wu_create_order', array($this, 'create_order'));

		add_action('wu_ajax_nopriv_wu_create_order', array($this, 'create_order'));

		/*
		 * Validates form and process preflight.
		 */
		add_action('wu_ajax_wu_validate_form', array($this, 'maybe_handle_order_submission'));

		add_action('wu_ajax_nopriv_wu_validate_form', array($this, 'maybe_handle_order_submission'));

		/*
		 * Adds the necessary scripts
		 */
		add_action('wu_checkout_scripts', array($this, 'register_scripts'));

		/*
		 * Errors
		 */
		add_action('wu_checkout_errors', array($this, 'maybe_display_checkout_errors'));

	} // end init;

	/**
	 * Add checkout rewrite rules.
	 *
	 * Adds the following URL structures.
	 * For this example, let's use /register as the registration page.
	 *
	 * It registers:
	 * 1. site.com/register/plan_id:         Pre-selects the plan_id;
	 * 2. site.com/register/plan_id/3:       Pre-selects the plan_id and 3 months;
	 * 3. site.com/register/plan_id/12/year: Pre-selects the plan and the duration unit.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_rewrite_rules() {

		$register = Checkout_Pages::get_instance()->get_signup_page('register');

		if (!is_a($register, '\WP_Post')) {

			return;

		} // end if;

		$register_slug = $register->post_name;

		/*
		 * The first rewrite rule.
		 *
		 * This will match the registration URL and a plan
		 * slug.
		 *
		 * Example: site.com/register/premium
		 * Will pre-select the premium product.
		 */
		add_rewrite_rule(
			"{$register_slug}\/([0-9a-zA-Z-_]+)[\/]?$",
			'index.php?pagename=' . $register_slug . '&products[]=$matches[1]&wu_preselected=products',
			'top'
		);

		/*
		 * This one is here for backwards compatibility.
		 * It always assign to months.
		 */
		add_rewrite_rule(
			"{$register_slug}\/([0-9a-zA-Z-_]+)\/([0-9]+)[\/]?$",
			'index.php?pagename=' . $register_slug . '&products[]=$matches[1]&duration=$matches[2]&duration_unit=month&wu_preselected=products',
			'top'
		);

		/*
		 * This is the one we really want.
		 * It allows us to create custom registration URLs
		 * such as /register/premium/1/year
		 */
		add_rewrite_rule(
			"{$register_slug}\/([0-9a-zA-Z-_]+)\/([0-9]+)[\/]?([a-z]+)[\/]?$",
			'index.php?pagename=' . $register_slug . '&products[]=$matches[1]&duration=$matches[2]&duration_unit=$matches[3]&wu_preselected=products',
			'top'
		);

		/*
		 * By the default, the template selection
		 * URL structure uses the word template.
		 * This can be changed using the filter below.
		 */
		$template_slug = apply_filters('wu_template_selection_rewrite_rule_slug', 'template', $register_slug);

		/*
		 * Template site pre-selection.
		 * Allows for registration urls such as
		 * /register/template/starter
		 */
		add_rewrite_rule(
			"{$register_slug}\/{$template_slug}\/([0-9a-zA-Z-_]+)[\/]?$",
			'index.php?pagename=' . $register_slug . '&template_name=$matches[1]&wu_preselected=template_id',
			'top'
		);

	} // end add_rewrite_rules;

	/**
	 * Filters the wu_request with the query vars.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed  $value The value from wu_request.
	 * @param string $key The key value.
	 * @return mixed
	 */
	public function get_checkout_from_query_vars($value, $key) {

		if (!did_action('wp')) {

			return $value;

		} // end if;

		$from_query = get_query_var($key);

		$cart_arguments = apply_filters('wu_get_checkout_from_query_vars', array(
			'products',
			'duration',
			'duration_unit',
			'template_id',
			'wu_preselected',
		));

		/**
		 * Deal with site templates in a specific manner.
		 *
		 * @since 2.0.8
		 */
		if ($key === 'template_id') {

			$template_name = get_query_var('template_name', null);

			if ($template_name !== null) {

				$d = wu_get_site_domain_and_path($template_name);

				$wp_site = get_site_by_path($d->domain, $d->path);

				$site = $wp_site ? wu_get_site($wp_site->blog_id) : false;

				if ($site && $site->get_type() === Site_Type::SITE_TEMPLATE) {

					return $site->get_id();

				} // end if;

			} // end if;

		} // end if;

		/*
		 * Otherwise, simply check for its existence
		 * on the query object.
		 */
		if (in_array($key, $cart_arguments, true) && $from_query) {

			return $from_query;

		} // end if;

		return $value;

	} // end get_checkout_from_query_vars;

	/**
	 * Setups the necessary boilerplate code to have checkouts work.
	 *
	 * @since 2.0.0
	 * @param \WP_Ultimo\UI\Checkout_Element $element The checkout element.
	 * @return void
	 */
	public function setup_checkout($element = null) {

		$checkout_form_slug = wu_request('checkout_form');

		if (is_a($element, \WP_Ultimo\UI\Checkout_Element::class)) {

			$checkout_form_slug = $element->get_pre_loaded_attribute('slug');

		} // end if;

		$this->checkout_form = wu_get_checkout_form_by_slug($checkout_form_slug);

		if ($this->session === null) {

			$this->session = wu_get_session('signup');

		} // end if;

		if ($this->checkout_form) {

			$this->steps = $this->checkout_form->get_settings();

			$first_step = current($this->steps);

			$step_name = wu_request('checkout_step', wu_get_isset($first_step, 'id', 'checkout'));

			$this->step_name = $step_name;

			$this->step = $this->checkout_form->get_step($this->step_name);

			$this->auto_submittable_field = $this->contains_auto_submittable_field($this->step['fields']);

			$this->step['fields'] = wu_create_checkout_fields($this->step['fields']);

		} // end if;

		if (is_user_logged_in()) {

			$_REQUEST['user_id'] = get_current_user_id();

		} // end if;

		wu_no_cache(); // Prevent the registration page from being cached.

	} // end setup_checkout;

	/**
	 * Checks if a list of fields has an auto-submittable field.
	 *
	 * @since 2.0.4
	 *
	 * @param array $fields The list of fields of a step we need to check.
	 * @return false|string False if no auto-submittable field is present, the field to watch otherwise.
	 */
	public function contains_auto_submittable_field($fields) {

		$relevant_fields = array();

		$field_types_to_ignore = array(
			'hidden',
			'submit_button',
			'period_selection',
		);

		// Extra check to prevent error messages from being displayed.
		if (!is_array($fields)) {

			$fields = array();

		} // end if;

		foreach ($fields as $field) {

			if (in_array($field['type'], $field_types_to_ignore, true)) {

				continue;

			} // end if;

			$relevant_fields[] = $field;

			if (count($relevant_fields) > 1) {

				return false;

			} // end if;

		} // end foreach;

		$auto_submittable_field = $relevant_fields[0]['type'];

		return wu_get_isset($this->get_auto_submittable_fields(), $auto_submittable_field, false);

	} // end contains_auto_submittable_field;

	/**
	 * Returns a list of auto-submittable fields.
	 *
	 * @since 2.0.4
	 * @return array
	 */
	public function get_auto_submittable_fields() {

		/**
		 * They key should be the signup field ID to search for,
		 * while the value should be the parameter we should watch for changes
		 * so we can submit the form when we detect one.
		 */
		$auto_submittable_fields = array(
			'template_selection' => 'template_id',
			'pricing_table'      => 'products',
		);

		return apply_filters('wu_checkout_get_auto_submittable_fields', $auto_submittable_fields, $this);

	} // end get_auto_submittable_fields;

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

			wp_send_json_success(array());

		} // end if;

	} // end maybe_handle_order_submission;

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
			/*
			 * Allow developers to intercept an order submission.
			 */
			do_action('wu_before_handle_order_submission', $this);

			/*
			 * Here's where we actually process the order.
			 *
			 * Throwables are caught and they rollback
			 * any database writes sent up until this point.
			 *
			 * @see process_order below.
			 * @since 2.0.0
			 */
			$results = $this->process_order();

			/*
			 * Allow developers to change the results an order submission.
			 */
			do_action('wu_after_handle_order_submission', $results, $this);

			if (is_wp_error($results)) {

				$this->errors = $results;

			} // end if;

		} catch (\Throwable $e) {

			wu_maybe_log_error($e);

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
	 * Process an order.
	 *
	 * This method is responsible for
	 * creating all the data elements we
	 * need in order to actually process a
	 * checkout.
	 *
	 * Those include:
	 * - A customer;
	 * - A pending payment;
	 * - A membership.
	 *
	 * With those elements, we can then
	 * delegate to the gateway to run their
	 * own preparations (@see run_preflight).
	 *
	 * We then return everything to be added
	 * to the front-end form. That data then
	 * gets submitted with the rest of the form,
	 * and eventually handled by process_checkout.
	 *
	 * @see process_checkout
	 *
	 * @since 2.0.0
	 * @return array|\WP_Error
	 */
	public function process_order() {

		global $current_site, $wpdb;

		/*
		 * First, we start to work on the cart object.
		 * We need to take into consideration the date we receive from
		 * the form submission.
		 */
		$cart = new Cart(apply_filters('wu_cart_parameters', array(
			'products'      => $this->request_or_session('products', array()),
			'discount_code' => $this->request_or_session('discount_code'),
			'country'       => $this->request_or_session('billing_country'),
			'state'         => $this->request_or_session('billing_state'),
			'city'          => $this->request_or_session('billing_city'),
			'membership_id' => $this->request_or_session('membership_id'),
			'payment_id'    => $this->request_or_session('payment_id'),
			'auto_renew'    => $this->request_or_session('auto_renew', false),
			'duration'      => $this->request_or_session('duration'),
			'duration_unit' => $this->request_or_session('duration_unit'),
			'cart_type'     => $this->request_or_session('cart_type', 'new'),
		), $this));

		/*
		 * Check if our order is valid.
		 *
		 * The is valid method checks for
		 * cart setup issues, as well as
		 */
		if ($cart->is_valid() === false) {

			return $cart->get_errors();

		} // end if;

		/*
		 * Update the checkout type
		 * based on the cart type we have on hand.
		 */
		$this->type = $cart->get_cart_type();

		/*
		 * Gets the gateway object we want to use.
		 *
		 * This will have been set on a previous step (session)
		 * or is going to be passed via the form (request)
		 */
		$gateway_id = $this->request_or_session('gateway');
		$gateway    = wu_get_gateway($gateway_id);

		/*
			* We need to handle free payments separately.
			*
			* In the same manner, if the order
			* IS NOT free, we need to make sure
			* the customer is not trying to game the system
			* passing the free gateway to get an free account.
			*
			* That's what's we checking on the else case.
			*/
		if ($cart->should_collect_payment() === false) {

			$gateway = wu_get_gateway('free');

		} else {

			if (!$gateway || $gateway->get_id() === 'free') {

				$this->errors = new \WP_Error('no-gateway', __('Payment gateway not registered.', 'wp-ultimo'));

				return false;

			} // end if;

		} // end if;

		/*
		 * If we do not have a gateway object,
		 * we need to bail.
		 */
		if (!$gateway) {

			return new \WP_Error('no-gateway', __('Payment gateway not registered.', 'wp-ultimo'));

		} // end if;

		$this->gateway_id = $gateway->get_id();

		/*
		 * Now we need to validate the form.
		 *
		 * Here we use the validation rules set.
		 * @see validation_rules
		 */
		$validation = $this->validate();

		/*
		 * Bail on error.
		 */
		if (is_wp_error($validation)) {

			return $validation;

		} // end if;

		/*
		 * From now on, logic can be delegated to
		 * special methods, so we need to set
		 * the order as globally accessible.
		 */
		$this->order = $cart;

		/*
		 * Handles display names, if needed.
		 */
		add_filter('pre_user_display_name', array($this, 'handle_display_name'));

		/*
		 * If we get to this point, most of the validations are done.
		 * Now, we will actually begin to create new data elements
		 * if necessary.
		 *
		 * First, we need to check for a customer.
		 */
		$this->customer = $this->maybe_create_customer();

		/*
		 * We encountered errors while trying to create
		 * a new customer or retrieve an existing one.
		 */
		if (is_wp_error($this->customer)) {

			return $this->customer;

		} // end if;

		/*
		 * Next, we need to create a membership.
		 *
		 * The cart object has a couple of handy methods
		 * that allow us to easily convert from it
		 * to an array of data that we can use
		 * to create a membership.
		 */
		$this->membership = $this->maybe_create_membership();

		/*
		 * We encountered errors while trying to create
		 * a new membership or retrieve an existing one.
		 */
		if (is_wp_error($this->membership)) {

			return $this->membership;

		} // end if;

		/*
		 * Next step: maybe create a site.
		 *
		 * Depending on the status of the cart,
		 * we might need to create a pending site to
		 * attach to the membership.
		 */
		$this->pending_site = $this->maybe_create_site();

		/*
		 * It's not really possible to get a wp error
		 * in here for now but completeness dictates I add this.
		 */
		if (is_wp_error($this->pending_site)) {

			return $this->pending_site;

		} // end if;

		/*
		 * Next, we need to create a payment.
		 *
		 * The cart object has a couple of handy methods
		 * that allow us to easily convert from it
		 * to an array of data that we can use
		 * to create a payment.
		 */
		$this->payment = $this->maybe_create_payment();

		/*
		 * We encountered errors while trying to create
		 * a new payment or retrieve an existing one.
		 */
		if (is_wp_error($this->payment)) {

			return $this->payment;

		} // end if;

		/*
		 * Hey champs!
		 *
		 * If we are here, we have almost everything we
		 * need. Now is time to prepare things to hand
		 * over to the gateway.
		 */
		$this->order->set_customer($this->customer);
		$this->order->set_membership($this->membership);
		$this->order->set_payment($this->payment);

		$gateway->set_order($this->order);

		/*
		 * Before we move on,
		 * let's check if the user is logged in,
		 * and if not, let's do that.
		 */
		if (!is_user_logged_in()) {

			wp_clear_auth_cookie();

			wp_set_current_user($this->customer->get_user_id());

			wp_set_auth_cookie($this->customer->get_user_id());

		} // end if;

		/*
		 * Action time.
		 *
		 * Here's where we actually call the gateway
		 * and build the success data we want to return to the
		 * front-end form.
		 */
		try {
			/*
			 * Checks for free memberships.
			 */
			if ($this->order->is_free() && (!wu_get_setting('enable_email_verification', true) || $this->customer->get_email_verification() !== 'pending')) {

				$this->membership->set_status(Membership_Status::ACTIVE);

				$this->membership->save();

				/**
				 * Trigger payment received manually.
				 *
				 * @since 2.0.10
				 */
				$gateway->trigger_payment_processed($this->payment, $this->membership);

			} elseif ($this->order->has_trial()) {

				$this->membership->set_status(Membership_Status::TRIALING);

				$this->membership->set_date_trial_end(gmdate('Y-m-d 23:59:59', $this->order->get_billing_start_date()));
				$this->membership->set_date_expiration(gmdate('Y-m-d 23:59:59', $this->order->get_billing_start_date()));

				$this->membership->save();

				/**
				 * Trigger payment received manually.
				 *
				 * @since 2.0.10
				 */
				$gateway->trigger_payment_processed($this->payment, $this->membership);

				if (!wu_get_setting('enable_email_verification', true)) {
					/*
					 * In the case of a trial, we need to automatically publish the pending site as well.
					 */
					$this->membership->publish_pending_site_async();

				} // end if;

			} // end if;

			$success_data = array(
				'nonce'           => wp_create_nonce('wp-ultimo-register-nonce'),
				'customer'        => $this->customer->to_search_results(),
				'total'           => $this->order->get_total(),
				'recurring_total' => $this->order->get_recurring_total(),
				'membership_id'   => $this->membership->get_id(),
				'payment_id'      => $this->payment->get_id(),
				'cart_type'       => $this->order->get_cart_type(),
				'auto_renew'      => $this->order->should_auto_renew(),
				'gateway'         => array(
					'slug' => $gateway->get_id(),
					'data' => array(),
				),
			);

			/*
			 * Let's the gateway do its thing.
			 *
			 * Here gateways will run pre-flight code
			 * such as setting up payment intents and other
			 * important things that we need to be able to finish
			 * the process.
			 */
			$result = $gateway->run_preflight();

			/*
			 * Attach the gateway results to the return array.
			 */
			$success_data['gateway']['data'] = $result && is_array($result) ? $result : array();

			/*
			 * On error, bail.
			 */
			if (is_wp_error($result)) {

				return $result;

			} // end if;

		} catch (\Throwable $e) {

			wu_maybe_log_error($e);

			return new \WP_Error('exception', $e->getMessage(), $e->getTrace());

		} // end try;

		/**
		 * Allow developers to triggers additional hooks.
		 *
		 * @since 2.0.9
		 *
		 * @param \WP_Ultimo\Checkout\Checkout $checkout The checkout object instance.
		 * @param \WP_Ultimo\Checkout\Cart $cart The checkout cart instance.
		 * @return void
		 */
		do_action('wu_checkout_after_process_order', $this, $this->order);

		/*
		 * All set!
		 */
		return $success_data;

	} // end process_order;

	/**
	 * Checks if a customer exists, otherwise, creates a new one.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Customer|\WP_Error
	 */
	protected function maybe_create_customer() {
		/*
		 * Check if we have
		 * a customer for the current user.
		 */
		$customer = wu_get_current_customer();

		/*
		 * Get the form slug to save with the customer.
		 */
		$form_slug = $this->checkout_form ? $this->checkout_form->get_slug() : 'none';

		/*
		 * We don't have one,
		 * so we'll need to create it.
		 *
		 * We can't return early because we need
		 * to set and validate the billing address,
		 * and that happens at the end of this method.
		 */
		if (empty($customer)) {

			$username = $this->request_or_session('username');

			/*
			 * Handles auto-generation based on the email address.
			 */
			if ($this->request_or_session('auto_generate_username') === 'email') {

				$username = wu_username_from_email($this->request_or_session('email_address'));

				/*
				 * Case where the site title is also auto-generated, based on the username.
				 */
				if ($this->request_or_session('auto_generate_site_title') && $this->request_or_session('site_title', '') === '') {

					$_REQUEST['site_title'] = $username;

				} // end if;

			} // end if;

			/*
			 * If we get to this point,
			 * we don't have an existing customer.
			 *
			 * Next step then would be to create one.
			 */
			$customer_data = array(
				'username'           => $username,
				'email'              => $this->request_or_session('email_address'),
				'password'           => $this->request_or_session('password'),
				'email_verification' => $this->get_customer_email_verification_status(),
				'signup_form'        => $form_slug,
				'meta'               => array(),
			);

			/*
			 * If the user is logged in,
			 * we use the existing email address to create the customer.
			 */
			if ($this->is_existing_user()) {

				$customer_data = array(
					'email'              => wp_get_current_user()->user_email,
					'email_verification' => 'verified',
				);

			} // end if;

			/*
			 * Tries to create it.
			 */
			$customer = wu_create_customer($customer_data);

			/*
			 * Something failed, bail.
			 */
			if (is_wp_error($customer)) {

				return $customer;

			} // end if;

		} // end if;

		/*
		 * Updates IP, and country
		 */
		$customer->update_last_login(true, true);

		/*
		 * Next, we need to validate the billing address,
		 * and save it.
		 */
		$billing_address = $customer->get_billing_address();

		/*
		 * I know this appears super unsafe,
		 * but we clean the data up on the billing address
		 * class, so there's no problem in passing
		 * the entire post array in here.
		 */
		$session = $this->session->get('signup') ?? array();
		$billing_address->attributes(array_merge($session, $_POST));

		/*
		 * Validates the address.
		 */
		$valid_address = $billing_address->validate();

		/*
		 * There's something invalid on the address,
		 * bail with the errors.
		 */
		if (is_wp_error($valid_address)) {

			return $valid_address;

		} // end if;

		$customer->set_billing_address($billing_address);

		$address_saved = $customer->save();

		/*
		 * This should rarely happen, but if something goes
		 * wrong with the customer update, we return a general error.
		 */
		if (!$address_saved) {

			return new \WP_Error('address_failure', __('Something wrong happened while attempting to save the customer billing address', 'wp-ultimo'));

		} // end if;

		/*
		 * Handle meta fields.
		 *
		 * Gets all the meta fields for customers and
		 * save them to the customer as meta.
		 */
		$this->handle_customer_meta_fields($customer, $form_slug);

		/**
		 * Allow plugin developers to do additional stuff when the customer
		 * is added.
		 *
		 * Here's where we add the hooks for adding the customer->user to
		 * the main site as well, for example.
		 *
		 * @since 2.0.0
		 * @param Customer $customer The customer that was maybe created.
		 * @param Checkout $this     The current checkout class.
		 */
		do_action('wu_maybe_create_customer', $customer, $this);

		/*
		 * Otherwise, get the customer back.
		 */
		return $customer;

	} // end maybe_create_customer;

	/**
	 * Save meta data related to customers.
	 *
	 * @since 2.0.0
	 *
	 * @param Customer $customer The created customer.
	 * @param string   $form_slug The form slug.
	 * @return void
	 */
	protected function handle_customer_meta_fields($customer, $form_slug) {

		if (empty($form_slug) || $form_slug === 'none') {

			return;

		} // end if;

		$checkout_form = wu_get_checkout_form_by_slug($form_slug);

		if ($checkout_form) {

			$customer_meta_fields = $checkout_form->get_all_meta_fields('customer_meta');

			$meta_repository = array();

			foreach ($customer_meta_fields as $customer_meta_field) {
				/*
				 * Adds to the repository so we can save it again.
				 * in filters, if we need be.
				 */
				$meta_repository[$customer_meta_field['id']] = $this->request_or_session($customer_meta_field['id']);

				wu_update_customer_meta(
					$customer->get_id(),
					$customer_meta_field['id'],
					$this->request_or_session($customer_meta_field['id']),
					$customer_meta_field['type'],
					$customer_meta_field['name']
				);

			} // end foreach;

			/**
			 * Allow plugin developers to save meta
			 * data in different ways if they need to.
			 *
			 * @since 2.0.0
			 * @param array $meta_repository The list of meta fields, key => value structured.
			 * @param Customer $customer The WP Ultimo customer object.
			 * @param Checkout $this The checkout class.
			 */
			do_action('wu_handle_customer_meta_fields', $meta_repository, $customer, $this);

			/**
			 * Do basically the same thing, now for user meta.
			 *
			 * @since 2.0.4
			 */
			$user_meta_fields = $checkout_form->get_all_meta_fields('user_meta');

			$user = $customer->get_user();

			$user_meta_repository = array();

			foreach ($user_meta_fields as $user_meta_field) {
				/*
				 * Adds to the repository so we can save it again.
				 * in filters, if we need be.
				 */
				$user_meta_repository[$user_meta_field['id']] = $this->request_or_session($user_meta_field['id']);

				update_user_meta($customer->get_user_id(), $user_meta_field['id'], $this->request_or_session($user_meta_field['id']));

			} // end foreach;

			/**
			 * Allow plugin developers to save user meta
			 * data in different ways if they need to.
			 *
			 * @since 2.0.4
			 * @param array $meta_repository The list of meta fields, key => value structured.
			 * @param \WP_User $user The WordPress user object.
			 * @param Customer $customer The WP Ultimo customer object.
			 * @param Checkout $this The checkout class.
			 */
			do_action('wu_handle_user_meta_fields', $user_meta_repository, $user, $customer, $this);

		} // end if;

	} // end handle_customer_meta_fields;

	/**
	 * Checks if a membership exists, otherwise, creates a new one.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Membership|\WP_Error
	 */
	protected function maybe_create_membership() {
		/*
		 * The first thing we'll do is check the cart
		 * to see if a membership was passed.
		 */
		if ($this->order->get_membership()) {

			return $this->order->get_membership();

		} // end if;

		/*
		 * If that's not the case,
		 * we'll need to create a new one.
		 *
		 * The cart object has a couple of handy methods
		 * that allow us to easily convert from it
		 * to an array of data that we can use
		 * to create a membership.
		 */
		$membership_data = $this->order->to_membership_data();

		/*
		 * Append additional data to the membership.
		 */
		$membership_data['customer_id']   = $this->customer->get_id();
		$membership_data['user_id']       = $this->customer->get_user_id();
		$membership_data['gateway']       = $this->gateway_id;
		$membership_data['signup_method'] = wu_request('signup_method');

		/*
		 * Important dates.
		 */
		$membership_data['date_expiration'] = $this->order->get_billing_start_date();

		$membership = wu_create_membership($membership_data);

		return $membership;

	} // end maybe_create_membership;

	/**
	 * Checks if a pending site exists, otherwise, creates a new one.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Site|\WP_Error
	 */
	protected function maybe_create_site() {
		/*
		 * Let's get a list of membership sites.
		 * This list includes pending sites as well.
		 */
		$sites = $this->membership->get_sites();

		/*
		 * Decide if we should create a new site or not.
		 *
		 * When should we create a new pending site?
		 * There are a couple of rules:
		 * - The membership must not have a pending site;
		 * - The membership must not have an existing site;
		 *
		 * The get_sites method already includes pending sites,
		 * so we can safely rely on it.
		 */
		if (!empty($sites)) {
			/*
			 * Returns the first site on that list.
			 * This is not ideal, but since we'll usually only have
			 * one site here, it's ok. for now.
			 */
			return current($sites);

		} // end if;

		$site_url = $this->request_or_session('site_url');

		/*
		 * Let's handle auto-generation of site URLs.
		 *
		 * To decide if we need to auto-generate the site URL,
		 * we'll check the request for the auto_generate_site_url = username request value.
		 *
		 * If that's present and no site_url is present, then we need to auto-generate this.
		 * The strategy here is simple, we basically set the site_url to the username.
		 */
		if ($this->request_or_session('auto_generate_site_url') === 'username') {

			$site_url = $this->customer->get_username();

		} // end if;

		$d = wu_get_site_domain_and_path($site_url, $this->request_or_session('site_domain'));

		/*
		 * Validates the site url.
		 */
		$results = wpmu_validate_blog_signup($site_url, $this->request_or_session('site_title'), $this->customer->get_user());

		if ($results['errors']->has_errors()) {

			return $results['errors'];

		} // end if;

		/*
		 * Get the form slug to save with the customer.
		 */
		$form_slug = $this->checkout_form ? $this->checkout_form->get_slug() : 'none';

		/*
		 * Get the transient data to save with the site
		 * that way we can use it when actually registering
		 * the site on WordPress.
		 */
		$transient = $this->session->get('signup');

		$transient = is_array($transient) ? $transient : array();

		if ($this->checkout_form) {

			$transient = array_merge($transient, $this->get_site_meta_fields($form_slug, 'site_meta'));

			$transient = array_merge($transient, $this->get_site_meta_fields($form_slug, 'site_option'));

		} // end if;

		/*
		 * Removes password fields from transient data,
		 * to make sure plain passwords do not get stored
		 * on the database.
		 */
		$transient = array_filter($transient, function($item) {

			return strpos($item, 'password') === false;

		}, ARRAY_FILTER_USE_KEY);

		/*
		 * Gets the template id from the request.
		 * Here, there's some logic we need to do to
		 * try to get the template id if we get a
		 * template name instead of a number.
		 *
		 * This logic is handled inside the
		 * get_checkout_from_query_vars() method.
		 *
		 * @see get_checkout_from_query_vars()
		 */
		$template_id = apply_filters('wu_checkout_template_id', (int) $this->request_or_session('template_id'), $this->membership, $this);

		$site_data = array(
			'domain'         => $d->domain,
			'path'           => $d->path,
			'title'          => $this->request_or_session('site_title'),
			'template_id'    => $template_id,
			'customer_id'    => $this->customer->get_id(),
			'membership_id'  => $this->membership->get_id(),
			'transient'      => $transient,
			'signup_options' => $this->get_site_meta_fields($form_slug, 'site_option'),
			'signup_meta'    => $this->get_site_meta_fields($form_slug, 'site_meta'),
			'type'           => Site_Type::CUSTOMER_OWNED,
		);

		$pending_site = $this->membership->create_pending_site($site_data);

		return $pending_site;

	} // end maybe_create_site;

	/**
	 * Gets list of site meta data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $form_slug The form slug.
	 * @param string $meta_type The meta type. Can be site_meta or site_option.
	 * @return array
	 */
	protected function get_site_meta_fields($form_slug, $meta_type = 'site_meta') {

		if (empty($form_slug) || $form_slug === 'none') {

			return array();

		} // end if;

		$checkout_form = wu_get_checkout_form_by_slug($form_slug);

		$list = array();

		if ($checkout_form) {

			$site_meta_fields = $checkout_form->get_all_meta_fields($meta_type);

			foreach ($site_meta_fields as $site_meta_field) {

				$list[$site_meta_field['id']] = $this->request_or_session($site_meta_field['id']);

			} // end foreach;

		} // end if;

		return $list;

	} // end get_site_meta_fields;

	/**
	 * Checks if a pending payment exists, otherwise, creates a new one.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Payment|\WP_Error
	 */
	protected function maybe_create_payment() {
		/*
		 * The first thing we'll do is check the cart
		 * to see if a payment was passed.
		 */
		if ($this->order->get_payment()) {

			return $this->order->get_payment();

		} // end if;

		/*
		 * The membership might have a previous payment.
		 * We'll go ahead and cancel that one out in cases
		 * of a upgrade/downgrade or add-on.
		 */
		$previous_payment = $this->membership->get_last_pending_payment();

		$cancel_types = array(
			'upgrade',
			'downgrade',
			'addon',
		);

		if ($previous_payment && in_array($this->type, $cancel_types, true)) {

			$previous_payment->set_status(Payment_Status::CANCELLED);

			/*
			 * This can actually return a wp_error,
			 * but to be honest, we don't really care if we
			 * were able to cancel the previous payment or not.
			 */
			$previous_payment->save();

		} // end if;

		/*
		 * If that's not the case,
		 * we'll need to create a new one.
		 *
		 * The cart object has a couple of handy methods
		 * that allow us to easily convert from it
		 * to an array of data that we can use
		 * to create a payment.
		 */
		$payment_data = $this->order->to_payment_data();

		/*
		 * Append additional data to the payment.
		 */
		$payment_data['customer_id']   = $this->customer->get_id();
		$payment_data['membership_id'] = $this->membership->get_id();
		$payment_data['gateway']       = $this->gateway_id;

		/*
		 * Save the original cart for later reference.
		 * We do this on the meta table.
		 */
		$payment_data['meta'] = array(
			'wu_original_cart' => $this->order,
		);

		/*
		 * If this is a free order,
		 * we can set the payment status to completed.
		 */
		if (!$this->order->should_collect_payment()) {

			$payment_data['status'] = Payment_Status::COMPLETED;

		} // end if;

		/*
		 * Create new payment.
		 */
		$payment = wu_create_payment($payment_data);

		/*
		 * Then, if this is a trial,
		 * we need to set the payment value to zero.
		 */
		if ($this->order->has_trial()) {

			$payment->attributes(array(
				'tax_total'    => 0,
				'subtotal'     => 0,
				'refund_total' => 0,
				'total'        => 0,
			));

			$payment->save();

		} // end if;

		return $payment;

	} // end maybe_create_payment;

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

		// Set billing address to be used on the order
		$country = !empty($this->request_or_session('country')) ? $this->request_or_session('country') : $this->request_or_session('billing_country', '');
		$state   = !empty($this->request_or_session('state')) ? $this->request_or_session('state') : $this->request_or_session('billing_state', '');
		$city    = !empty($this->request_or_session('city')) ? $this->request_or_session('city') : $this->request_or_session('billing_city', '');

		$cart = new Cart(apply_filters('wu_cart_parameters', array(
			'products'      => $this->request_or_session('products', array()),
			'discount_code' => $this->request_or_session('discount_code'),
			'country'       => $country,
			'state'         => $state,
			'city'          => $city,
			'membership_id' => $this->request_or_session('membership_id'),
			'payment_id'    => $this->request_or_session('payment_id'),
			'auto_renew'    => $this->request_or_session('auto_renew', false),
			'duration'      => $this->request_or_session('duration'),
			'duration_unit' => $this->request_or_session('duration_unit'),
			'cart_type'     => $this->request_or_session('cart_type', 'new'),
		), $this));

		/**
		 * Calculate state and city options, if necessary.
		 *
		 * @since 2.0.11
		 */
		$country_data = wu_get_country($cart->get_country());

		wp_send_json_success(array(
			'order'  => $cart,
			'states' => wu_key_map_to_array($country_data->get_states_as_options(), 'code', 'name'),
			'cities' => wu_key_map_to_array($country_data->get_cities_as_options($state), 'code', 'name'),
			'labels' => array(
				'state_field' => $country_data->get_administrative_division_name(null, true),
				'city_field'  => $country_data->get_municipality_name(null, true),
			),
		));

	} // end create_order;

	/**
	 * Returns the checkout variables.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_checkout_variables() {

		global $current_site;

		/*
		 * Localized strings.
		 */
		$i18n = array(
			'loading'        => __('Loading...', 'wp-ultimo'),
			'added_to_order' => __('The item was added!', 'wp-ultimo'),
			'weak_password'  => __('The Password entered is too weak.', 'wp-ultimo'),
		);

		/*
		 * Get the default gateway.
		 */
		$default_gateway = current(array_keys(wu_get_active_gateway_as_options()));

		$d = wu_get_site_domain_and_path('replace');

		$site_domain = str_replace('replace.', '', $d->domain);

		$duration = $this->request_or_session('duration');
		$duration_unit = $this->request_or_session('duration_unit');

		// If duration is not set we check for a previous period_selection field in form to use;
		if (empty($duration) && $this->steps) {

			foreach ($this->steps as $step) {

				foreach ($step['fields'] as $field) {

					if ($field['type'] === 'period_selection') {

						$duration = $field['period_options'][0]['duration'];
						$duration_unit = $field['period_options'][0]['duration_unit'];

						break;

					} // end if;

				} // end foreach;

				if ($step['id'] !== $this->step['id']) {

					break;

				} // end if;

			} // end foreach;

		} // end if;

		/*
		 * Set the default variables.
		 */
		$variables = array(
			'i18n'               => $i18n,
			'ajaxurl'            => wu_ajax_url(),
			'late_ajaxurl'       => wu_ajax_url('init'),
			'baseurl'            => remove_query_arg('pre-flight', wu_get_current_url()),

			'country'            => $this->request_or_session('billing_country'),
			'city'               => $this->request_or_session('billing_city'),
			'state'              => $this->request_or_session('billing_state'),
			'duration'           => $duration,
			'duration_unit'      => $duration_unit,

			'site_url'           => $this->request_or_session('site_url'),
			'site_domain'        => $this->request_or_session('site_domain', preg_replace('#^https?://#', '', $site_domain)),
			'is_subdomain'       => is_subdomain_install(),

			'gateway'            => wu_request('gateway', $default_gateway),

			'needs_billing_info' => true,
			'auto_renew'         => true,
			'products'           => array(),
		);

		/*
		 * There's a couple of things we need to determine.
		 *
		 * First, we need to check for a payment parameter.
		 */
		$payment_hash = wu_request('payment');

		/*
		 * If a hash exists, we need to retrieve the ID.
		 */
		$payment    = wu_get_payment_by_hash($payment_hash);
		$payment_id = $payment ? $payment->get_id() : 0;

		/*
		 * With the payment id in hand, we can
		 * we do not pass the products, as this is
		 * a retry.
		 */
		if ($payment_id) {

			$variables['payment_id'] = $payment_id;

		} // end if;

		/*
		 * The next case we need to take care of
		 * are addons, upgrades and downgrades.
		 *
		 * Those occur when we have a membership hash present
		 * and additional products, including or not a plan.
		 */
		$membership_hash = wu_request('membership');

		/*
		 * If a hash exists, we need to retrieve the ID.
		 */
		$membership    = wu_get_membership_by_hash($membership_hash);
		$membership_id = $membership ? $membership->get_id() : 0;

		/*
		 * With the membership id in hand, we can
		 * we do not pass the products, as this is
		 * a retry.
		 */
		if ($membership_id) {

			$variables['membership_id'] = $membership_id;

		} // end if;

		$variables['products'] = $this->request_or_session('products', array());

		list($plan, $other_products) = wu_segregate_products($variables['products']);

		$variables['plan'] = $plan ? $plan->get_id() : 0;

		/*
		 * Try to fetch the template_id
		 */
		$variables['template_id'] = $this->request_or_session('template_id', 0);

		/*
		 * Let's also create a cart object,
		 * so we can pre-configure the form on the front-end
		 * accordingly.
		 */
		$variables['order'] = new Cart($variables);

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
	 * Returns the validation rules for the fields.
	 *
	 * @todo The fields needs to declare this themselves.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function validation_rules() {
		/*
		 * Validations rules change
		 * depending on the type of order.
		 *
		 * For example, the only type that
		 * requires the site fields
		 * are the 'new'.
		 *
		 * First, let's set upm the general rules:
		 */
		$rules = array(
			'email_address'    => 'required_without:user_id|email',
			'username'         => 'required_without:user_id|alpha_dash|min:4|lowercase',
			'password'         => 'required_without:user_id|min:6',
			'password_conf'    => 'same:password',
			'template_id'      => 'integer|site_template',
			'products'         => 'products',
			'gateway'          => '',
			'valid_password'   => 'accepted',
			'billing_country'  => 'country|required_with:billing_country',
			'billing_zip_code' => 'required_with:billing_zip_code',
			'billing_state'    => 'state',
			'billing_city'     => 'city',
		);

		/*
		 * Add rules for site when creating a new account.
		 */
		if ($this->type === 'new') {

			$rules['site_title'] = 'min:4';
			$rules['site_url']   = 'required|lowercase|unique_site';

		} // end if;

		return apply_filters('wu_checkout_validation_rules', $rules, $this);

	} // end validation_rules;

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

			/*
			 * Re-adds the template id check
			 */
			if (wu_request('template_id', null) !== null) {

				$fields_available[] = 'template_id';

			} // end if;

			$validation_rules = array_filter($validation_rules, function($rule) use ($fields_available) {

				return in_array($rule, $fields_available, true);

			}, ARRAY_FILTER_USE_KEY);

		} elseif (wu_request('pre-flight')) {

			$validation_rules = array();

			// $validation_rules['products'] = 'required';

			return $validation_rules;

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

				if (isset($validation_rules[$field['id']])) {

					$validation_rules[$field['id']] .= '|required';

				} else {

					$validation_rules[$field['id']] = 'required';

				} // end if;

			} // end if;

			/*
			 * Product fields
			 */
			if (wu_get_isset($field, 'id') && in_array($field['id'], $product_fields, true)) {

				$validation_rules['products'] = 'products|required';

			} // end if;

		} // end foreach;

		return $validation_rules;

	} // end get_validation_rules;

	/**
	 * Validates the rules and make sure we only save models when necessary.
	 *
	 * @since 2.0.0
	 * @param array $rules Custom rules to use instead of the default ones.
	 * @return true|\WP_Error
	 */
	public function validate($rules = null) {

		$validator = new \WP_Ultimo\Helpers\Validator;

		$session = $this->session->get('signup');

		$stack = $_REQUEST;

		if (is_array($session)) {

			$stack = array_merge($session, $_REQUEST);

		} // end if;

		if ($rules === null) {

			$rules = $this->get_validation_rules();

		} // end if;

		$validator->validate($stack, $rules);

		if ($validator->fails()) {

			$errors = $validator->get_errors();

			$errors->remove('valid_password');

			return $errors;

		} // end if;

		return true;

	} // end validate;

	/**
	 * Decides if we are to process a checkout.
	 *
	 * Needs to decide if we are simply putting the customer through the next step
	 * or if we need to actually process the checkout.
	 * It checks of the current checkout is multi-step;
	 * If it is, process info, save into session and send to the next step.
	 * Otherwise, we process the checkout.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_process_checkout() {
		/*
		 * Sets up the checkout
		 * environment.
		 */
		$this->setup_checkout();

		/*
		 * Checks if we should be here.
		 * We can only process a checkout
		 * if certain conditions are met.
		 */
		if (!$this->should_process_checkout()) {

			return;

		} // end if;

		/*
		 * Checks if we are in the last step.
		 *
		 * WP Ultimo supports multi-step checkout
		 * flows. That means that we do different
		 * things on the intermediary steps (mostly
		 * add things to the session) and on the final,
		 * where we process the checkout.
		 *
		 * Let's deal with the last step case first.
		 */
		if ($this->is_last_step()) {
			/*
			 * We are in the last step and
			 * we can process the checkout normally.
			 */
			$results = $this->process_checkout();

			/*
			 * Error!
			 *
			 * We redirect the customer back to the
			 * checkout page, passing the payment query
			 * arg so the customer can try again.
			 */
			if (is_wp_error($results)) {

				$redirect_url = wu_get_current_url();

				$this->session->set('errors', $results);

				/*
				 * We attach the payment data
				 * to the error, so we can retrieve it here.
				 */
				$payment = wu_get_isset($results->get_error_data(), 'payment');

				/*
				 * If the payment exists,
				 * use the hash to redirect the customer
				 * to a try again page.
				 */
				if ($payment) {

					$redirect_url = add_query_arg(array(
						'payment' => $payment->get_hash(),
						'status'  => 'error',
					), $redirect_url);

				} // end if;

				/*
				 * Redirect go burrr!
				 */
				wp_redirect($redirect_url);

				exit;

			} // end if;

		/*
		 * This is not the final step,
		 * so we just clean the data and save it
		 * for later.
		 */
		} else {
			/*
			 * Cleans data and add it to the session.
			 *
			 * Here we remove the items that either
			 * have checkout_ on their name, or start
			 * with a underscore.
			 */
			$to_save = array_filter($_POST, function($item) {

				return strpos($item, 'checkout_') !== 0 && strpos($item, '_') !== 0;

			}, ARRAY_FILTER_USE_KEY);

			/*
			 * Append the cleaned date to the
			 * active session.
			 */
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

		return wu_request('checkout_action') === 'wu_checkout' && !wp_doing_ajax();

	} // end should_process_checkout;

	/**
	 * Handles the checkout submission.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function process_checkout() {

		/**
		 * Before we process the checkout.
		 *
		 * @since 2.0.11
		 * @param \WP_Ultimo\Checkout\Checkout $checkout The current checkout instance;
		 */
		do_action('wu_checkout_before_process_checkout', $this);

		$this->setup_checkout();

		$gateway = wu_get_gateway(wu_request('gateway'));

		try {
			/*
			 * Creates the order.
			 */
			$this->order = new Cart(array(
				'products'      => $this->request_or_session('products', array()),
				'membership_id' => $this->request_or_session('membership_id'),
				'payment_id'    => $this->request_or_session('payment_id'),
				'discount_code' => $this->request_or_session('discount_code'),
				'country'       => $this->request_or_session('billing_country'),
				'state'         => $this->request_or_session('billing_state'),
				'city'          => $this->request_or_session('billing_city'),
				'currency'      => $this->request_or_session('currency', wu_get_setting('currency_symbol', 'USD')),
				'auto_renew'    => $this->request_or_session('auto_renew', false),
				'cart_type'     => $this->request_or_session('cart_type'),
			));

			/*
			 * We need to handle free payments and trials w/o cc separately.
			 *
			 * In the same manner, if the order
			 * IS NOT free, we need to make sure
			 * the customer is not trying to game the system
			 * passing the free gateway to get an free account.
			 *
			 * That's what's we checking on the else case.
			 */
			if ($this->order->should_collect_payment() === false) {

				$gateway = wu_get_gateway('free');

			} else {

				if ($gateway->get_id() === 'free') {

					$this->errors = new \WP_Error('no-gateway', __('Payment gateway not registered.', 'wp-ultimo'));

					return false;

				} // end if;

			} // end if;

			if (!$gateway) {

				$this->errors = new \WP_Error('no-gateway', __('Payment gateway not registered.', 'wp-ultimo'));

				return false;

			} // end if;

			/*
			 * Set the gateway data.
			 */
			$gateway->set_order($this->order);

			/*
			 * Let's grab the cart type.
			 * We'll use it to perform the necessary actions
			 * with memberships, payments, and such.
			 */
			$type = $this->order->get_cart_type();

			/*
			 * Explicitly set the data types.
			 */
			$customer   = $this->order->get_customer();
			$membership = $this->order->get_membership();
			$payment    = $this->order->get_payment();

			/*
			 * Here's where the action actually happens.
			 *
			 * The gateway takes in the info about the transaction
			 * and perform the necessary steps to make sure the
			 * data on the gateway correctly reflects the data on WP Ultimo.
			 */
			$status = $gateway->process_checkout($payment, $membership, $customer, $this->order, $type);

			/*
			 * If the gateway returns a explicit false value
			 * we understand that as a signal that the gateway wants to
			 * deal with the modifications by itself.
			 *
			 * In that case, we simply return.
			 */
			if ($status === false) {

				return;

			} // end if;

			/*
			 * Run after every checkout processing.
			 *
			 * @since 2.0.4
			 */
			do_action('wu_checkout_done', $payment, $membership, $customer, $this->order, $type, $this);

			/*
			 * Deprecated hook for registration.
			 */
			if (has_action('wp_ultimo_registration')) {

				$_payment = wu_get_payment($payment->get_id());

				$args = array(
					0, // Site ID is not yet available at this point
					$customer->get_user_id(),
					$this->session->get('signup'),
					$_payment && $_payment->get_membership() ? new \WU_Plan($_payment->get_membership()->get_plan()) : false,
				);

				ob_start();

				do_action_deprecated('wp_ultimo_registration', $args, '2.0.0');

				ob_flush();

			} // end if;

			/*
			 * Checkout can be processed both on the
			 * front-end and the back-end, and we need to deal with
			 * them separately.
			 *
			 * Here we going to check for back-end ones.
			 */
			if (is_admin()) {

				$redirect_url = add_query_arg(array(
					'page'    => 'account',
					'updated' => $type,
				), admin_url('admin.php'));

				wp_redirect($redirect_url);

				exit;

			} // end if;

			/*
			 * Otherwise, we redirect
			 * to the thank you page
			 * of the front-end mode.
			 */
			$redirect_url = $this->get_thank_you_page();

			/**
			 * Set the redirect URL.
			 *
			 * This is a legacy filter. Some of the parameters
			 * passed are not available, such as the $site_id.
			 *
			 * @since 1.1.3 Let developers filter the redirect URL.
			 */
			$redirect_url = apply_filters('wp_ultimo_redirect_url_after_signup', $redirect_url, 0, get_current_user_id(), $_POST);

			$redirect_url = add_query_arg(array(
				'payment' => $payment ? $payment->get_hash() : 'none',
				'status'  => 'done',
			), $redirect_url);

			wp_redirect($redirect_url);

			exit;

		} catch (\Throwable $e) {

			wu_maybe_log_error($e);

			return new \WP_Error('error', $e->getMessage(), array(
				'trace'   => $e->getTrace(),
				'payment' => $payment,
			));

		} // end try;

	} // end process_checkout;

	/**
	 * Handle user display names, if first and last names are available.
	 *
	 * @since 2.0.4
	 *
	 * @param string $display_name The current display name.
	 * @return string
	 */
	public function handle_display_name($display_name) {

		$first_name = $this->request_or_session('first_name', '');

		$last_name = $this->request_or_session('last_name', '');

		if ($first_name || $last_name) {

			$display_name = trim("$first_name $last_name");

		} // end if;

		return $display_name;

	} // end handle_display_name;

	/*
	 * Helper methods
	 *
	 * These mostly deal with
	 * multi-step checkout control
	 * and can be mostly ignored!
	 */

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
	 * Checks if the user already exists.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_existing_user() {

		return is_user_logged_in();

	} // end is_existing_user;

	/**
	 * Returns the customer email verification status we want to use depending on the type of checkout.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_customer_email_verification_status() {

		$should_confirm_email = wu_get_setting('enable_email_verification', true);

		return $this->order->should_collect_payment() === false && $should_confirm_email ? 'pending' : 'none';

	} // end get_customer_email_verification_status;

	/**
	 * Adds the checkout scripts.
	 *
	 * @see $this->get_checkout_variables()
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		$custom_css = apply_filters('wu_checkout_custom_css', '');

		wp_add_inline_style('wu-checkout', $custom_css);

		wp_enqueue_style('wu-checkout');

		wp_enqueue_style('wu-admin');

		wp_register_script('wu-checkout', wu_get_asset('checkout.js', 'js'), array('jquery-core', 'wu-vue', 'moment', 'wu-block-ui', 'wu-functions', 'password-strength-meter', 'underscore', 'wp-polyfill', 'wp-hooks', 'wu-cookie-helpers'), wu_get_version(), true);

		wp_localize_script('wu-checkout', 'wu_checkout', $this->get_checkout_variables());

		wp_enqueue_script('wu-checkout');

	} // end register_scripts;

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

		/*
		 * If we enter the if statement below,
		 * it means that we don't have a step name set
		 * so we need to set it to the first.
		 */
		if ($current_step_index === false) {

			$current_step_index = 0;

		} // end if;

		$index = $current_step_index + 1;

		return isset($keys[$index]) ? $keys[$index] : $keys[$current_step_index];

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

		if (!is_array($steps)) {

			return array();

		} // end if;

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
	 * Checks if we are in the first step of the signup.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_first_step() {

		$step_names = array_column($this->steps, 'id');

		if (empty($step_names)) {

			return true;

		} // end if;

		return $this->step_name === array_shift($step_names);

	} // end is_first_step;

	/**
	 * Checks if we are in the last step of the signup.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_last_step() {

		/**
		 * What is this pre-flight parameter, you may ask...
		 *
		 * Well, some shortcodes can jump-start the signup process
		 * for example, the pricing table shortcode allows the
		 * customers to select a plan.
		 *
		 * This submits the form inside the shortcode to the registration
		 * page, but if that page is a one-step signup, this class-checkout
		 * will deal with it as if it was a last-step submission.
		 *
		 * The presence of the pre-flight URL parameter prevents that
		 * from happening.
		 *
		 * The summary is: if you need to post info to the registration page
		 * you need to add the ?pre-flight to the action URL.
		 */
		if (wu_request('pre-flight')) {

			return false;

		} // end if;

		$step_names = array_column($this->steps, 'id');

		if (empty($step_names)) {

			return true;

		} // end if;

		return $this->step_name === array_pop($step_names);

	} // end is_last_step;

	/**
	 * Decides if we should display errors on the checkout screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_display_checkout_errors() {

		if (wu_request('status') !== 'error') {

			return;

		} // end if;

	} // end maybe_display_checkout_errors;

} // end class Checkout;
