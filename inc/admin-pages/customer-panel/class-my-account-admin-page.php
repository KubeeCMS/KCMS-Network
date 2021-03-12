<?php
/**
 * WP Ultimo My_Account Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages\Customer_Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Admin_Pages\Base_Admin_Page;

/**
 * WP Ultimo My_Account Admin Page.
 */
class My_Account_Admin_Page extends Base_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'account';

	/**
	 * Menu position. This is only used for top-level menus
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $position = 10101010;

	/**
	 * Dashicon to be used on the menu item. This is only used on top-level menus
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $menu_icon = 'dashicons-wu-email';

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
		'user_admin_menu' => 'exist',
	);

	/**
	 * Allow child classes to add further initializations.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function init() {

		add_action('admin_bar_menu', array($this, 'add_account_overview_item'));

	} // end init;

	/**
	 * Adds the network name and logo to the admin bar.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The admin bar class.
	 * @return void
	 */
	public function add_account_overview_item($wp_admin_bar) {

		$args = array (
			'id'    => 'wu-account-overview',
			'title' => '<span class="dashicons-wu-compass" style="font-size: 16px; color: #9ea3a8; display: inline; float: left; height: 27px; margin-right: 6px; line-height: 2;"></span>' . __('Account Overview', 'wp-ultimo'),
			'href'  => user_admin_url(),
		);

		$wp_admin_bar->add_node($args);

	} // end add_account_overview_item;

	/**
	 * Allow child classes to add further initializations, but only after the page is loaded.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function page_loaded() {}  // end page_loaded;

	/**
	 * Allow child classes to add hooks to be run once the page is loaded.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
	 * @since 1.8.2
	 * @return void
	 */
	public function hooks() {} // end hooks;

	/**
	 * Allow child classes to add screen options; Useful for pages that have list tables.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function screen_options() {} // end screen_options;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {} // end register_widgets;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('My Account', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('My Account', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('My Account', 'wp-ultimo');

	} // end get_submenu_title;

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output() {

		echo "<div class='wrap'><h1>My Account</h1></div>";

	} // end output;

} // end class My_Account_Admin_Page;
