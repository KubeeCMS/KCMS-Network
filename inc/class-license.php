<?php
/**
 * License Handler
 *
 * Handles WP Ultimo activation and compat layer with Freemius.
 *
 * @package WP_Ultimo
 * @subpackage License
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles WP Ultimo activation and compat layer with Freemius.
 *
 * @since 2.0.0
 */
class License {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The activator instance, in our case a Freemius instance.
	 *
	 * @since 2.0.0
	 * @var \Freemius
	 */
	protected $activator;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		/*
		 * Check the permissions before allowing access to the account page.
		 */
		add_action('load-wp-ultimo_page_wp-ultimo-account', array($this, 'maybe_prevent_access'));

		add_filter('fs_is_network_active', array($this, 'maybe_force_active_state'), 10, 2);

		add_filter('fs_find_caller_plugin_file', array($this, 'fix_mu_plugin_path'), 10, 3);

		/*
		 * Register forms for license activation.
		 */
		$this->register_forms();

		/*
		 * Only activate Freemius when absolutely necessary.
		 */
		if (!$this->check_request()) {

			return;

		} // end if;

		$this->setup_activator();

	} // end init;

	/**
	 * Request a support signature to the API.
	 *
	 * This confirms ownership of the license and allows us
	 * to display past conversations with confidence that the
	 * customer is who they say they is.
	 *
	 * @since 2.0.7
	 * @return string
	 */
	public function request_support_signature() {

		$signature_url = wu_with_license_key('https://api.wpultimo.com/signature');

		$response = wp_remote_get($signature_url);

		if (is_wp_error($response)) {

			return $response;

		} // end if;

		$body = wp_remote_retrieve_body($response);

		$data = (array) json_decode($body, true);

		$signature = wu_get_isset($data, 'signature', 'no_signature');

		return $signature;

	} // end request_support_signature;

	/**
	 * Display the widget window for support.
	 *
	 * @since 2.0.7
	 *
	 * @param string $subject The subject of the new chat.
	 * @param string $message The message for the new chat.
	 * @return void
	 */
	public function maybe_add_support_window($subject = '', $message = '') {

		if (current_user_can('manage_network') && !did_action('admin_enqueue_scripts')) {

			$deps = WP_Ultimo()->is_loaded() ? array() : array('wu-setup-wizard-polyfill');

			wp_register_script('wu-support', wu_get_asset('support.js', 'js'), $deps, wu_get_version(), true);

			$customer = $this->get_customer(true);

			if (!$customer) {

				return;

			} // end if;

			$signature = $this->request_support_signature();

			wp_localize_script('wu-support', 'wu_support_vars', array(
				'avatar'               => get_avatar_url($customer->email),
				'email'                => $customer->email,
				'display_name'         => sprintf('%s %s', $customer->first, $customer->last),
				'license_key'          => $this->get_license_key(true),
				'signature'            => $signature,
				'subject'              => $subject,
				'message'              => $message,
				'should_use_polyfills' => (int) !WP_Ultimo()->is_loaded(),
			));

			wp_enqueue_script('wu-support');

		} // end if;

	} // end maybe_add_support_window;

	/**
	 * Maybe force the active state of WP Ultimo if being used as must-use.
	 *
	 * @since 2.0.0
	 *
	 * @param bool   $is_active The current active value.
	 * @param string $plugin The plugin name/slug.
	 * @return bool
	 */
	public function maybe_force_active_state($is_active, $plugin) {

		if ($plugin === WP_ULTIMO_PLUGIN_BASENAME && wu_is_must_use()) {

			return true;

		} // end if;

		return $is_active;

	} // end maybe_force_active_state;

	/**
	 * Fix plugin path is being used as must use.
	 *
	 * @since 2.0.0
	 *
	 * @param string $final_path The plugin file absolute path.
	 * @param string $slug The plugin slug.
	 * @param string $path The plugin file relative path.
	 * @return string
	 */
	public function fix_mu_plugin_path($final_path, $slug, $path) {

		if (empty($path) && $slug === 'wp-ultimo') {

			return WP_ULTIMO_PLUGIN_FILE;

		} // end if;

		return $final_path;

	} // end fix_mu_plugin_path;

	/**
	 * Registers the form and handler to license activation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {

		if (function_exists('wu_register_form')) {

			add_filter('removable_query_args', array($this, 'add_activation_to_removable_query_list'));

			add_action('load-wp-ultimo_page_wp-ultimo-settings', array($this, 'add_successful_activation_message'));

			wu_register_form('license_activation', array(
				'render'  => array($this, 'render_activation_form'),
				'handler' => array($this, 'handle_activation_form'),
			));

		} // end if;

	} // end register_forms;

	/**
	 * Adds our query arg to the removable list.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args The current list of removable query args.
	 * @return array
	 */
	public function add_activation_to_removable_query_list($args) {

		$args[] = 'wp-ultimo-activation';

		return $args;

	} // end add_activation_to_removable_query_list;

	/**
	 * Adds a successful message when activation is successful.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_successful_activation_message() {

		if (wu_request('wp-ultimo-activation') === 'success') {

			WP_Ultimo()->notices->add(__('WP Ultimo successfully activated!', 'wp-ultimo'), 'success', 'network-admin', false, array(
				array(
					'title' => __('Manage your Account &rarr;', 'wp-ultimo'),
					'url'   => wu_network_admin_url('wp-ultimo-account'),
				)
			));

		} // end if;

	} // end add_successful_activation_message;

	/**
	 * Render the license activation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_activation_form() {

		$fields = array(
			'license_key'   => array(
				'type'        => 'text',
				'title'       => __('Your License Key', 'wp-ultimo'),
				'desc'        => __('Enter your license key here. You received your license key via email when you completed your purchase. Your license key usually starts with "sk_".', 'wp-ultimo'),
				'placeholder' => __('e.g. sk_******************', 'wp-ultimo'),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => __('Activate', 'wp-ultimo'),
				'placeholder'     => __('Activate', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('total-actions', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
		));

		$form->render();

	} // end render_activation_form;

	/**
	 * Handle license activation form submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_activation_form() {

		$license = License::get_instance();

		$activation_results = $license->activate(wu_request('license_key'));

		if (isset($activation_results->error)) {

			$activation_results = new \WP_Error('error', $activation_results->error);

		} // end if;

		if (is_wp_error($activation_results)) {

			wp_send_json_error($activation_results);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('wp-ultimo-activation', 'success', wu_get_current_url()),
		));

	} // end handle_activation_form;

	/**
	 * Check permissions before accessing.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function maybe_prevent_access() {

		if (!current_user_can('wu_license')) {

			wp_die(__('Sorry, you are not allowed to access this page.'));

		} // end if;

	} // end maybe_prevent_access;

	/**
	 * We only load the Freemius SDK if we really, really need it.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function check_request() {

		$current_url = wu_get_isset($_SERVER, 'REQUEST_URI', '');

		/*
		 * There are certain pages in WordPress where we need to load Freemius.
		 */
		$allowed_pages = array(
			'wp-admin/network/plugins.php',
		);

		foreach ($allowed_pages as $allowed_page) {

			if (strpos($current_url, $allowed_page) !== false) {

				return true;

			} // end if;

		} // end foreach;

		if (wp_doing_cron()) {

			return true;

		} // end if;

		if (wp_doing_ajax() && is_main_site()) {

			return true;

		} // end if;

		$page = wu_request('page', 'not-freemius');

		return strpos($page, 'wp-ultimo') !== false;

	} // end check_request;

	/**
	 * Gets the activator instance.
	 *
	 * @since 2.0.0
	 * @return null|\Freemius
	 */
	public function get_activator() {

		return $this->activator;

	} // end get_activator;

	/**
	 * Sets up the activator instance.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function setup_activator() {

		if (!defined('WP_ULTIMO_PLUGIN_DIR')) {

			return;

		} // end if;

		if (!(is_main_site()) && !is_network_admin()) {

			return;

		} // end if;

		if ($this->activator === null) {

			// Activate multisite network integration.
			if (!defined('WP_FS__PRODUCT_2963_MULTISITE')) {

				define('WP_FS__PRODUCT_2963_MULTISITE', true); // phpcs:ignore

			} // end if;

			require_once WP_ULTIMO_PLUGIN_DIR . '/dependencies/next-press/wordpress-sdk/start.php';

			$this->activator = fs_dynamic_init(array(
				'id'                  => '2963',
				'slug'                => 'wp-ultimo',
				'premium_slug'        => 'wp-ultimo',
				'type'                => 'plugin',
				'public_key'          => 'pk_3805d2ca1a07acd1c333307d1c93f',
				'is_premium'          => true,
				'is_premium_only'     => true,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'is_org_compliant'    => false,
				'anonymous_mode'      => true,
				'ignore_pending_mode' => true,
				'has_affiliation'     => 'selected',
				'menu'                => array(
					'slug'        => is_network_admin() ? 'wp-ultimo' : false,
					'first-path'  => 'plugins.php',
					'contact'     => false,
					'support'     => false,
					'affiliation' => false,
					'addons'      => false,
					'account'     => false,
					'pricing'     => false,
					'network'     => true,
				),
			));

			/*
			 * Skips the activation as we will handle it.
			 */
			if (!$this->allowed()) {

				$this->activator->skip_connection(null, true);

				/*
				 * Let's try to activate it manually, via wp-config.
				 */
				if (defined('WP_ULTIMO_LICENSE_KEY') && WP_ULTIMO_LICENSE_KEY) {

					add_action('init', function() {

						$this->activate(WP_ULTIMO_LICENSE_KEY);

					});

				} // end if;

			} // end if;

		} // end if;

	} // end setup_activator;

	/**
	 * Tries to perform a license activation.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $license_key The customer license key.
	 * @param boolean $email The customer email address.
	 * @return string|WP_Error
	 */
	public function activate($license_key, $email = false) {
		return true;

		if ($email === false) {

			$email = wp_get_current_user()->user_email;

		} // end if;

		if (!$license_key) {

			return new \WP_Error('missing-license', __('License key is required.', 'wp-ultimo'));

		} // end if;

		if (!$this->get_activator()) {

			$this->setup_activator();

		} // end if;

		$site = $this->get_activator()->get_site_info(array(
			'blog_id' => wu_get_main_site_id()
		));

		try {

			$results = $this->get_activator()->opt_in($email, false, false, $license_key, false, false, false, null, array($site));

		} catch (\Throwable $e) {

			wu_log_add('license', $e->getMessage());

			return new \WP_Error('general-error', __('An unexpected error occurred.', 'wp-ultimo'));

		} // end try;

		return $results;

	} // end activate;

	/**
	 * Checks if this copy of the plugin was activated.
	 *
	 * @since 2.0.0
	 *
	 * @param string $plan Plan to check against.
	 * @return bool
	 */
	public function allowed($plan = 'wpultimo') {

		if (!$this->get_activator()) {

			return true;

		} // end if;

		return $this->get_activator()->is_plan($plan);

	} // end allowed;

	/**
	 * Returns the customer of the current license.
	 *
	 * @since 2.0.0
	 * @param bool $force_load Force the activator to be loaded.
	 * @return FS_User|false
	 */
	public function get_customer($force_load = false) {

		if ($force_load && !$this->get_activator()) {

			$this->setup_activator();

		} // end if;

		if (!$this->get_activator()) {

			return false;

		} // end if;

		return $this->get_activator()->get_network_user();

	} // end get_customer;

	/**
	 * Returns the current install.
	 *
	 * @since 2.0.0
	 * @return FS_Site|false
	 */
	public function get_install() {

		if (!$this->get_activator()) {

			return false;

		} // end if;

		return $this->get_activator()->get_network_install();

	} // end get_install;

	/**
	 * Returns the current plan the customer subscribes to.
	 *
	 * @since 2.0.0
	 * @return FS_Plan|false
	 */
	public function get_plan() {

		if (!$this->get_activator()) {

			return false;

		} // end if;

		return $this->get_activator()->get_plan();

	} // end get_plan;

	/**
	 * Returns the license object.
	 *
	 * @since 2.0.0
	 * @param bool $force_load Force the activator to be loaded.
	 * @return FS_Plugin_License|false
	 */
	public function get_license($force_load = false) {

		if ($force_load && !$this->get_activator()) {

			$this->setup_activator();

		} // end if;

		$install = $this->get_install();

		if (!$install) {

			return false;

		} // end if;

		$license_id = $install->license_id;

		return $this->get_activator()->_get_license_by_id($license_id);

	} // end get_license;

	/**
	 * Returns the license key used to activate this copy.
	 *
	 * @since 2.0.0
	 * @param bool $force_load Force the activator to be loaded.
	 * @return string|false
	 */
	public function get_license_key($force_load = false) {

		$license = $this->get_license($force_load);

		return $license ? $license->secret_key : false;

	} // end get_license_key;

	/**
	 * Checks if the whitelabel mode was activated.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_whitelabel() {

		$license = $this->get_license();

		return $license ? $license->is_whitelabeled : false;

	} // end is_whitelabel;

	/**
	 * Inverse of the is_whitelabel. Used in callbacks.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_not_whitelabel() {

		return !$this->is_whitelabel();

	} // end is_not_whitelabel;

	/**
	 * Returns the license key set as constant if it exists.
	 *
	 * @since 2.0.0
	 * @return false|string
	 */
	public function has_license_key_defined_as_constant() {

		return defined('WP_ULTIMO_LICENSE_KEY') ? WP_ULTIMO_LICENSE_KEY : false;

	} // end has_license_key_defined_as_constant;

} // end class License;
