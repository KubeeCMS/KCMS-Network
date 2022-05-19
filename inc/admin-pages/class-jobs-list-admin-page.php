<?php
/**
 * WP Ultimo Jobs Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Jobs Admin Page.
 */
class Jobs_List_Admin_Page extends Base_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-jobs';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * If this is a submenu, we need a parent menu to attach this to
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * Allows us to highlight another menu page, if this page has no parent page at all.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $highlight_menu_slug = 'wp-ultimo-settings';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * To add a page to the regular admin (wp-admin/), use: 'admin_menu' => 'capability_here'
	 * To add a page to the network admin (wp-admin/network), use: 'network_admin_menu' => 'capability_here'
	 * To add a page to the user (wp-admin/user) admin, use: 'user_admin_menu' => 'capability_here'
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $supported_panels = array(
		'network_admin_menu' => 'wu_read_jobs',
	);

	/**
	 * Overrides the init method to add additional hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		parent::init();

		add_filter('action_scheduler_admin_view_class', array($this, 'hide_as_admin_page'), 9999, 1);

	} // end init;

	/**
	 * Hide the Action Scheduler admin page on sub-sites.
	 *
	 * @since 2.0.0
	 *
	 * @param string $admin_view_class Admin View class name.
	 * @return string
	 */
	public function hide_as_admin_page($admin_view_class) {

		if (is_network_admin() || class_exists('WooCommerce')) {

			return $admin_view_class;

		} // end if;

		return '\WP_Ultimo\Compat\AS_Admin_View';

	} // end hide_as_admin_page;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Jobs', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Jobs', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Jobs', 'wp-ultimo');

	} // end get_submenu_title;

	/**
	 * Runs the hooks for the admin list table.
	 *
	 * Required for searched to work as intended.
	 *
	 * @since 2.0.10
	 * @return void
	 */
	public function page_loaded() {

		\ActionScheduler_AdminView::instance()->process_admin_ui();

	} // end page_loaded;

	/**
	 * Calls the Action Scheduler renderer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output() {

		\ActionScheduler_AdminView::instance()->render_admin_ui();

	} // end output;

} // end class Jobs_List_Admin_Page;
