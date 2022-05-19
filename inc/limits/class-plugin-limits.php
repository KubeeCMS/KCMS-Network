<?php
/**
 * Handles limitations to post types, uploads and more.
 *
 * @package WP_Ultimo
 * @subpackage Limits
 * @since 2.0.0
 */

namespace WP_Ultimo\Limits;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles limitations to post types, uploads and more.
 *
 * @since 2.0.0
 */
class Plugin_Limits {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Site-level plugins cache.
	 *
	 * @since 2.0.0
	 * @var null|array
	 */
	protected $plugins = null;

	/**
	 * Network plugins cache.
	 *
	 * @since 2.0.0
	 * @var null|array
	 */
	protected $network_plugins = null;

	/**
	 * Runs on the first and only instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_sunrise_loaded', array($this, 'load_limitations'));

	} // end init;

	/**
	 * Apply limitations if they are available.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_limitations() {

		if (wu_get_current_site()->has_limitations()) {

			add_filter('site_option_active_sitewide_plugins', array($this, 'deactivate_network_plugins'));

			add_filter('option_active_plugins', array($this, 'deactivate_plugins'));

			add_filter('all_plugins', array($this, 'clear_plugin_list'));

			add_filter('the_content', array($this, 'clean_unused_shortcodes'), 9999);

			add_filter('plugin_action_links', array($this, 'clear_actions'), -2000, 2);

			add_filter('show_network_active_plugins', '__return_true');

			add_action('load-plugins.php', array($this, 'admin_page_hooks'));

		} // end if;

		add_action('wu_site_post_save', array($this, 'activate_and_inactive_plugins'), 10, 3);

		add_action('wu_checkout_done', array($this, 'maybe_activate_and_inactive_plugins'), 10, 5);

	} // end load_limitations;

	/**
	 * Registers scripts onto the plugins page.
	 *
	 * @since 2.0.5
	 * @return void
	 */
	public function admin_page_hooks() {

		add_action('admin_enqueue_scripts', 'add_wubox');

	} // end admin_page_hooks;

	/**
	 * Automatically activate and deactivate plugins when the site is created or a upgrade happens.
	 *
	 * @since 2.0.0
	 *
	 * @param array                  $data Saved data.
	 * @param \WP_Ultimo\Models\Site $site_object The site created.
	 * @param bool                   $new If this site is a new one.
	 * @return void
	 */
	public function activate_and_inactive_plugins($data, $site_object, $new) {

		if ($site_object && $new) {

			$site_object->sync_plugins();

		} // end if;

	} // end activate_and_inactive_plugins;

	/**
	 * Activate and Deactivate plugins on upgrades and downgrades.
	 *
	 * @since 2.0.5
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment The payment object.
	 * @param \WP_Ultimo\Models\Membership $membership The membership object.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer object.
	 * @param \WP_Ultimo\Checkout\Cart     $cart The cart object.
	 * @param string                       $type The cart type.
	 * @return void
	 */
	public function maybe_activate_and_inactive_plugins($payment, $membership, $customer, $cart, $type) {

		if ($type !== 'new' && $membership) {

			$membership->sync_plugins();

		} // end if;

	} // end maybe_activate_and_inactive_plugins;

	/**
	 * Clear the actions of the plugins list table.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $actions The list of plugin actions.
	 * @param string $plugin_file The plugin path/slug.
	 * @return array
	 */
	public function clear_actions($actions, $plugin_file) {

		if (!function_exists('wu_generate_upgrade_to_unlock_url')) {

			return $actions;

		} // end if;

		$upgrade = sprintf(
			'<a href="%s" class="wubox wu-styling" title="%s"><span class="dashicons-wu-lock1 wu-mr-1"></span>%s</a>',
			wu_generate_upgrade_to_unlock_url(array(
				'module' => 'plugins',
				'type'   => $plugin_file,
			)),
			__('Upgrade to unlock', 'wp-ultimo'),
			__('Upgrade to unlock', 'wp-ultimo')
		);

		$plugin_limits = wu_get_current_site()->get_limitations()->plugins;

		if (isset($actions['network_active'])) {

			$actions['network_active'] = sprintf('<span class="wu-styling">
				<span class="wu-text-green-600">
					<span class="dashicons-wu-flash wu-align-text-bottom"></span>%s
				</span>
			</span>', __('Always Loaded', 'wp-ultimo'));

		} // end if;

		if ($plugin_limits->allowed($plugin_file, 'force_active_locked')) {

			unset($actions['deactivate']);

		} elseif ($plugin_limits->allowed($plugin_file, 'force_inactive_locked')) {

			$actions['upgrade'] = $upgrade;

			unset($actions['activate']);

		} // end if;

		return $actions;

	} // end clear_actions;

	/**
	 * Clears the plugin list.
	 *
	 * This method is responsible for controlling
	 * plugin visibility on the plugins list table.
	 *
	 * @since 2.0.0
	 *
	 * @param array $plugins The original list of plugins.
	 * @return array
	 */
	public function clear_plugin_list($plugins) {

		if (is_main_site()) {

			return $plugins;

		} // end if;

		$plugin_limits = wu_get_current_site()->get_limitations()->plugins;

		foreach ($plugins as $plugin_slug => $plugin_data) {

			if ($plugin_data['Network']) {

				unset($plugins[$plugin_slug]);

			} // end if;

			if (strpos($plugin_slug, 'wp-ultimo') === 0) {

				unset($plugins[$plugin_slug]);

			} // end if;

			if ($plugin_limits->allowed($plugin_slug, 'hidden')) {

				unset($plugins[$plugin_slug]);

			} // end if;

		} // end foreach;

		return $plugins;

	} // end clear_plugin_list;

	/**
	 * Deactivates the network plugins that people are not allowed to use.
	 *
	 * We need different methods because keys are different network wide and on the sub-site level.
	 *
	 * @since 2.0.0
	 *
	 * @param array $plugins Array with the plugins activated.
	 * @return array
	 */
	public function deactivate_network_plugins($plugins) {
		/*
		 * Bail on network admin =)
		 */
		if (is_network_admin() || is_main_site()) {

			return $plugins;

		} // end if;

		/*
		 * Get the network plugins cache, if they're set.
		 */
		if (is_array($this->network_plugins)) {

			return $this->network_plugins;

		} // end if;

		$plugin_limits = wu_get_current_site()->get_limitations()->plugins;

		foreach ($plugins as $plugin_slug => $timestamp) {

			if (strpos($plugin_slug, 'wp-ultimo') !== false) {

				continue;

			} // end if;

			if ($plugin_limits->allowed($plugin_slug, 'force_inactive_locked')) {

				unset($plugins[$plugin_slug]);

			} // end if;

		} // end foreach;

		// Ensure get_plugins function is loaded.
		if (!function_exists('get_plugins')) {

			include ABSPATH . '/wp-admin/includes/plugin.php';

		} // end if;

		$other_plugins = get_plugins();

		foreach ($other_plugins as $plugin_path => $other_plugin) {

			if ($plugin_limits->allowed($plugin_path, 'force_active_locked')) {

				$plugins[] = $plugin_path;

			} // end if;

		} // end foreach;

		$this->network_plugins = $plugins;

		return $plugins;

	} // end deactivate_network_plugins;

	/**
	 * Deactivates the plugins that people are not allowed to use.
	 *
	 * @since 2.0.0
	 *
	 * @param array $plugins Array with the plugins activated.
	 * @return array
	 */
	public function deactivate_plugins($plugins) {
		/*
		 * Bail on network admin =)
		 */
		if (is_network_admin() || is_main_site()) {

			return $plugins;

		} // end if;

		/*
		 * Get the site-level plugins cache, if they're set.
		 */
		if (is_array($this->plugins)) {

			return $this->plugins;

		} // end if;

		$plugin_limits = wu_get_current_site()->get_limitations()->plugins;

		foreach ($plugins as $plugin_slug) {

			if (strpos($plugin_slug, 'wp-ultimo') !== false) {

				continue;

			} // end if;

			if ($plugin_limits->allowed($plugin_slug, 'force_inactive_locked')) {

				$index = array_search($plugin_slug, $plugins, true);

				unset($plugins[$index]);

			} // end if;

		} // end foreach;

		// Ensure get_plugins function is loaded.
		if (!function_exists('get_plugins')) {

			include ABSPATH . '/wp-admin/includes/plugin.php';

		} // end if;

		$other_plugins = get_plugins();

		foreach ($other_plugins as $plugin_path => $other_plugin) {

			if ($plugin_limits->allowed($plugin_path, 'force_active_locked')) {

				$plugins[] = $plugin_path;

			} // end if;

		} // end foreach;

		$this->plugins = $plugins;

		return $plugins;

	} // end deactivate_plugins;

	/**
	 * Remove the unused shortcodes after we disable plugins.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content The post content.
	 * @return string
	 */
	public function clean_unused_shortcodes($content) {

		$content = preg_replace('/\[.+\].+\[\/.+\]/', '', $content);

		return $content;

	} // end clean_unused_shortcodes;

} // end class Plugin_Limits;
