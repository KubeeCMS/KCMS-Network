<?php
/**
 * Light Ajax Implementation
 *
 * Helper class that mimics the Admin Ajax URL.
 * Based on the code found on: https://coderwall.com/p/of7y2q/faster-ajax-for-wordpress
 *
 * @package WP_Ultimo
 * @subpackage Light_Ajax
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a lighter ajax option to WP Ultimo.
 *
 * @since 1.9.14
 */
class Light_Ajax {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Sets up the listener
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		if (isset($_REQUEST['wu-ajax'])) {

			add_action('plugins_loaded', array($this, 'process_light_ajax'));

		} // end if;

	} // end __construct;

	/**
	 * Adds an wu_ajax handler.
	 *
	 * @since 1.9.14
	 * @return void
	 */
	public function process_light_ajax() {

		// mimic the actual admin-ajax
		define('DOING_AJAX', true); // phpcs:ignore

		if (!isset($_REQUEST['action'])) {

			die('-1');

		} // end if;

		// Typical headers
		header('Content-Type: text/html');

		send_nosniff_header();

		// Disable caching
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');

		$action = esc_attr(trim($_REQUEST['action']));

		if (is_user_logged_in()) {

			do_action('wu_ajax_' . $action); // phpcs:ignore

		} else {

			do_action('wu_ajax_nopriv_' . $action); // phpcs:ignore

		} // end if;

		die('1');

	} // end process_light_ajax;

} // end class Light_Ajax;
