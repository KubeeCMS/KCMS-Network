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
		 * Only activate Freemius when absolutely necessary.
		 */
		if (!$this->check_request()) {

			return;

		} // end if;

		$this->setup_activator();

	} // end init;

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

		if (!(is_main_site()) && !is_network_admin()) {

			return;

		} // end if;

		if ($this->activator === null) {

			// Activate multisite network integration.
			if (!defined('WP_FS__PRODUCT_2963_MULTISITE')) {

				define('WP_FS__PRODUCT_2963_MULTISITE', true); // phpcs:ignore

			} // end if;

			require_once WP_ULTIMO_PLUGIN_DIR . '/dependencies/freemius/wordpress-sdk/start.php';

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
	 * @return FS_User|false
	 */
	public function get_customer() {

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
	 * @return FS_Plugin_License|false
	 */
	public function get_license() {

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
	 * @return string|false
	 */
	public function get_license_key() {

		$license = $this->get_license();

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
