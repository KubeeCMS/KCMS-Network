<?php
/**
 * Handles limitations to disk space
 *
 * @todo We need to move posts on downgrade.
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
class Disk_Space_Limits {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs on the first and only instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('init', array($this, 'setup'));

	} // end init;

	/**
	 * Sets up the hooks and checks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function setup() {

		/**
		 * Allow plugin developers to short-circuit the limitations.
		 *
		 * You can use this filter to run arbitrary code before any of the limits get initiated.
		 * If you filter returns any truthy value, the process will move on, if it returns any falsy value,
		 * the code will return and none of the hooks below will run.
		 *
		 * @since 1.7.0
		 * @return bool
		 */
		if (!apply_filters('wu_apply_plan_limits', wu_get_current_site()->has_limitations())) {

			return;

		} // end if;

		if (!wu_get_current_site()->has_module_limitation('disk_space')) {

			return;

		} // end if;

		add_filter('site_option_upload_space_check_disabled', '__return_zero');

		add_filter('get_space_allowed', array($this, 'apply_disk_space_limitations'));

	} // end setup;

	/**
	 * Changes the disk_space to the one on the product.
	 *
	 * @since 2.0.0
	 *
	 * @param string $disk_space The new disk space.
	 * @return int
	 */
	public function apply_disk_space_limitations($disk_space) {

		$modified_disk_space = wu_get_current_site()->get_limitations()->disk_space->get_limit();

		if (is_numeric($modified_disk_space)) {

			return $modified_disk_space;

		} // end if;

		return $disk_space;

	} // end apply_disk_space_limitations;

} // end class Disk_Space_Limits;
