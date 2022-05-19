<?php
/**
 * Adds the Toolbox UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use WP_Ultimo\Logger;
use WP_Ultimo\UI\Base_Element;
use WP_Ultimo\License;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Toolbox UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Toolbox {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Element construct.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		add_action('init', array($this, 'load_toolbox'));

	} // end __construct;

	/**
	 * Checks if we should add the toolbox or not.
	 *
	 * @todo fix the check for license activation.
	 * @since 2.0.0
	 * @return boolean
	 */
	protected function is_toolbox_enabled() {

		$can_see_toolbox = current_user_can('manage_network');

		if (class_exists('\user_switching') && !$can_see_toolbox) {

			$old_user = \user_switching::get_old_user();

			$can_see_toolbox = user_can($old_user, 'manage_network');

		} // end if;

		return apply_filters('wu_is_toolbox_enabled', wu_get_setting('enable_jumper', true) && License::get_instance()->allowed() && $can_see_toolbox && !is_network_admin());

	} // end is_toolbox_enabled;

	/**
	 * Loads the necessary elements to display the Toolbox.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_toolbox() {

		if ($this->is_toolbox_enabled()) {

			add_action('wp_footer', array($this, 'output'));

			add_action('admin_footer', array($this, 'output'));

			add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

		} // end if;

	} // end load_toolbox;

	/**
	 * Adds the admin styles to make sure the tooltip renders.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_styles() {

		wp_enqueue_style('wu-admin');

	} // end enqueue_styles;

	/**
	 * Outputs the actual HTML markup of the Toolbox.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output() {

		$current_site = wu_get_current_site();

		wu_get_template('ui/toolbox', array(
			'toolbox'      => $this,
			'current_site' => $current_site,
			'customer'     => $current_site ? $current_site->get_customer() : false,
			'membership'   => $current_site ? $current_site->get_membership() : false,
		));

	} // end output;

} // end class Toolbox;
