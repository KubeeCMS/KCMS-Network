<?php
/**
 * The Register API endpoint.
 *
 * @package WP_Ultimo
 * @subpackage API
 * @since 2.0.0
 */

namespace WP_Ultimo\API;

use \WP_Ultimo\Checkout\Cart;
use \WP_Ultimo\Database\Sites\Site_Type;
use \WP_Ultimo\Database\Payments\Payment_Status;
use \WP_Ultimo\Database\Memberships\Membership_Status;
use \WP_Ultimo\Objects\Billing_Address;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * The Register API endpoint.
 *
 * @since 2.0.0
 */
class Register_Endpoint {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Loads the initial register route hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_register_rest_routes', array($this, 'register_route'));

	} // end init;

	/**
	 * Adds a new route to the wu namespace, for the register endpoint.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\API $api The API main singleton.
	 * @return void
	 */
	public function register_route($api) {

		$namespace = $api->get_namespace();

		register_rest_route($namespace, '/register', array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array($this, 'handle_get'),
			'permission_callback' => array($api, 'check_authorization'),
		));

		register_rest_route($namespace, '/register', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array($this, 'handle_endpoint'),
			'permission_callback' => array($api, 'check_authorization'),
			'args'                => $this->get_rest_args(),
		));

	} // end register_route;

	/**
	 * Handle the register endpoint get for zapier integration reasons.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Request $request WP Request Object.
	 * @return array
	 */
	public function handle_get($request) {

		return array(
			'registration_status' => wu_get_setting('enable_registration', true) ? 'open' : 'closed',
		);

	} // end handle_get;

	/**
	 * Handle the register endpoint logic.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Request $request WP Request Object.
	 * @return array
	 */
	public function handle_endpoint($request) {

		global $wpdb;

		$params = json_decode($request->get_body(), true);

		if (\WP_Ultimo\API::get_instance()->should_log_api_calls()) {

			wu_log_add('api-calls', json_encode($params, JSON_PRETTY_PRINT));

		} // end if;

		$validation_errors = $this->validate($params);

		if (is_wp_error($validation_errors)) {

			$validation_errors->add_data(array(
				'status' => 400,
			));

			return $validation_errors;

		} // end if;

		$wpdb->query('START TRANSACTION');

		try {

			$customer = $this->maybe_create_customer($params);

			if (is_wp_error($customer)) {

				return $this->rollback_and_return($customer);

			} // end if;

			$customer->update_last_login(true, true);

			$customer->add_note(array(
				'text'      => __('Created via REST API', 'wp-ultimo'),
				'author_id' => $customer->get_user_id(),
			));

			/*
			 * Payment Method defaults
			 */
			$payment_method = wp_parse_args(wu_get_isset($params, 'payment_method'), array(
				'gateway'                 => '',
				'gateway_customer_id'     => '',
				'gateway_subscription_id' => '',
				'gateway_payment_id'      => '',
			));

			/*
			 * Cart params and creation
			 */
			$cart_params = $params;

			$cart_params = wp_parse_args($cart_params, array(
				'type' => 'new',
			));

			$cart = new Cart($cart_params);

			/*
			 * Validates if the cart is valid.
			 */
			if ($cart->is_valid() && count($cart->get_line_items()) === 0) {

				return new \WP_Error('invalid_cart', __('Products are required.', 'wp-ultimo'), array_merge((array) $cart->done(), array(
					'status' => 400,
				)));

			} // end if;

			/*
			 * Get Membership data
			 */
			$membership_data = $cart->to_membership_data();

			$membership_data = array_merge($membership_data, wu_get_isset($params, 'membership', array(
				'status' => Membership_Status::PENDING,
			)));

			$membership_data['customer_id']             = $customer->get_id();
			$membership_data['gateway']                 = wu_get_isset($payment_method, 'gateway');
			$membership_data['gateway_subscription_id'] = wu_get_isset($payment_method, 'gateway_subscription_id');
			$membership_data['gateway_customer_id']     = wu_get_isset($payment_method, 'gateway_customer_id');
			$membership_data['auto_renew']              = wu_get_isset($params, 'auto_renew');

			/*
			 * Unset the status because we are going to transition it later.
			 */
			$membership_status = $membership_data['status'];

			unset($membership_data['status']);

			$membership = wu_create_membership($membership_data);

			if (is_wp_error($membership)) {

				return $this->rollback_and_return($membership);

			} // end if;

			$membership->add_note(array(
				'text'      => __('Created via REST API', 'wp-ultimo'),
				'author_id' => $customer->get_user_id(),
			));

			$payment_data = $cart->to_payment_data();

			$payment_data = array_merge($payment_data, wu_get_isset($params, 'payment', array(
				'status' => Payment_Status::PENDING,
			)));

			/*
			 * Unset the status because we are going to transition it later.
			 */
			$payment_status = $payment_data['status'];

			unset($payment_data['status']);

			$payment_data['customer_id']        = $customer->get_id();
			$payment_data['membership_id']      = $membership->get_id();
			$payment_data['gateway']            = wu_get_isset($payment_method, 'gateway');
			$payment_data['gateway_payment_id'] = wu_get_isset($payment_method, 'gateway_payment_id');

			$payment = wu_create_payment($payment_data);

			if (is_wp_error($payment)) {

				return $this->rollback_and_return($payment);

			} // end if;

			$payment->add_note(array(
				'text'      => __('Created via REST API', 'wp-ultimo'),
				'author_id' => $customer->get_user_id(),
			));

			$site = false;

			/*
			 * Site creation.
			 */
			if (wu_get_isset($params, 'site')) {

				$site = $this->maybe_create_site($params, $membership);

				if (is_wp_error($site)) {

					return $this->rollback_and_return($site);

				} // end if;

			} // end if;

			/*
			 * Deal with status changes.
			 */
			if ($membership_status !== $membership->get_status()) {

				$membership->set_status($membership_status);

				$membership->save();

				/*
				 * The above change might trigger a site publication
				 * to take place, so we need to try to fetch the site
				 * again, this time as a WU Site object.
				 */
				if ($site) {

					$wp_site = get_site_by_path($site['domain'], $site['path']);

					if ($wp_site) {

						$site['id'] = $wp_site->blog_id;

					} // end if;

				} // end if;

			} // end if;

			if ($payment_status !== $payment->get_status()) {

				$payment->set_status($payment_status);

				$payment->save();

			} // end if;

		} catch (\Throwable $e) {

			$wpdb->query('ROLLBACK');

			return new \WP_Error('registration_error', $e->getMessage(), array('status' => 500));

		} // end try;

		$wpdb->query('COMMIT');

		/*
		 * We have everything we need now.
		 */
		return array(
			'membership' => $membership->to_array(),
			'customer'   => $customer->to_array(),
			'payment'    => $payment->to_array(),
			'site'       => $site ? $site : array('id' => 0),
		);

	} // end handle_endpoint;

	/**
	 * Returns the list of arguments allowed on to the endpoint.
	 *
	 * This is also used to build the documentation page for the endpoint.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_rest_args() {
		/*
		 * Billing Address Fields
		 */
		$billing_address_fields = Billing_Address::fields_for_rest(false);

		$customer_args = array(
			'customer_id' => array(
				'description' => __('The customer ID, if the customer already exists. If you also need to create a customer/wp user, use the "customer" property.', 'wp-ultimo'),
				'type'        => 'integer',
			),
			'customer'    => array(
				'description' => __('Customer data. Needs to be present when customer id is not.', 'wp-ultimo'),
				'type'        => 'object',
				'properties'  => array(
					'user_id'         => array(
						'description' => __('Existing WordPress user id to attach this customer to. If you also need to create a WordPress user, pass the properties "username", "password", and "email".', 'wp-ultimo'),
						'type'        => 'integer',
					),
					'username'        => array(
						'description' => __('The customer username. This is used to create the WordPress user.', 'wp-ultimo'),
						'type'        => 'string',
						'minLength'   => 4,
					),
					'password'        => array(
						'description' => __('The customer password. This is used to create the WordPress user. Note that no validation is performed here to enforce strength.', 'wp-ultimo'),
						'type'        => 'string',
						'minLength'   => 6,
					),
					'email'           => array(
						'description' => __('The customer email address. This is used to create the WordPress user.', 'wp-ultimo'),
						'type'        => 'string',
						'format'      => 'email',
					),
					'billing_address' => array(
						'type'       => 'object',
						'properties' => $billing_address_fields,
					)
				),
			),
		);

		$membership_args = array(
			'membership' => array(
				'description' => __('The membership data is automatically generated based on the cart info passed (e.g. products) but can be overridden with this property.', 'wp-ultimo'),
				'type'        => 'object',
				'properties'  => array(
					'status'                      => array(
						'description' => __('The membership status.', 'wp-ultimo'),
						'type'        => 'string',
						'enum'        => array_values(Membership_Status::get_allowed_list()),
						'default'     => Membership_Status::PENDING,
					),
					'date_expiration'             => array(
						'description' => __('The membership expiration date. Must be a valid PHP date format.', 'wp-ultimo'),
						'type'        => 'string',
						'format'      => 'date-time',
					),
					'date_trial_end'              => array(
						'description' => __('The membership trial end date. Must be a valid PHP date format.', 'wp-ultimo'),
						'type'        => 'string',
						'format'      => 'date-time',
					),
					'date_activated'              => array(
						'description' => __('The membership activation date. Must be a valid PHP date format.', 'wp-ultimo'),
						'type'        => 'string',
						'format'      => 'date-time',
					),
					'date_renewed'                => array(
						'description' => __('The membership last renewed date. Must be a valid PHP date format.', 'wp-ultimo'),
						'type'        => 'string',
						'format'      => 'date-time',
					),
					'date_cancellation'           => array(
						'description' => __('The membership cancellation date. Must be a valid PHP date format.', 'wp-ultimo'),
						'type'        => 'string',
						'format'      => 'date-time',
					),
					'date_payment_plan_completed' => array(
						'description' => __('The membership completion date. Used when the membership is limited to a limited number of billing cycles. Must be a valid PHP date format.', 'wp-ultimo'),
						'type'        => 'string',
						'format'      => 'date-time',
					),
				),
			),
		);

		$payment_args = array(
			'payment'        => array(
				'description' => __('The payment data is automatically generated based on the cart info passed (e.g. products) but can be overridden with this property.', 'wp-ultimo'),
				'type'        => 'object',
				'properties'  => array(
					'status' => array(
						'description' => __('The payment status.', 'wp-ultimo'),
						'type'        => 'string',
						'enum'        => array_values(Payment_Status::get_allowed_list()),
						'default'     => Payment_Status::PENDING,
					),
				),
			),
			'payment_method' => array(
				'description' => __('Payment method information. Useful when using the REST API to integrate other payment methods.', 'wp-ultimo'),
				'type'        => 'object',
				'properties'  => array(
					'gateway'                 => array(
						'description' => __('The gateway name. E.g. stripe.', 'wp-ultimo'),
						'type'        => 'string',
					),
					'gateway_customer_id'     => array(
						'description' => __('The customer ID on the gateway system.', 'wp-ultimo'),
						'type'        => 'string',
					),
					'gateway_subscription_id' => array(
						'description' => __('The subscription ID on the gateway system.', 'wp-ultimo'),
						'type'        => 'string',
					),
					'gateway_payment_id'      => array(
						'description' => __('The payment ID on the gateway system.', 'wp-ultimo'),
						'type'        => 'string',
					),
				),
			),
		);

		$site_args = array(
			'site' => array(
				'type'       => 'object',
				'properties' => array(
					'site_url'    => array(
						'type'        => 'string',
						'description' => __('The site subdomain or subdirectory (depending on your Multisite install). This would be "test" in "test.your-network.com".', 'wp-ultimo'),
						'minLength'   => 4,
						'required'    => true,
					),
					'site_title'  => array(
						'type'        => 'string',
						'description' => __('The site title. E.g. My Amazing Site', 'wp-ultimo'),
						'minLength'   => 4,
						'required'    => true,
					),
					'publish'     => array(
						'description' => __('If we should publish this site regardless of membership/payment status. Sites are created as pending by default, and are only published when a payment is received or the status of the membership changes to "active". This flag allows you to bypass the pending state.', 'wp-ultimo'),
						'type'        => 'boolean',
						'default'     => false,
					),
					'template_id' => array(
						'description' => __('The template ID we should copy when creating this site. If left empty, the value dictated by the products will be used.', 'wp-ultimo'),
						'type'        => 'integer',
					),
					'site_meta'   => array(
						'description' => __('An associative array of key values to be saved as site_meta.', 'wp-ultimo'),
						'type'        => 'object',
					),
					'site_option' => array(
						'description' => __('An associative array of key values to be saved as site_options. Useful for changing plugin settings and other site configurations.', 'wp-ultimo'),
						'type'        => 'object',
					),
				),
			),
		);

		$cart_args = array(
			'products'      => array(
				'description' => __('The products to be added to this membership. Takes an array of product ids or slugs.', 'wp-ultimo'),
				'uniqueItems' => true,
				'type'        => 'array',
			),
			'duration'      => array(
				'description' => __('The membership duration.', 'wp-ultimo'),
				'type'        => 'integer',
				'required'    => false,
			),
			'duration_unit' => array(
				'description' => __('The membership duration unit.', 'wp-ultimo'),
				'type'        => 'string',
				'default'     => 'month',
				'enum'        => array(
					'day',
					'week',
					'month',
					'year',
				),
			),
			'discount_code' => array(
				'description' => __('A discount code. E.g. PROMO10.', 'wp-ultimo'),
				'type'        => 'string',
			),
			'auto_renew'    => array(
				'description' => __('The membership auto-renew status. Useful when integrating with other payment options via this REST API.', 'wp-ultimo'),
				'type'        => 'boolean',
				'default'     => false,
				'required'    => true,
			),
			'country'       => array(
				'description' => __('The customer country. Used to calculate taxes and check if registration is allowed for that country.', 'wp-ultimo'),
				'type'        => 'string',
				'default'     => '',
			),
			'currency'      => array(
				'description' => __('The currency to be used.', 'wp-ultimo'),
				'type'        => 'string',
			),
		);

		$args = array_merge($customer_args, $membership_args, $cart_args, $payment_args, $site_args);

		return apply_filters('wu_rest_register_endpoint_args', $args, $this);

	} // end get_rest_args;

	/**
	 * Maybe create a customer, if needed.
	 *
	 * @since 2.0.0
	 *
	 * @param array $p The request parameters.
	 * @return \WP_Ultimo\Models\Customer|\WP_Error
	 */
	public function maybe_create_customer($p) {

		$customer_id = wu_get_isset($p, 'customer_id');

		if ($customer_id) {

			$customer = wu_get_customer($customer_id);

			if (!$customer) {

				return new \WP_Error('customer_not_found', __('The customer id sent does not correspond to a valid customer.', 'wp-ultimo'));

			} // end if;

		} else {

			$customer = wu_create_customer($p['customer']);

		} // end if;

		return $customer;

	} // end maybe_create_customer;

	/**
	 * Undocumented function
	 *
	 * @since 2.0.0
	 *
	 * @param array                        $p The request parameters.
	 * @param \WP_Ultimo\Models\Membership $membership The membership created.
	 * @return array|\WP_Ultimo\Models\Site\|\WP_Error
	 */
	public function maybe_create_site($p, $membership) {

		$site_data = $p['site'];

		/*
		 * Let's get a list of membership sites.
		 * This list includes pending sites as well.
		 */
		$sites = $membership->get_sites();

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

		$site_url = wu_get_isset($site_data, 'site_url');

		$d = wu_get_site_domain_and_path($site_url);

		/*
		 * Validates the site url.
		 */
		$results = wpmu_validate_blog_signup($site_url, wu_get_isset($site_data, 'site_title'), $membership->get_customer()->get_user());

		if ($results['errors']->has_errors()) {

			return $results['errors'];

		} // end if;

		/*
		 * Get the transient data to save with the site
		 * that way we can use it when actually registering
		 * the site on WordPress.
		 */
		$transient = array_merge(
			wu_get_isset($site_data, 'site_meta', array()),
			wu_get_isset($site_data, 'site_option', array())
		);

		$template_id = apply_filters('wu_checkout_template_id', (int) wu_get_isset($site_data, 'template_id'), $membership, $this);

		$site_data = array(
			'domain'         => $d->domain,
			'path'           => $d->path,
			'title'          => wu_get_isset($site_data, 'site_title'),
			'template_id'    => $template_id,
			'customer_id'    => $membership->get_customer()->get_id(),
			'membership_id'  => $membership->get_id(),
			'transient'      => $transient,
			'signup_meta'    => wu_get_isset($site_data, 'site_meta', array()),
			'signup_options' => wu_get_isset($site_data, 'site_option', array()),
			'type'           => Site_Type::CUSTOMER_OWNED,
		);

		$membership->create_pending_site($site_data);

		$site_data['id'] = 0;

		if (wu_get_isset($site_data, 'publish')) {

			$membership->publish_pending_site();

			$wp_site = get_site_by_path($site_data['domain'], $site_data['path']);

			if ($wp_site) {

				$site_data['id'] = $wp_site->blog_id;

			} // end if;

		} // end if;

		return $site_data;

	} // end maybe_create_site;

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		return array(
			'customer_id'       => 'required_without:customer',
			'customer'          => 'required_without:customer_id',
			'customer.username' => 'required_without_all:customer_id,customer.user_id',
			'customer.password' => 'required_without_all:customer_id,customer.user_id',
			'customer.email'    => 'required_without_all:customer_id,customer.user_id',
			'customer.user_id'  => 'required_without_all:customer_id,customer.username,customer.password,customer.email',
			'site.site_url'     => 'required_with:site|alpha_num|min:4|lowercase|unique_site',
			'site.site_title'   => 'required_with:site|min:4',
		);

	} // end validation_rules;

	/**
	 * Validates the rules and make sure we only save models when necessary.
	 *
	 * @since 2.0.0
	 * @param array $args The params to validate.
	 * @return array|\WP_Error
	 */
	public function validate($args) {

		$validator = new \WP_Ultimo\Helpers\Validator;

		$validator->validate($args, $this->validation_rules());

		if ($validator->fails()) {

			return $validator->get_errors();

		} // end if;

		return true;

	} // end validate;

	/**
	 * Rolls back database changes and returns the error passed.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Error $error The error to return.
	 * @return \WP_Error
	 */
	protected function rollback_and_return($error) {

		global $wpdb;

		$wpdb->query('ROLLBACK');

		return $error;

	} // end rollback_and_return;

} // end class Register_Endpoint;
