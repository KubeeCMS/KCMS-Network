<?php
/**
 * Customer Manager
 *
 * Handles processes related to Customers.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Customer_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use \WP_Ultimo\Managers\Base_Manager;
use \WP_Ultimo\Models\Customer;
use \WP_Ultimo\Database\Memberships\Membership_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to webhooks.
 *
 * @since 2.0.0
 */
class Customer_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'customer';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Customer';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		add_action('wp_login', array($this, 'log_ip_and_last_login'), 10, 2);

		add_filter('heartbeat_send', array($this, 'on_heartbeat_send'));

		add_action('wu_transition_customer_email_verification', array($this, 'transition_customer_email_verification'), 10, 3);

		add_action('init', array($this, 'maybe_verify_email_address'));

		add_action('wu_maybe_create_customer', array($this, 'maybe_add_to_main_site'), 10, 2);

		add_action('wp_ajax_wu_resend_verification_email', array($this, 'handle_resend_verification_email'));

	} // end init;

	/**
	 * Handle the resend verification email ajax action.
	 *
	 * @since 2.0.4
	 * @return void
	 */
	public function handle_resend_verification_email() {

		if (!check_ajax_referer('wu_resend_verification_email_nonce', false, false)) {

			wp_send_json_error(new \WP_Error('not-allowed', __('Error: you are not allowed to perform this action.', 'wp-ultimo')));

			exit;

		} // end if;

		$customer = wu_get_current_customer();

		if (!$customer) {

			wp_send_json_error(new \WP_Error('customer-not-found', __('Error: customer not found.', 'wp-ultimo')));

			exit;

		} // end if;

		$customer->send_verification_email();

		wp_send_json_success();

		exit;

	} // end handle_resend_verification_email;

	/**
	 * Handle heartbeat response sent.
	 *
	 * @since 2.0.0
	 *
	 * @param array $response The Heartbeat response.
	 * @return array $response The Heartbeat response
	 */
	public function on_heartbeat_send($response) {

		$this->log_ip_and_last_login(wp_get_current_user());

		return $response;

	} // end on_heartbeat_send;

	/**
	 * Saves the IP address and last_login date onto the user.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $user The WP User object of the user that logged in.
	 * @return void
	 */
	public function log_ip_and_last_login($user) {

		if (!is_a($user, '\WP_User')) {

			$user = get_user_by('login', $user);

		} // end if;

		if (!$user) {

			return;

		} // end if;

		$customer = wu_get_customer_by_user_id($user->ID);

		if (!$customer) {

			return;

		} // end if;

		$customer->update_last_login();

	} // end log_ip_and_last_login;

	/**
	 * Watches the change in customer verification status to take action when needed.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $old_status The old status of the customer verification.
	 * @param string  $new_status The new status of the customer verification.
	 * @param integer $customer_id Customer ID.
	 * @return void
	 */
	public function transition_customer_email_verification($old_status, $new_status, $customer_id) {

		if ($new_status !== 'pending') {

			return;

		} // end if;

		$customer = wu_get_customer($customer_id);

		if ($customer) {

			$customer->send_verification_email();

		} // end if;

	} // end transition_customer_email_verification;

	/**
	 * Verifies a customer by checking the email key.
	 *
	 * If only one membership is available and that's pending,
	 * we set it to active. That will trigger the publication of
	 * pending websites as well.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_verify_email_address() {

		$email_verify_key = wu_request('email-verification-key');

		if (!$email_verify_key || !is_user_logged_in()) {

			return;

		} // end if;

		$customer_hash = wu_request('customer');

		$customer_to_verify = wu_get_customer_by_hash($customer_hash);

		if (!$customer_to_verify) {

			wp_die(__('Invalid verification key.', 'wp-ultimo'));

		} // end if;

		$current_customer = wu_get_current_customer();

		if (!$current_customer) {

			wp_die(__('Invalid verification key.', 'wp-ultimo'));

		} // end if;

		if ($current_customer->get_id() !== $customer_to_verify->get_id()) {

			wp_die(__('Invalid verification key.', 'wp-ultimo'));

		} // end if;

		if ($customer_to_verify->get_email_verification() !== 'pending') {

			wp_die(__('Invalid verification key.', 'wp-ultimo'));

		} // end if;

		$key = $customer_to_verify->get_verification_key();

		if (!$key) {

			wp_die(__('Invalid verification key.', 'wp-ultimo'));

		} // end if;

		if ($key !== $email_verify_key) {

			wp_die(__('Invalid verification key.', 'wp-ultimo'));

		} // end if;

		/*
		 * Uff! If we got here, we can verify the customer.
		 */
		$customer_to_verify->disable_verification_key();
		$customer_to_verify->set_email_verification('verified');
		$customer_to_verify->save();

		/*
		 * Checks for memberships and pending sites.
		 */
		$memberships = $customer_to_verify->get_memberships();

		/*
		 * We can only take action if the customer has
		 * only one membership, otherwise we can't be sure about
		 * which one to manage.
		 */
		if (count($memberships) === 1) {

			$membership = current($memberships);

			/*
			 * Only publish pending memberships
			 */
			if ($membership->get_status() === Membership_Status::PENDING) {

				$membership->set_status(Membership_Status::ACTIVE);

				$membership->save();

			} elseif ($membership->get_status() === Membership_Status::TRIALING) {

				$membership->publish_pending_site_async();

			} // end if;

			$payments = $membership->get_payments();

			if ($payments) {

				$redirect_url = add_query_arg(array(
					'payment' => $payments[0]->get_hash(),
					'status'  => 'done',
				), wu_get_registration_url());

				wp_redirect($redirect_url);

				exit;

			} // end if;

		} // end if;

		wp_redirect(get_admin_url($customer_to_verify->get_primary_site_id()));

		exit;

	} // end maybe_verify_email_address;

	/**
	 * Maybe adds the customer to the main site.
	 *
	 * @since 2.0.0
	 *
	 * @param Customer $customer The customer object.
	 * @param Checkout $checkout The checkout object.
	 * @return void
	 */
	public function maybe_add_to_main_site($customer, $checkout) {

		if (!wu_get_setting('add_users_to_main_site')) {

			return;

		} // end if;

		$user_id = $customer->get_user_id();

		$is_already_user = is_user_member_of_blog($user_id, wu_get_main_site_id());

		if ($is_already_user === false) {

			$role = wu_get_setting('main_site_default_role', 'subscriber');

			add_user_to_blog(wu_get_main_site_id(), $user_id, $role);

		} // end if;

	} // end maybe_add_to_main_site;

} // end class Customer_Manager;
