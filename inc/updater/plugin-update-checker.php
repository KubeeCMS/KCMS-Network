<?php
/**
 * Silence is golden;
 *
 * Here for backwards compatibility issues.
 *
 * @package Compat
 * @deprecated 2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

if (!class_exists('PluginUpdateChecker_2_0')) {

	/**
	 * Deprecated: PluginUpdateChecker_2_0
	 *
	 * @deprecated 2.0.0
	 */
	class PluginUpdateChecker_2_0 { // phpcs:ignore

		/**
		 * Deprecated: enable_update_checker
		 *
		 * @deprecated 2.0.0
		 * @return void
		 */
		public function enable_update_checker() {} // end enable_update_checker;

	} // end class PluginUpdateChecker_2_0;

} // end if;

if (!class_exists('PucFactory')) {

	/**
	 * Deprecated: PucFactory
	 *
	 * @deprecated 2.0.0
	 */
	class PucFactory { // phpcs:ignore

		/**
		 * Deprecated: buildUpdateChecker
		 *
		 * @deprecated 2.0.0
		 * @return void
		 */
		public static function buildUpdateChecker() {} // end buildUpdateChecker;

	} // end class PucFactory;

} // end if;
