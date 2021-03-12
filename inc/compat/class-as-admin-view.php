<?php
/**
 * Replaces the AS Admin View page to hide it on sub-sites.
 *
 * @see inc/admin-pages/class-jobs-list-admin-page.php
 *
 * @package WP_Ultimo
 * @subpackage Compat
 * @since 2.0.0
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Replaces the admin view class with an empty shell.
 *
 * @since 2.0.0
 */
class AS_Admin_View {

	/**
	 * Empty initialization.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {} // end init;

} // end class AS_Admin_View;
