<?php
/**
 * WP Ultimo Sites Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages\Customer_Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Admin_Pages\List_Admin_Page;

/**
 * WP Ultimo Sites Admin Page.
 */
class Sites_Admin_Page extends List_Admin_Page {

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
	protected $position = 10101010;

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
		'user_admin_menu' => 'exist',
	);

	/**
	 * Allow child classes to add further initializations.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function init() {

		add_action('plugins_loaded', array($this, 'user_admin_init'));

	} // end init;

	/**
	 * Only run stuff inside the user admin panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function user_admin_init() {

		if (is_user_admin()) {

			add_action('init', array($this, 'get_site_count'));

			add_action('user_admin_menu', array($this, 'add_sites_as_submenu'), 999999);

		} // end if;

	} // end user_admin_init;

	/**
	 * Set the badge of the sites menu to the number of user sites.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_site_count() {

		$customer = wu_get_current_customer();

		if (!$customer) {

			return;

		} // end if;

		$site_count = wu_get_sites(array(
			'count'      => true,
			'meta_query' => array(
				'customer_id' => array(
					'key'   => 'wu_customer_id',
					'value' => $customer->get_id(),
				),
			),
		));

		$site_count += count($customer->get_pending_sites());

		$this->badge_count = $site_count;

	} // end get_site_count;

	/**
	 * Adds the sites owned as submenus.
	 *
	 * @todo mark pending sites as such.
	 * @todo create overview page for each site.
	 * @since 2.0.0
	 * @return void
	 */
	public function add_sites_as_submenu() {

		$customer = wu_get_current_customer();

		if (!$customer) {

			return;

		} // end if;

		/**
		 * First add real sites
		 */
		$sites = wu_get_sites(array(
			'meta_query' => array(
				'customer_id' => array(
					'key'   => 'wu_customer_id',
					'value' => $customer->get_id(),
				),
			),
		));

		foreach ($sites as $site) {

			add_submenu_page(
				$this->id,
				$site->get_title(),
				sprintf('&mdash; %s', $site->get_title()),
				'exist',
				'site-' . $site->get_id(),
				function() {}
			);

		} // end foreach;

		$pending_sites = $customer->get_pending_sites();

		foreach ($pending_sites as $site) {

			add_submenu_page(
				$this->id,
				$site->get_title(),
				sprintf('&mdash; %s', $site->get_title()),
				'exist',
				'site-' . uniqid(),
				function() {}
			);

		} // end foreach;

	} // end add_sites_as_submenu;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Sites', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Sites', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Your Sites', 'wp-ultimo');

	} // end get_submenu_title;

	/**
	 * Loads the list table for this particular page.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\List_Tables\Base_List_Table
	 */
	public function table() {

		return new \WP_Ultimo\List_Tables\Customer_Panel\Site_List_Table();

	} // end table;

} // end class Sites_Admin_Page;
