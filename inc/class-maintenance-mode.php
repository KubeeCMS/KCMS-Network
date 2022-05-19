<?php
/**
 * Adds the Maintenance Mode.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Maintenance Mode.
 *
 * @since 2.0.0
 */
class Maintenance_Mode {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Initializes
	 *
	 * @since 2.0.0
	 */
	public function init() {

		add_action('wp_ultimo_load', array($this, 'add_settings'));

		if (wu_get_setting('maintenance_mode')) {

			$this->hooks();

		} // end if;

	} // end init;

	/**
	 * Adds the additional hooks, when necessary.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks() {

		add_action('wu_ajax_toggle_maintenance_mode', array($this, 'toggle_maintenance_mode'));

		if (!is_main_site()) {

			add_action('admin_bar_menu', array($this, 'add_notice_to_admin_bar'), 15);

		} // end if;

		if (self::check_maintenance_mode()) {

			add_filter('pre_option_blog_public', '__return_true');

			if (!is_admin()) {

				add_action('wp', array($this, 'render_page'));

				if (function_exists('wp_robots_no_robots')) {

					add_filter('wp_robots', 'wp_robots_no_robots'); // WordPress 5.7+

				} else {

					add_action('wp_head', 'wp_no_robots', 20);

				} // end if;

			} // end if;

		} // end if;

	} // end hooks;

	/**
	 * Add maintenance mode Notice to Admin Bar
	 *
	 * @since 2.0.0
	 * @param WP_Admin_Bar $wp_admin_bar The Admin Bar class.
	 * @return void
	 */
	public function add_notice_to_admin_bar($wp_admin_bar) {

		if (!current_user_can('manage_options')) {

			return;

		} // end if;

		if (is_admin() || self::check_maintenance_mode()) {

			$args = array(
				'id'     => 'wu-maintenance-mode',
				'parent' => 'top-secondary',
				'title'  => __('Maintenance Mode - Active', 'wp-ultimo'),
				'href'   => '#wp-ultimo-site-maintenance-element',
				'meta'   => array(
					'class' => 'wu-maintenance-mode ' . (self::check_maintenance_mode() ? '' : 'hidden'),
					'title' => __('This means that your site is not available for visitors at the moment. Only you and other logged users have access to it. Click here to toggle this option.', 'wp-ultimo'),
				),
			);

			$wp_admin_bar->add_node($args);

		} // end if;

	} // end add_notice_to_admin_bar;

	/**
	 * Render page - html filtrable
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_page() {

		if (is_main_site() || current_user_can('read')) {

			return;

		} // end if;

		$text = apply_filters(
			'wu_maintenance_mode_text',
			__('Website under planned maintenance. Please check back later.', 'wp-ultimo')
		);

		$title = apply_filters(
			'wu_maintenance_mode_title',
			__('Under Maintenance', 'wp-ultimo')
		);

		wp_die($text, $title);

	} // end render_page;

	/**
	 * Check should display maintenance mode
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public static function check_maintenance_mode() {

		return get_site_meta(get_current_blog_id(), 'wu_maintenance_mode', true);

	} // end check_maintenance_mode;

	/**
	 * Callback button admin toggle maintenance mode.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function toggle_maintenance_mode() {

		check_ajax_referer('wu_toggle_maintenance_mode', $_POST['_wpnonce']);

		$site_id = \WP_Ultimo\Helpers\Hash::decode(wu_request('site_hash'), 'site');

		if (!current_user_can_for_blog($site_id, 'manage_options')) {

			return wp_send_json_error(array(
				'message' => __('You do not have the necessary permissions to perform this option.', 'wp-ultimo'),
				'value'   => false,
			));

		} // end if;

		$value = wu_request('maintenance_status', false);

		$value = wu_string_to_bool($value);

		update_site_meta($site_id, 'wu_maintenance_mode', $value);

		$return = array(
			'message' => __('New maintenance settings saved.', 'wp-ultimo'),
			'value'   => $value,
		);

		wp_send_json_success($return);

	} // end toggle_maintenance_mode;

	/**
	 * Filter the WP Ultimo settings to add Jumper options
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_settings() {

		wu_register_settings_field('sites', 'maintenance_mode', array(
			'title'   => __('Site Maintenance Mode', 'wp-ultimo'),
			'desc'    => __('Allow your customers and super admins to quickly take sites offline via a toggle on the site dashboard.', 'wp-ultimo'),
			'type'    => 'toggle',
			'default' => 0,
			'order'   => 23,
		));

	} // end add_settings;

} // end class Maintenance_Mode;
