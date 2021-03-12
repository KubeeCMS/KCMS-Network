<?php
/**
 * WP Ultimo My Sites Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages\Customer_Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Admin_Pages\Base_Customer_Facing_Admin_Page;

/**
 * WP Ultimo My Sites Admin Page.
 */
class My_Sites_Admin_Page extends Base_Customer_Facing_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'sites';

	/**
	 * Menu position. This is only used for top-level menus
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $position = 101010101;

	/**
	 * Dashicon to be used on the menu item. This is only used on top-level menus
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $menu_icon = 'dashicons-wu-browser';

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
		'admin_menu'      => 'exist',
		'user_admin_menu' => 'exist',
	);

	/**
	 * The current customer instance.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Models\Customer
	 */
	protected $customer;

	/**
	 * Checks if we need to add this page.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->current_site = wu_get_current_site();

		$this->current_membership = $this->current_site->get_membership();

		if ($this->current_site->get_type() === 'customer_owned') {

			parent::__construct();

			add_action('admin_menu', array($this, 'unset_default_my_sites_menu'));

			add_action('admin_bar_menu', array($this, 'change_my_sites_link'), 90);

			add_action('current_screen', array($this, 'force_screen_options'));

		} // end if;

	} // end __construct;

	/**
	 * Loads the current site and membership.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function page_loaded() {

		$this->customer = wu_get_current_customer();

	} // end page_loaded;

	/**
	 * Allow child classes to add hooks to be run once the page is loaded.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
	 * @since 1.8.2
	 * @return void
	 */
	public function hooks() {} // end hooks;

	/**
	 * Remove the default my sites link.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function unset_default_my_sites_menu() {

		global $submenu;

		unset($submenu['index.php'][5]);

	} // end unset_default_my_sites_menu;

	/**
	 * Update the my sites link on the top-bar.
	 *
	 * @since 2.0.0
	 *
	 * @param object $wp_admin_bar The admin bar object.
	 * @return void
	 */
	public function change_my_sites_link($wp_admin_bar) {

		$my_sites = $wp_admin_bar->get_node('my-sites');

		$args = array(
			'page' => 'sites',
		);

		$my_sites->href = add_query_arg($args, admin_url('admin.php'));

		$wp_admin_bar->add_node($my_sites);

	} // end change_my_sites_link;

	/**
	 * Force the screen options so our customize options show up.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function force_screen_options() {

		if (get_current_screen()->id !== 'toplevel_page_sites') {

			return;

		} // end if;

		// Forces Screen options so we can add our links.
		add_screen_option('wu_fix', array(
			'option' => 'test',
			'value'  => true,
		));

	} // end force_screen_options;

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
	public function register_widgets() {

		\WP_Ultimo\UI\Login_Form_Element::get_instance()->as_inline_content(get_current_screen()->id, 'wu_dash_before_metaboxes');

		\WP_Ultimo\UI\Simple_Text_Element::get_instance()->as_inline_content(get_current_screen()->id, 'wu_dash_before_metaboxes');

		\WP_Ultimo\UI\My_Sites_Element::get_instance()->as_inline_content(get_current_screen()->id, 'wu_dash_before_metaboxes');

	} // end register_widgets;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('My Sites', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('My Sites', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('My Sites', 'wp-ultimo');

	} // end get_submenu_title;

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output() {
		/*
		 * Renders the base edit page layout, with the columns and everything else =)
		 */
		wu_get_template('base/dash', array(
			'screen'            => get_current_screen(),
			'page'              => $this,
			'has_full_position' => false,
		));

	} // end output;

} // end class My_Sites_Admin_Page;
