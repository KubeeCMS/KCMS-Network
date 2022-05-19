<?php
/**
 * WP Ultimo Switch Template Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages\Customer_Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Switch Template Admin Page.
 */
class Template_Switching_Admin_Page extends \WP_Ultimo\Admin_Pages\Base_Customer_Facing_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wu-template-switching';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * This page has no parent, so we need to highlight another sub-menu.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $highlight_menu_slug = 'account';

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
		'user_admin_menu' => 'read',
		'admin_menu'      => 'read',
	);

	/**
	 * Should we hide admin notices on this page?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hide_admin_notices = true;

	/**
	 * Should we force the admin menu into a folded state?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $fold_menu = true;

	/**
	 * If this customer facing page has menu settings.
	 *
	 * @since 2.0.9
	 * @var boolean
	 */
	protected $menu_settings = false;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Switch Template', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Switch Template', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Registers the necessary scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		do_action('wu_template_switching_admin_page_scripts', null, null);

	} // end register_scripts;

	/**
	 * Overrides the page loaded method.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function page_loaded() {

		do_action('wu_template_switching_admin_page', null);

		parent::page_loaded();

	} // end page_loaded;

	/**
	 * Displays the content of the activation section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output() {
		/*
		 * Renders the base edit page layout, with the columns and everything else =)
		 */
		wu_get_template('base/centered', array(
			'screen'  => get_current_screen(),
			'page'    => $this,
			'content' => '',
			'labels'  => array(
				'updated_message' => __('Template switched successfully!', 'wp-ultimo')
			)
		));

	} // end output;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		\WP_Ultimo\UI\Template_Switching_Element::get_instance()->as_metabox(get_current_screen()->id);

	} // end register_widgets;

} // end class Template_Switching_Admin_Page;
