<?php
/**
 * Handles registration pages and such.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles registration pages and such.
 *
 * @since 2.0.0
 */
class Checkout_Pages {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Initializes the Checkout_Pages singleton and adds hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_filter('display_post_states', array($this, 'add_wp_ultimo_status_annotation'), 10, 2);

		add_action('wu_thank_you_site_block', array($this, 'add_verify_email_notice'), 10, 3);

		add_shortcode('wu_confirmation', array($this, 'render_confirmation_page'));

		if (is_main_site()) {

			add_action('before_signup_header', array($this, 'redirect_to_registration_page'));

			$use_custom_login = wu_get_setting('enable_custom_login_page', false);

			if (!$use_custom_login) {

				return;

			} // end if;

			add_filter('login_url', array($this, 'filter_login_url'), 10, 3);

			add_filter('lostpassword_url', array($this, 'filter_login_url'), 10, 3);

			add_filter('retrieve_password_message', array($this, 'replace_reset_password_link'), 10, 4);

			add_filter('network_site_url', array($this, 'maybe_change_wp_login_on_urls'));

			add_action('login_init', array($this, 'maybe_obfuscate_login_url'), 9);

			add_action('template_redirect', array($this, 'maybe_redirect_to_admin_panel'));

			add_action('after_password_reset', array($this, 'maybe_redirect_to_confirm_screen'));

			add_action('lost_password', array($this, 'maybe_handle_password_reset_errors'));

			add_action('validate_password_reset', array($this, 'maybe_handle_password_reset_errors'));

			/**
			 * Adds the force elements controls.
			 */
			add_action('post_submitbox_misc_actions', array($this, 'render_compat_mode_setting'));

			add_action('save_post', array($this, 'handle_compat_mode_setting'));

		} // end if;

	} // end init;

	/**
	 * Renders the compat mode option for pages and posts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_compat_mode_setting() {

		$post_id = get_the_ID();

		$value = get_post_meta($post_id, '_wu_force_elements_loading', true);

		wp_nonce_field('_wu_force_compat_' . $post_id, '_wu_force_compat');

		// phpcs:disable
		?>
		
    <div class="misc-pub-section misc-pub-section-last" style="margin-top: 12px; margin-bottom: 6px; display: flex; align-items: center;">
				<label for="wu-compat-mode">
						<span style="display: block; font-weight: 600; margin-bottom: 3px;"><?php _e('WP Ultimo Compatibility Mode', 'wp-ultimo'); ?></span>
						<small style="display: block; line-height: 1.8em;"><?php _e('Toggle this option on if WP Ultimo elements are not loading correctly or at all.', 'wp-ultimo'); ?></small>
				</label>
				<div style="margin-left: 6px;">
					<input id="wu-compat-mode" type="checkbox" value="1" <?php checked($value, true, true); ?> name="_wu_force_elements_loading" />
				</div>
    </div>

		<?php

		// phpcs:enable

	} // end render_compat_mode_setting;

	/**
	 * Handles saving the compat mode switch on posts.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id The id of the post being saved.
	 * @return void
	 */
	public function handle_compat_mode_setting($post_id) {

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {

			return;

		} // end if;

		if (!isset($_POST['_wu_force_compat']) || !wp_verify_nonce($_POST['_wu_force_compat'], '_wu_force_compat_' . $post_id)) {

			return;

		} // end if;

		if (!current_user_can('edit_post', $post_id)) {

			return;

		} // end if;

		if (isset($_POST['_wu_force_elements_loading'])) {

			update_post_meta($post_id, '_wu_force_elements_loading', $_POST['_wu_force_elements_loading']);

		} else {

			delete_post_meta($post_id, '_wu_force_elements_loading');

		} // end if;

	} // end handle_compat_mode_setting;

	/**
	 * Replace wp-login.php in email URLs.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url The URL to filter.
	 * @return string
	 */
	public function maybe_change_wp_login_on_urls($url) {
		/*
		 * Only perform computational-heavy tasks if the URL has
		 * wp-login.php in it to begin with.
		 */
		if (strpos($url, 'wp-login.php') === false) {

			return $url;

		} // end if;

		$post_id = wu_get_setting('default_login_page', 0);

		$post = get_post($post_id);

		if ($post) {

			$url = str_replace('wp-login.php', $post->post_name, $url);

		} // end if;

		return $url;

	} // end maybe_change_wp_login_on_urls;

	/**
	 * Get an error message.
	 *
	 * @since 2.0.0
	 *
	 * @param string $error_code The error code.
	 * @return string
	 */
	public function get_error_message($error_code) {

		$messages = array(
			'invalidcombo'            => __('<strong>Error</strong>: There is no account with that username or email address.'),
			'password_reset_mismatch' => __('<strong>Error</strong>: The passwords do not match.'),
			'invalidkey'              => __('<strong>Error</strong>: Your password reset link appears to be invalid. Please request a new link below.'),
			'expiredkey'              => __('<strong>Error</strong>: Your password reset link has expired. Please request a new link below.'),
		);

		return wu_get_isset($messages, $error_code, __('Something went wrong', 'wp-ultimo'));

	} // end get_error_message;

	/**
	 * Handle password reset errors.
	 *
	 * We redirect users to our custom login URL,
	 * so we can add an error message.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Error $errors The error object.
	 * @return void
	 */
	public function maybe_handle_password_reset_errors($errors) {

		if ($errors->has_errors()) {

			$url = add_query_arg(array(
				'action'     => wu_request('action', ''),
				'user_login' => wu_request('user_login', ''),
				'error'      => $errors->get_error_code(),
			), wp_login_url());

			wp_redirect($url);

			exit;

		} // end if;

	} // end maybe_handle_password_reset_errors;

	/**
	 * Maybe redirects users to the confirm screen.
	 *
	 * If we are successful in resetting a password,
	 * we need to prevent the user from reaching the empty
	 * wp-login.php message, so we redirect them to the passed
	 * redirect_to query argument.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_redirect_to_confirm_screen() {

		if (wu_request('redirect_to')) {

			wp_redirect(wu_request('redirect_to'));

			exit;

		} // end if;

	} // end maybe_redirect_to_confirm_screen;

	/**
	 * Replace the reset password link, if necessary.
	 *
	 * @since 2.0.0
	 *
	 * @param string $message The email message.
	 * @param string $key The reset key.
	 * @param string $user_login The user login.
	 * @param array  $user_data The user data array.
	 * @return string
	 */
	public function replace_reset_password_link($message, $key, $user_login, $user_data) {

		if (!is_main_site()) {

			return $message;

		} // end if;

		$results = array();

		preg_match_all('/.*\/wp-login\.php.*/', $message, $results);

		if (isset($results[0][0])) {

			// Localize password reset message content for user.
			$locale = get_user_locale($user_data);

			$switched_locale = switch_to_locale($locale);

			$new_url = add_query_arg(array(
				'action'  => 'rp',
				'key'     => $key,
				'login'   => rawurlencode($user_login),
				'wp_lang' => $locale
			), wp_login_url());

			$new_url = set_url_scheme($new_url, null);

			$message = str_replace($results[0], $new_url, $message);

		} // end if;

		if ($switched_locale) {

			restore_previous_locale();

		} // end if;

		return $message;

	} // end replace_reset_password_link;

	/**
	 * Redirect logged users when they reach the login page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_redirect_to_admin_panel() {

		global $post;

		if (!is_user_logged_in()) {

			return;

		} // end if;

		$custom_login_page = $this->get_signup_page('login');

		if (empty($custom_login_page) || empty($post)) {

			return;

		} // end if;

		if ($custom_login_page->ID !== $post->ID) {

			return;

		} // end if;

		/**
		 * Create an exclusion list of parameters that prevent the auto-redirect.
		 *
		 * This is needed because otherwise page builder won't be able to
		 * edit the login page once it is defined.
		 *
		 * @since 2.0.4
		 * @return array
		 */
		$exclusion_list = apply_filters('wu_maybe_redirect_to_admin_panel_exclusion_list', array(
			'preview',           // WordPress Preview
			'ct_builder',        // Oxygen Builder
			'fl_builder',        // Beaver Builder
			'elementor-preview', // Elementor
			'brizy-edit',        // Brizy
			'brizy-edit-iframe', // Brizy
		), $custom_login_page, $post, $this);

		foreach ($exclusion_list as $exclusion_param) {

			if (wu_request($exclusion_param, null) !== null) {

				return;

			} // end if;

		} // end foreach;

		$user = wp_get_current_user();

		$active_blog = get_active_blog_for_user($user->ID);

		$redirect_to = $active_blog ? get_admin_url($active_blog->blog_id) : false;

		if (is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin($user->ID)) {

			$redirect_to = user_admin_url();

		} elseif (is_multisite() && !$user->has_cap('read')) {

			$redirect_to = get_dashboard_url($user->ID);

		} elseif (!$user->has_cap('edit_posts')) {

			$redirect_to = $user->has_cap('read') ? admin_url('profile.php') : home_url();

		} // end if;

		if (!$redirect_to) {

			return;

		} // end if;

		wp_redirect($redirect_to);

		exit;

	} // end maybe_redirect_to_admin_panel;

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

			$message .= sprintf('<br><a href="#" class="wu-resend-verification-email wu-text-gray-700">%s</a>', __('Resend verification email &rarr;', 'wp-ultimo'));

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

		if (!$use_custom_login) {

			return;

		} // end if;

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {

			return;

		} // end if;

		if (wu_request('interim-login')) {

			return;

		} // end if;

		if (wu_request('action') === 'logout') {

			return;

		} // end if;

		$new_login_url = $this->get_page_url('login');

		if (!$new_login_url) {

			return;

		} // end if;

		$should_obfuscate = wu_get_setting('obfuscate_original_login_url', 1);

		$bypass_obfuscation = wu_request('wu_bypass_obfuscation');

		if ($should_obfuscate && !$bypass_obfuscation) {

			status_header(404);

			nocache_headers();

			global $wp_query;

			$wp_query->set_404();

			include(get_404_template());

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
	public function filter_login_url($login_url, $redirect, $force_reauth = false) {

		$function_caller = wu_get_function_caller(5);

		if ($function_caller === 'wp_auth_check_html') {

			return $login_url;

		} // end if;

		$params = array();

		$old_url_params = wp_parse_url($login_url, PHP_URL_QUERY);

		wp_parse_str($old_url_params, $params);

		$new_login_url = $this->get_page_url('login');

		if (!$new_login_url) {

			return $login_url;

		} // end if;

		if ($redirect) {

			$new_login_url = add_query_arg('redirect_to', $redirect, $new_login_url);

		} // end if;

		if ($force_reauth) {

			$new_login_url = add_query_arg('reauth', 1, $new_login_url);

		} // end if;

		if ($params) {

			$new_login_url = add_query_arg($params, $new_login_url);

		} // end if;

		return $new_login_url;

	} // end filter_login_url;

	/**
	 * Returns the ID of the pages being used for each WP Ultimo purpose.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_signup_pages() {

		return array(
			'register'       => wu_get_setting('default_registration_page', false),
			'login'          => wu_get_setting('default_login_page', false),
			'block_frontend' => wu_get_setting('default_block_frontend_page', false),
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
			'register'       => __('WP Ultimo - Register Page', 'wp-ultimo'),
			'login'          => __('WP Ultimo - Login Page', 'wp-ultimo'),
			'block_frontend' => __('WP Ultimo - Site Blocked Page', 'wp-ultimo'),
		);

		$pages = array_map('absint', $this->get_signup_pages());

		if (in_array($post->ID, $pages, true)) {

			$key = array_search($post->ID, $pages, true);

			$states['wp_ultimo_page'] = $labels[$key];

		} // end if;

		return $states;

	} // end add_wp_ultimo_status_annotation;

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
			'errors'     => Checkout::get_instance()->errors,
			'membership' => wu_get_membership_by_hash(wu_request('membership')),
		));

	} // end render_confirmation_page;

} // end class Checkout_Pages;
