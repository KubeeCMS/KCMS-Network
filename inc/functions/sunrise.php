<?php
/**
 * Sunrise Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * General helper functions for sunrise.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Sunrise
 * @version     2.0.11
 */
function wu_should_load_sunrise() {

	return \WP_Ultimo\Sunrise::should_load_sunrise();

} // end wu_should_load_sunrise;

/**
 * Get a setting value, when te normal APIs are not available.
 *
 * Should only be used if we're running in sunrise.
 *
 * @since 2.0.0
 *
 * @param string $setting Setting to get.
 * @param mixed  $default Default value.
 * @return mixed
 */
function wu_get_setting_early($setting, $default = false) {

	if (did_action('wp_ultimo_load')) {

		_doing_it_wrong('wu_get_setting_early', __('Regular setting APIs are already available. You should use wu_get_setting() instead.', 'wp-ultimo'), '2.0.0');

	} // end if;

	$settings_key = \WP_Ultimo\Settings::KEY;

	$settings = get_network_option(null, 'wp-ultimo_' . $settings_key);

	return wu_get_isset($settings, $setting, $default);

} // end wu_get_setting_early;

/**
 * Load te dev tools, if they exist.
 *
 * @since 2.0.11
 *
 * @param boolean $load If we should load it or not.
 * @return string The path to the dev tools folder.
 */
function wu_load_dev_tools($load = true) {

	if (defined('WP_ULTIMO_SUNRISE_FILE')) {

    /*
     * If the vendor folder exists, we are
     * for sure, inside a dev environment.
     */
		$autoload_file = dirname(WP_ULTIMO_SUNRISE_FILE, 2) . '/vendor/autoload.php';

		if (file_exists($autoload_file)) {

			$load && require_once $autoload_file;

			return $autoload_file;

		} // end if;

	} // end if;

	return false;

} // end wu_has_dev_tools;
