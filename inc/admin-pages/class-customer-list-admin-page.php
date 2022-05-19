<?php
/**
 * WP Ultimo Dashboard Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Dashboard Admin Page.
 */
class Customer_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-customers';

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
		'network_admin_menu' => 'wu_read_customers',
	);

	/**
	 * Register ajax forms that we use for payments.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Add new Customer
		 */
		wu_register_form('add_new_customer', array(
			'render'     => array($this, 'render_add_new_customer_modal'),
			'handler'    => array($this, 'handle_add_new_customer_modal'),
			'capability' => 'wu_add_customers',
		));

	} // end register_forms;

	/**
	 * Renders the add new customer modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_new_customer_modal() {

		$fields = array(
			'type'          => array(
				'type'      => 'tab-select',
				'html_attr' => array(
					'v-model' => 'type',
				),
				'options'   => array(
					'existing' => __('Existing User', 'wp-ultimo'),
					'new'      => __('Invite New', 'wp-ultimo'),
				),
			),
			'user_id'       => array(
				'type'              => 'model',
				'title'             => __('Existing User', 'wp-ultimo'),
				'placeholder'       => __('Search WordPress user...', 'wp-ultimo'),
				'tooltip'           => '',
				'min'               => 1,
				'wrapper_html_attr' => array(
					'v-show' => "require('type', 'existing')",
				),
				'html_attr'         => array(
					'data-model'        => 'user',
					'data-value-field'  => 'ID',
					'data-label-field'  => 'display_name',
					'data-search-field' => 'display_name',
					'data-max-items'    => 1,
				),
			),
			'username'      => array(
				'type'              => 'text',
				'title'             => __('Username', 'wp-ultimo'),
				'placeholder'       => __('E.g. johnsmith', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-show' => "require('type', 'new')",
				),
			),
			'email_address' => array(
				'type'              => 'email',
				'title'             => __('Email Address', 'wp-ultimo'),
				'placeholder'       => __('E.g. customer@wpultimo.dev', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-show' => "require('type', 'new')",
				),
			),
			'set_password'  => array(
				'type'              => 'toggle',
				'title'             => __('Set Password', 'wp-ultimo'),
				'desc'              => __('If not set, the user will be asked to set a password after accepting the invite.', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-show' => "require('type', 'new')",
				),
				'html_attr'         => array(
					'v-model' => 'set_password',
				),
			),
			'password'      => array(
				'type'              => 'password',
				'title'             => __('Password', 'wp-ultimo'),
				'placeholder'       => __('E.g. p@$$w0rd', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-show' => "require('type', 'new') && require('set_password', true)",
				),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => __('Create Customer', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					// 'v-bind:disabled' => '!confirmed',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('add_new_customer', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'add_new_customer',
				'data-state'  => json_encode(array(
					'set_password' => false,
					'type'         => 'existing',
				)),
			),
		));

		$form->render();

	} // end render_add_new_customer_modal;

	/**
	 * Handles creation of a new customer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_new_customer_modal() {

		if (wu_request('type', 'existing') === 'new') {

			$customer_data = array(
				'email'    => wu_request('email_address'),
				'username' => wu_request('username'),
				'password' => wu_request('password', false),
				'meta'     => array(),
			);

		} else {

			$customer_data = array(
				'user_id' => wu_request('user_id', 0),
			);

		} // end if;

		/*
			* Tries to create the customer
			*/
		$customer = wu_create_customer($customer_data);

		if (is_wp_error($customer)) {

			wp_send_json_error($customer);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-edit-customer', array(
				'id' => $customer->get_id(),
			))
		));

	} // end handle_add_new_customer_modal;

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
			'deleted_message' => __('Customer removed successfully.', 'wp-ultimo'),
			'search_label'    => __('Search Customer', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Customers', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Customers', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Customers', 'wp-ultimo');

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
				'label'   => __('Add Customer', 'wp-ultimo'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('add_new_customer'),
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

		return new \WP_Ultimo\List_Tables\Customer_List_Table();

	} // end table;

} // end class Customer_List_Admin_Page;
