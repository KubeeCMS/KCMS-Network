<?php
/**
 * WP Ultimo Event View Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Event;

/**
 * WP Ultimo Event View Admin Page.
 */
class Event_View_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-view-event';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Object ID being edited.
	 *
	 * @since 1.8.2
	 * @var string
	 */
	public $object_id = 'event';

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
	protected $highlight_menu_slug = 'wp-ultimo-events';

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
		'network_admin_menu' => 'wu_read_events',
	);

	/**
	 * Allow child classes to register scripts and styles that can be loaded on the output function, for example.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		\WP_Ultimo\Scripts::get_instance()->register_script('wu-event-view', wu_get_asset('event-view-page.js', 'js'), array('jquery'));

		wp_enqueue_script('wu-event-view');

		wp_enqueue_script('clipboard');

		wp_enqueue_script('wu-vue');

	} // end register_scripts;

	/**
	 * Register ajax forms that we use for membership.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Delete Event - Confirmation modal
		 */

		add_filter('wu_data_json_success_delete_event_modal', function($data_json) {
			return array(
				'redirect_url' => wu_network_admin_url('wp-ultimo-events', array('deleted' => 1))
			);
		});

	} // end register_forms;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		add_meta_box('wp-ultimo-message', __('Event Message', 'wp-ultimo'), array($this, 'output_default_widget_message'), get_current_screen()->id, 'normal', null);

		add_meta_box('wp-ultimo-initiator', __('Event', 'wp-ultimo'), array($this, 'output_default_widget_initiator'), get_current_screen()->id, 'side', null);

		add_meta_box('wp-ultimo-payload', __('Event Payload', 'wp-ultimo'), array($this, 'output_default_widget_payload'), get_current_screen()->id, 'normal', null);

		$this->add_info_widget('info', array(
			'title'    => __('Timestamps', 'wp-ultimo'),
			'position' => 'side',
			'modified' => false,
		));

	} // end register_widgets;

	/**
	 * Outputs the markup for the default Save widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_default_widget_message() {

		wu_get_template('events/widget-message', array(
			'screen' => get_current_screen(),
			'page'   => $this,
			'labels' => $this->get_labels(),
			'object' => $this->get_object(),
		));

	} // end output_default_widget_message;

	/**
	 * Outputs the markup for the payload widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_default_widget_payload() {

		$object = $this->get_object();

		wu_get_template('events/widget-payload', array(
			'title'        => __('Event Payload', 'wp-ultimo'),
			'loading_text' => __('Loading Payload', 'wp-ultimo'),
			'payload'      => json_encode($object->get_payload(), JSON_PRETTY_PRINT),
		));

	} // end output_default_widget_payload;

	/**
	 * Outputs the markup for the initiator widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_default_widget_initiator() {

		$object = $this->get_object();

		$args = array(
			'object' => $object,
		);

		wu_get_template('events/widget-initiator', $args);

	} // end output_default_widget_initiator;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Event', 'wp-ultimo') : __('Add new Event', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Event', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		return array();

	} // end action_links;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return array(
			'edit_label'          => __('Edit Event', 'wp-ultimo'),
			'add_new_label'       => __('Add new Event', 'wp-ultimo'),
			'updated_message'     => __('Event updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Event', 'wp-ultimo'),
			'title_description'   => '',
			'save_button_label'   => __('Save Event', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Event', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Event
	 */
	public function get_object() {

		if (isset($_GET['id'])) {

			$query = new \WP_Ultimo\Database\Events\Event_Query;

			$item = $query->get_item_by('id', $_GET['id']);

			if ($item) {

				return $item;

			} // end if;

		} // end if;

		wp_redirect(wu_network_admin_url('wp-ultimo-events'));

		exit;

	} // end get_object;

	/**
	 * Events have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return false;

	} // end has_title;

	/**
	 * Handles the save of this form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save() {

		return;

	} // end handle_save;

} // end class Event_View_Admin_Page;
