<?php
/**
 * Adds support to multiple accounts for plugins such as WooCommerce.
 *
 * WordPress, even in multisite mode, has only one User database table.
 * This can cause problems in a WaaS environment.
 *
 * Image the following scenario:
 *
 * - You two e-commerce stores on your network: Store A and Store B;
 * - A potential customer comes to Store A and purchases an item, to do that
 *   they need to create an account.
 * - A month later, that same customer stumbles upon Store B, and decides to make
 *   another purchase. This time, however, they get an 'email already in use error'
 *   during checkout.
 * - This happens because the user database is shared across sites, but it can
 *   cause a lot of confusion.
 *
 * This class attempts to handle situations like this gracefully.
 * It will allow the customer to create a second user account with the same email address,
 * and will scope that user to the sub-site where it was created only.
 *
 * Right now, it supports:
 * - Default WordPress registration;
 * - WooCommerce.
 *
 * @package WP_Ultimo
 * @subpackage Compat/Multiple_Accounts_Compat
 * @since 2.0.0
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds support to multiple accounts for plugins such as WooCommerce.
 *
 * @since 2.0.0
 */
class Multiple_Accounts_Compat {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Backs up the original email.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $original_email = '';

	/**
	 * Holds the fake email generated for later reference.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $fake_email = '';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		// Add the settings to enable or disable this feature.
		add_action('wu_settings_login', array($this, 'add_settings'), 10);

		if ($this->should_load()) {

			// Unset user, if the current one is not part of the site.
			add_action('plugins_loaded', array($this, 'maybe_unset_current_user'), -10);

			// prevents woocommerce from adding customers to sites without our knowledge
			add_action('woocommerce_process_login_errors', array($this, 'prevent_woo_from_adding_to_blog'), -10);

			// Add post action to allow
			add_action('init', array($this, 'check_post_for_register'));

			// Change the email back
			add_filter('woocommerce_new_customer_data', array($this, 'new_costumer_original_email'));

			// check for register
			add_filter('wpmu_validate_user_signup', array($this, 'skip_email_exist'));

			// For single site as well
			add_filter('pre_user_email', array($this, 'skip_email_exist_single'));

			// Action in the login to debug the login info
			add_filter('authenticate', array($this, 'fix_login'), 50000, 3);

			// Now we handle the password thing
			add_action('init', array($this, 'handle_reset_password'), 2000);

			// Now we add a custom column in that table to allow the admin to control them
			add_filter('wpmu_users_columns', array($this, 'add_multiple_account_column'));

			// Adds the number of additional accounts.
			add_filter('manage_users_custom_column', array($this, 'add_column_content'), 10, 3);

			// It's a fix for the WooCommerce Email
			add_filter('woocommerce_checkout_update_order_meta', array($this, 'fix_billing_email_in_wc_order'), 10, 2);

		} // end if;

	} // end init;

	/**
	 * Prevent WooCommerce from adding users to site without us knowing.
	 *
	 * We only use the filter 'woocommerce_process_login_errors', because
	 * that's it guaranteed to only run inside the login handler.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $results Arbitrary item being return by the filter chosen.
	 * @return mixed
	 */
	public function prevent_woo_from_adding_to_blog($results) {

		add_filter('can_add_user_to_blog', '__return_false');

		return $results;

	} // end prevent_woo_from_adding_to_blog;

	/**
	 * Checks if the user belongs to a site they are currently viewing and unset them if they don't.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_unset_current_user() {

		global $current_user;

		if (is_admin() || is_main_site() || current_user_can('manage_network')) {

			return;

		} // end if;

		/**
		 * Allow developers to bypass the unset current user code.
		 *
		 * Returning anything other than null will bypass the unset
		 * of the current user logged in.
		 *
		 * This can be useful in some scenarios, for example,
		 * when dealing with sub-sites that are being used as
		 * admin panels.
		 *
		 * @since 2.0.11
		 *
		 * @param mixed $null_or_bypass Null to move on, anything else to bypass it.
		 * @param false|\WP_User $current_user The current user object.
		 * @return mixed
		 */
		if (apply_filters('wu_bypass_unset_current_user', null, $current_user) !== null) {

			return;

		} // end if;

		$user = wp_get_current_user();

		$has_user = $this->check_for_user_in_site($user->user_email, $user->ID);

		/*
		 * Despite being currently logged in, this account does not
		 * belong to the sub-site in question, so we unset the user
		 * currently logged in.
		 */
		if ($has_user === false) {

			wu_x_header('X-Ultimo-Multiple-Accounts: user-unset');

			$current_user = null;

			wp_set_current_user(0);

		} // end if;

	} // end maybe_unset_current_user;

	/**
	 * Allow plugin developers to disable this functionality to prevent compatibility issues.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function should_load() {

		return apply_filters('wu_should_load_multiple_accounts_support', wu_get_setting('enable_multiple_accounts', true));

	} // end should_load;

	// Methods

	/**
	 * Add multiple accounts setting to enable or disable this feature.
	 *
	 * @since 2.0.0
	 *
	 * @return void.
	 */
	public function add_settings() {

		wu_register_settings_field('login-and-registration', 'multiple_accounts_header', array(
			'title' => __('Multiple Accounts', 'wp-ultimo'),
			'desc'  => __('Options related to the Multiple Accounts feature.', 'wp-ultimo'),
			'type'  => 'header',
		));

		wu_register_settings_field('login-and-registration', 'enable_multiple_accounts', array(
			'title'   => __('Enable Multiple Accounts', 'wp-ultimo'),
			'desc'    => __('Allow users to have accounts in different sites with the same email address. This is useful when running stores with WooCommerce and other plugins, for example.', 'wp-ultimo') . ' ' . sprintf('<a href="%s" target="_blank">%s</a>', wu_get_documentation_url('multiple-accounts'), __('Read More', 'wp-ultimo')),
			'type'    => 'toggle',
			'default' => 0,
		));

	} // end add_settings;

	/**
	 * Fixes the email on the WooCommerce Orders.
	 *
	 * @since 2.0.0
	 *
	 * @param int   $order_id The WooCommerce order.
	 * @param mixed $posted No idea.
	 * @return void
	 */
	public function fix_billing_email_in_wc_order($order_id, $posted) {

		if ($posted['billing_email'] === $this->fake_email) {

			update_post_meta($order_id, '_billing_email', $this->original_email);

		} // end if;

	} // end fix_billing_email_in_wc_order;

	/**
	 * Adds the Multiple accounts column to the users table.
	 *
	 * @since 2.0.0
	 *
	 * @param array $columns Original columns.
	 * @return array
	 */
	public function add_multiple_account_column($columns) {

		$columns['multiple_accounts'] = __('Multiple Accounts', 'wp-ultimo');

		return $columns;

	} // end add_multiple_account_column;

	/**
	 * Renders the content of our custom column.
	 *
	 * @since 2.0.0
	 *
	 * @param null   $null No idea.
	 * @param string $column The name of the column.
	 * @param int    $user_id The ID of the user.
	 * @return void
	 */
	public function add_column_content($null, $column, $user_id) {

		if ($column === 'multiple_accounts') {

			// Get user email
			$user = get_user_by('ID', $user_id);

			// Get all the accounts with the same email
			$users = new \WP_User_Query(array(
				'blog_id' => 0,
				'search'  => $user->user_email,
				'fields'  => array('ID', 'user_login'),
			));

			// translators: the %d is the account count for that email address.
			$html = sprintf(__('<strong>%d</strong> accounts using this email.', 'wp-ultimo'), $users->total_users);

			$html .= sprintf("<br><a href='%s' class=''>" . __('See all', 'wp-ultimo') . ' &raquo;</a>', network_admin_url('users.php?s=' . $user->user_email));

			echo $html;

		} // end if;

	} // end add_column_content;

	/**
	 * Handles password resetting.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_reset_password() {

		// Only run in the right case
		if (wu_request('action') === 'retrievepassword' || wu_request('wc_reset_password')) {

			// Only do thing if is login by email
			if (is_email($_REQUEST['user_login'])) {

				$user = $this->get_right_user($_REQUEST['user_login']);

				$_REQUEST['user_login'] = $user->user_login;

				$_POST['user_login'] = $user->user_login;

			} // end if;

		} // end if;

	} // end handle_reset_password;

	/**
	 * Checks if a given user is a member in the site.
	 *
	 * @since 2.0.0
	 *
	 * @param string $email    The user email address.
	 * @param int    $user_id  The user ID to check.
	 * @return bool
	 */
	public function check_for_user_in_site($email, $user_id = 0) {

		// Sets the right user to be returned;
		$has_user = false;

		$query = array(
			'search' => $email,
		);

		/**
		 * When the user id is present, we use it
		 * to disambiguate the users, as the same user
		 * with the same email address can have users
		 * registered on different sub-sites.
		 *
		 * @since 2.0.11
		 */
		if ($user_id) {

			$query['include'] = array(
				absint($user_id),
			);

		} // end if;

		// Now we search for the correct user based on the password and the blog information
		$users = new \WP_User_Query($query);

		// Loop the results and check which one is in this group
		foreach ($users->results as $user_with_email) {

			// Check for the pertinence of that user in this site
			if ($this->user_can_for_blog($user_with_email, get_current_blog_id(), 'read')) {

				$has_user = true;

				break;

			} // end if;

		} // end foreach;

		// If nothing was found return false;
		return $has_user;

	} // end check_for_user_in_site;

	/**
	 * Gets the right user when logging-in.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_User $user The current user object. Usually false.
	 * @param string   $username The username to search for.
	 * @param string   $password The user password.
	 * @return \WP_User
	 */
	public function fix_login($user, $username, $password) {

		if (isset($_POST['username'])) {

			// Get the email
			$email = $_POST['username'];

			// Only do thing if is login by email
			if (is_email($email)) {

				// Sets the right user to be returned;
				$user = $this->get_right_user($email, $password);

				if (!$user) {

					return null;

				} // end if;

			} // end if;

		} // end if;

		return $user;

	} // end fix_login;

	/**
	 * Skip the email check in WordPress
	 *
	 * @since 2.0.0
	 *
	 * @param array $result Array containing signup errors.
	 * @return array
	 */
	public function skip_email_exist($result) {

		$key = array_search(__('Sorry, that email address is already used!'), $result['errors']->errors['user_email']);

		if (isset($result['errors']->errors['user_email']) && $key !== false) {

			unset($result['errors']->errors['user_email'][$key]);

			if (empty($result['errors']->errors['user_email'])) {

				unset($result['errors']->errors['user_email']);

			} // end if;

		} // end if;

		if (!defined('WP_IMPORTING')) {

		  define('WP_IMPORTING', 'SKIP_EMAIL_EXIST'); // phpcs:ignore

		} // end if;

		return $result;

	} // end skip_email_exist;

	/**
	 * Skip email existing while importing.
	 *
	 * @since 2.0.0
	 *
	 * @param string $user_email Email address.
	 * @return string
	 */
	public function skip_email_exist_single($user_email) {

		if (!defined('WP_IMPORTING')) {

		  define('WP_IMPORTING', 'SKIP_EMAIL_EXIST'); // phpcs:ignore

		} // end if;

		return $user_email;

	} // end skip_email_exist_single;

	/**
	 * We check the POST variable to change the email, preventing the block from WordPress.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function check_post_for_register() {

		$checkout_action = wu_request('checkout_action', 'not-wp-ultimo');

		/*
		 * We don't want to change this fo WP Ultimo
		 * registrations.
		 */
		if ($checkout_action !== 'not-wp-ultimo') {

			return;

		} // end if;

		$billing_email = wu_request('billing_email');

		// Check if we need to run
		if ($billing_email) {

			// We need to check if theres a user with the same email in the same site
			if (!$this->check_for_user_in_site($billing_email)) {

				// Copy original email
				$this->original_email = $billing_email;

				// Set that to a different email
				$_POST['billing_email'] = rand(0, 1000) . '_fake_wp_ultimo@email.com';

				$this->fake_email = $_POST['billing_email'];

			} // end if;

		} // end if;

		// Check if we need to run
		if (isset($_POST['email']) && isset($_POST['woocommerce-register-nonce'])) {

			// We need to check if theres a user with the same email in the same site
			if (!$this->check_for_user_in_site($_POST['email'])) {

				// Copy original email
				$this->original_email = $_POST['email'];

				// Set that to a different email
				$_POST['email'] = rand(0, 1000) . '_fake_wp_ultimo@email.com';

				$this->fake_email = $_POST['email'];

			} // end if;

		} // end if;

	} // end check_post_for_register;

	/**
	 * We change the email back to the original.
	 *
	 * @since 2.0.0
	 *
	 * @param array $user_data The user data.
	 * @return array
	 */
	public function new_costumer_original_email($user_data) {

		// Fix email
		$user_data['user_email'] = $this->original_email;

		// Fix username.
		$username = sanitize_user(current(explode('@', $user_data['user_email'])), true);

		// Ensure username is unique.
		$append = 1;

		$o_username = $username;

		while (username_exists($username)) {

			$username = $o_username . $append;

			$append++;

		} // end while;

		// Fix email
		$user_data['user_login'] = $username;

		// Return refactored data
		return $user_data;

	} // end new_costumer_original_email;

	/**
	 * Check if user can do something in a specific blog.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_User $user The user object.
	 * @param int      $blog_id The blog id.
	 * @param string   $capability Capability to check against.
	 * @return boolean
	 */
	public function user_can_for_blog($user, $blog_id, $capability) {

		$switched = is_multisite() ? switch_to_blog($blog_id) : false;

		$current_user = $user;

		if (empty($current_user)) {

			if ($switched) {

				restore_current_blog();

			} // end if;

			return false;

		} // end if;

		$args = array_slice(func_get_args(), 2);

		$args = array_merge(array($capability), $args);

		$can = call_user_func_array(array($current_user, 'has_cap'), $args);

		if ($switched) {

			restore_current_blog();

		} // end if;

		return $can;

	} // end user_can_for_blog;

	/**
	 * Gets the right user for a given domain.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $email User email address.
	 * @param boolean $password User password.
	 * @return \WP_User|false
	 */
	protected function get_right_user($email, $password = false) {

		// Sets the right user to be returned;
		$right_user = null;

		// $hash = wp_hash_password($password);
		// Now we search for the correct user based on the password and the blog information
		$users = new \WP_User_Query(array('search' => $email));

		// Loop the results and check which one is in this group
		foreach ($users->results as $user_with_email) {

			$conditions = $password == false ? true : wp_check_password($password, $user_with_email->user_pass, $user_with_email->ID);

			// Check for the pertinence of that user in this site
			if ($conditions && $this->user_can_for_blog($user_with_email, get_current_blog_id(), 'read')) {

				// Set right user
				$right_user = $user_with_email;

				continue;

			} // end if;

		} // end foreach;

		// Return right user
		return $right_user;

	} // end get_right_user;

} // end class Multiple_Accounts_Compat;
