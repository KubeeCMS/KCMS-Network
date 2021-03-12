<?php
/**
 * Admin Themes Compatibility.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Themes_Compatibility
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a Admin Themes Compatibility for WP Ultimo.
 *
 * @since 1.9.14
 */
class Admin_Themes_Compatibility {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Sets up the listeners.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		add_filter('admin_body_class', array($this, 'add_body_classes'));

	} // end __construct;

	/**
	 * Body tag classes. Fired by `body_class` filter.
	 *
	 * @since 2.0.0
	 *
	 * @param array $classes Body Classes.
	 * @return array
	 */
	public function add_body_classes($classes) {

		$prefix = 'wu-compat-admin-theme-';

		foreach (self::get_admin_themes() as $key => $value) {

			if ($value['activated']) {

				$classes .= ' ' . $prefix . $key . ' ';

			} // end if;

		} // end foreach;

		return $classes;

	} // end add_body_classes;

	/**
	 * Get list of Admin Themes
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_admin_themes() {

		return apply_filters('wu_admin_themes_compatibility', array(
			'material-wp' => array(
				'activated' => class_exists('MaterialWP'),
			),
			'pro-theme'   => array(
				'activated' => class_exists('PROTheme'),
			),
			'admin-2020'  => array(
				'activated' => function_exists('run_admin_2020'),
			),
			'clientside'  => array(
				'activated' => class_exists('Clientside'),
			),
			'wphave'     => array(
				'activated' => class_exists('wphave_admin'),
			),
			'waaspro'     => array(
				'activated' => class_exists('AdminUIPRO') || class_exists('AdminUIPROflat'),
			),
		));

	} // end get_admin_themes;

} // end class Admin_Themes_Compatibility;
