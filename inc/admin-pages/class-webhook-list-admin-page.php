<?php
/**
 * WP Ultimo Webhook Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Webhook Admin Page.
 */
class Webhook_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-webhooks';

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
		'network_admin_menu' => 'wu_read_webhooks',
	);

	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		wp_register_script('wu-webhook-page', wu_get_asset('webhook-page.js', 'js'), array('jquery', 'wu-sweet-alert'));

		wp_localize_script('wu-webhook-page', 'wu_webhook_page', array(
			'i18n' => array(
				'error_title'   => __('Webhook Test', 'wp-ultimo'),
				'error_message' => __('An error occurred when sending the test webhook, please try again.', 'wp-ultimo'),
				'copied'        => __('Copied!', 'wp-ultimo'),
			),
		));

		wp_enqueue_script('wu-webhook-page');

	} // end register_scripts;

	/**
	 * Register ajax forms that we use for add new webhooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Add new webhook.
		 */
		wu_register_form('add_new_webhook_modal', array(
			'render'     => array($this, 'render_add_new_webhook_modal'),
			'handler'    => array($this, 'handle_add_new_webhook_modal'),
			'capability' => 'wu_edit_webhooks',
		));

	} // end register_forms;

	/**
	 * Renders the add new webhook modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function render_add_new_webhook_modal() {

		$events = wu_get_event_types();

		$event_options = array();

		foreach ($events as $slug => $event) {

			$event_options[$slug] = $slug;

		} // end foreach;

		$fields = array(
			'name'          => array(
				'type'        => 'text',
				'title'       => __('Webhook Name', 'wp-ultimo'),
				'desc'        => __('A name to easily identify your webhook.', 'wp-ultimo'),
				'placeholder' => __('E.g. Zapier Integration', 'wp-ultimo'),
			),
			'event'         => array(
				'title'   => __('Event', 'wp-ultimo'),
				'type'    => 'select',
				'desc'    => __('The event that will trigger the webhook.', 'wp-ultimo'),
				'options' => $event_options
			),
			'webhook_url'   => array(
				'type'        => 'url',
				'title'       => __('Webhook Url', 'wp-ultimo'),
				'desc'        => __('The url of your webhook.', 'wp-ultimo'),
				'placeholder' => __('E.g. https://example.com/', 'wp-ultimo'),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => __('Add New Webhook', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					// 'v-bind:disabled' => '!confirmed',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('edit_line_item', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'edit_line_item',
				'data-state'  => json_encode(array(
					'event' => ''
				)),
			),
		));

		$form->render();

	} // end render_add_new_webhook_modal;

	/**
	 * Handles the add new webhook modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_new_webhook_modal() {

		$status = wu_create_webhook($_POST);

		if (is_wp_error($status)) {

			wp_send_json_error($status);

		} else {

			wp_send_json_success(array(
				'redirect_url' => wu_network_admin_url('wp-ultimo-edit-webhook', array(
					'id' => $status->get_id()
				))
			));

		} // end if;

	} // end handle_add_new_webhook_modal;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {} // end register_widgets;

	/**
	 * Returns an array with the labels for the edit page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function get_labels() {

		return array(
			'deleted_message' => __('Webhook removed successfully.', 'wp-ultimo'),
			'search_label'    => __('Search Webhook', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Webhooks', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Webhooks', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Webhooks', 'wp-ultimo');

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
				'label'   => __('Add New Webhook', 'wp-ultimo'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('add_new_webhook_modal'),
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

		return new \WP_Ultimo\List_Tables\Webhook_List_Table();

	} // end table;

} // end class Webhook_List_Admin_Page;
