<?php
/**
 * WP Ultimo Event Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

use \WP_Ultimo\Models\Event;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Event Admin Page.
 */
class Event_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-events';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = '';

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
		'network_admin_menu' => 'wu_read_events',
	);

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('init', array($this, 'set_badge_count'));

	} // end init;

	/**
	 * Adds hooks when the page loads.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function page_loaded() {

		parent::page_loaded();

		add_action('in_admin_header', array($this, 'count_seen_events'));

	} // end page_loaded;

	/**
	 * Sets events badge notification subtracting the total number of events from the seen events in the user meta.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function set_badge_count() {

		$user = get_current_user_id();

		$seen_events = get_user_meta( $user, 'wu_seen_events');

		$all_events = Event::get_items_as_array(1000);

		if (!isset($seen_events[0])) {

			$seen_events[0] = 0;

		} // end if;

		$this->badge_count = count($all_events) - $seen_events[0];

	} // end set_badge_count;

	/**
	 * Sets the seen events in the current user meta.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function count_seen_events() {

		$events = Event::get_items_as_array();

		$user = get_current_user_id();

		update_user_meta($user, 'wu_seen_events', count($events));

		$this->badge_count = '';

	} // end count_seen_events;

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

		return __('Events', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Events', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Events', 'wp-ultimo');

	} // end get_submenu_title;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return array(
			array(
				'url'   => wu_network_admin_url('wp-ultimo-view-logs'),
				'label' => __('View Logs'),
				'icon'  => 'dashicons dashicons-editor-ol',
			),
		);

	} // end action_links;

	/**
	 * Loads the list table for this particular page.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\List_Tables\Base_List_Table
	 */
	public function table() {

		return new \WP_Ultimo\List_Tables\Event_List_Table();

	} // end table;

} // end class Event_List_Admin_Page;
