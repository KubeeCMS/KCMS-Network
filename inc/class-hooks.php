<?php
/**
 * WP Ultimo activation and deactivation hooks
 *
 * @package WP_Ultimo
 * @subpackage Hooks
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
class Hooks {

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

		/**
	 * Runs on WP Ultimo activation
	 */
		register_activation_hook(WP_ULTIMO_PLUGIN_FILE, array('WP_Ultimo\Hooks', 'on_activation'));

		/**
		 * Runs on WP Ultimo deactivation
		 */
		register_deactivation_hook(WP_ULTIMO_PLUGIN_FILE, array('WP_Ultimo\Hooks', 'on_deactivation'));

		/**
		 * Runs the activation hook.
		 */
		add_action('plugins_loaded', array('WP_Ultimo\Hooks', 'on_activation_do'), 1);

	} // end init;

	/**
	 *  Runs when WP Ultimo is activated
	 *
	 * @since 1.9.6 It now uses hook-based approach, it is up to each sub-class to attach their own routines.
	 * @since 1.2.0
	 */
	public static function on_activation() {

		wu_log_add('wp-ultimo-core', __('Activating WP Ultimo...', 'wp-ultimo'));

		/*
		 * Set the activation flag
		 */
		update_network_option(null, 'wu_activation', 'yes');

	} // end on_activation;

	/**
	 * Runs whenever the activation flag is set.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function on_activation_do() {

		if (get_network_option(null, 'wu_activation') === 'yes' && wu_request('activate')) {

			// Removes the flag
			delete_network_option(null, 'wu_activation');

			/*
			 * Update the sunrise meta file.
			 */
			\WP_Ultimo\Sunrise::maybe_tap('activating');

			/**
			 * Let other parts of the plugin attach their routines for activation
			 *
			 * @since 1.9.6
			 * @return void
			 */
			do_action('wu_activation');

		} // end if;

	} // end on_activation_do;

	/**
	 * Runs when WP Ultimo is deactivated
	 *
	 * @since 1.9.6 It now uses hook-based approach, it is up to each sub-class to attach their own routines.
	 * @since 1.2.0
	 */
	public static function on_deactivation() {

		wu_log_add('wp-ultimo-core', __('Deactivating WP Ultimo...', 'wp-ultimo'));

		/*
		 * Update the sunrise meta file.
		 */
		\WP_Ultimo\Sunrise::maybe_tap('deactivating');

		/**
		 * Let other parts of the plugin attach their routines for deactivation
		 *
		 * @since 1.9.6
		 * @return void
		 */
		do_action('wu_deactivation');

	} // end on_deactivation;

} // end class Hooks;
