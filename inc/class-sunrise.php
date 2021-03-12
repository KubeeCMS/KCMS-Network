<?php
/**
 * WP Ultimo activation and deactivation hooks
 *
 * @package WP_Ultimo
 * @subpackage Sunrise
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo activation and deactivation hooks
 *
 * @since 2.0.0
 */
class Sunrise {

	/**
	 * Keeps the current sunrise.php version.
	 *
	 * @var string
	 */
	static $version = '2.0.0';

	/**
	 * Static-only class.
	 */
	private function __construct() {} // end __construct;

	/**
	 * Register the activation and deactivation hooks
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function init() {

		if (!get_network_option(null, 'wu_setup_finished', false)) {

			return;

		} // end if;

		require_once dirname(__DIR__) . '/dependencies/autoload.php';

		require_once __DIR__ . '/deprecated/early-deprecated.php';

		require_once __DIR__ . '/class-autoloader.php';

		require_once __DIR__ . '/functions/helper.php';

		require_once __DIR__ . '/functions/site.php';

		/*
		 * Setup autoloader
		 */
		\WP_Ultimo\Autoloader::init();

		/*
		 * Primary Domain capabilities
		 */
		\WP_Ultimo\Domain_Mapping\Primary_Domain::get_instance();

		/*
		 * SSO capabilities
		 */
		\WP_Ultimo\Domain_Mapping\SSO::get_instance();

		/*
		 * Domain Mapping capabilities
		 *
		 * Note: The two previous classes, although related, need to be loaded first.
		 */
		\WP_Ultimo\Domain_Mapping::get_instance();

		/*
		 * Plugin Limits
		 */
		\WP_Ultimo\Limits\Plugin_Limits::get_instance();

		/*
		 * Theme Limits
		 */
		\WP_Ultimo\Limits\Theme_Limits::get_instance();

	} // end init;

	/**
	 * Checks if we need to upgrade the sunrise version on wp-content
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function manage_sunrise_updates() {
		/*
		 * Get current version of the sunrise.php file
		 */
		$old_version = defined('WPULTIMO_SUNRISE_VERSION') ? WPULTIMO_SUNRISE_VERSION : '0.0.1';

		if (version_compare($old_version, self::$version, '<')) {

			\WP_Ultimo\Sunrise::try_upgrade();

		} // end if;

	} // end manage_sunrise_updates;

	/**
	 * Upgrades the sunrise file, if necessary.
	 *
	 * @todo: lots of logic needs to be here to deal with other plugins' code on sunrise.php
	 * @since 2.0.0
	 * @return true|\WP_Error
	 */
	public static function try_upgrade() {

		try {

			$new_file = WP_PLUGIN_DIR . '/wp-ultimo/sunrise.php';

			$location = WP_CONTENT_DIR . '/sunrise.php';

			$copy_results = @copy($new_file, $location); // phpcs:ignore

			wu_log_add('sunrise', __('Sunrise upgrade attempt succeeded.', 'wp-ultimo'));

			return true;

		} catch (\Throwable $e) {

			wu_log_add('sunrise', __('Sunrise upgrade attempt failed.', 'wp-ultimo'));

			return new \WP_Error('error', __('Sunrise upgrade attempt failed.', 'wp-ultimo'));

		} // end try;

	} // end try_upgrade;

} // end class Sunrise;
