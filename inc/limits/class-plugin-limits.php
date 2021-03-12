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
	 * Runs on the first and only instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('ms_loaded', array($this, 'load_limitations'));

	} // end init;

	/**
	 * Apply limitations if they are available.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_limitations() {

		if (wu_get_current_site()->has_limitations()) {

			add_filter('site_option_active_sitewide_plugins', array($this, 'deactivate_plugins'));

			add_filter('option_active_plugins', array($this, 'deactivate_plugins'));

			add_filter('all_plugins', array($this, 'deactivate_plugins'));

			add_filter('the_content', array($this, 'clean_unused_shortcodes'), 9999);

		} // end if;

	} // end load_limitations;

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

		foreach ($plugins as $plugin_slug => $key) {

			if (strpos($plugin_slug, 'wp-ultimo') !== false) {

				continue;

			} // end if;

			if (!wu_get_current_site()->is_plugin_allowed($plugin_slug)) {

				unset($plugins[$plugin_slug]);

			} // end if;

		} // end foreach;

		// Ensure get_plugins function is loaded.
		if (!function_exists('get_plugins')) {

			include ABSPATH . '/wp-admin/includes/plugin.php';

		} // end if;

		$other_plugins = get_plugins();

		foreach ($other_plugins as $plugin_path => $other_plugin) {

			$behavior = wu_get_current_site()->get_limitations()->get_plugin_behavior($plugin_path);

			if ($behavior === 'active' || $behavior === 'force_activation') {

				$plugins[$plugin_path] = 1;

			} // end if;

		} // end foreach;

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
