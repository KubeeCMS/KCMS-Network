<?php
/**
 * WP Ultimo Payment Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Database\Payments\Payment_Status;

/**
 * WP Ultimo Payment Admin Page.
 */
class Payment_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-payments';

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
		'network_admin_menu' => 'wu_read_payments',
	);

	/**
	 * Register ajax forms that we use for payments.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Edit/Add Line Item
		 */
		wu_register_form('add_new_payment', array(
			'render'     => array($this, 'render_add_new_payment_modal'),
			'handler'    => array($this, 'handle_add_new_payment_modal'),
			'capability' => 'wu_edit_payments',
		));

	} // end register_forms;

	/**
	 * Renders the add/edit line items form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_new_payment_modal() {

		$fields = array(
			'products'       => array(
				'type'        => 'model',
				'title'       => __('Products', 'wp-ultimo'),
				'placeholder' => __('Search Products...', 'wp-ultimo'),
				'desc'        => __('Each product will be added as a line item.', 'wp-ultimo'),
				'value'       => '',
				'tooltip'     => '',
				'html_attr'   => array(
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 10,
				),
			),
			'status'         => array(
				'type'        => 'select',
				'title'       => __('Status', 'wp-ultimo'),
				'placeholder' => __('Status', 'wp-ultimo'),
				'desc'        => __('The payment status to attach to the newly created payment.', 'wp-ultimo'),
				'value'       => Payment_Status::COMPLETED,
				'options'     => Payment_Status::to_array(),
				'tooltip'     => '',
			),
			'membership_id'  => array(
				'type'        => 'model',
				'title'       => __('Membership', 'wp-ultimo'),
				'placeholder' => __('Search Membership...', 'wp-ultimo'),
				'desc'        => __('The membership associated with this payment.', 'wp-ultimo'),
				'value'       => '',
				'tooltip'     => '',
				'html_attr'   => array(
					'data-model'       => 'membership',
					'data-value-field' => 'id',
					'data-label-field' => 'reference_code',
					'data-max-items'   => 1,
					'data-selected'    => '',
				),
			),
			'add_setup_fees' => array(
				'type'  => 'toggle',
				'title' => __('Include Setup Fees', 'wp-ultimo'),
				'desc'  => __('Checking this box will include setup fees attached to the selected products as well.', 'wp-ultimo'),
				'value' => 1,
			),
			'submit_button'  => array(
				'type'            => 'submit',
				'title'           => __('Add Payment', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
			),
		);

		$form = new \WP_Ultimo\UI\Form('add_payment', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'add_payment',
				'data-state'  => wu_convert_to_state(array(
					'taxable' => 0,
					'type'    => 'product',
				)),
			),
		));

		$form->render();

	} // end render_add_new_payment_modal;

	/**
	 * Handles the add/edit of line items.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_add_new_payment_modal() {

		$membership = wu_get_membership(wu_request('membership_id'));

		if (!$membership) {

			$error = new \WP_Error('invalid-membership', __('Invalid membership.', 'wp-ultimo'));

			return wp_send_json_error($error);

		} // end if;

		$cart = new \WP_Ultimo\Checkout\Cart(array(
			'products'  => explode(',', wu_request('products')),
			'cart_type' => wu_request('add_setup_fees') ? 'new' : 'renewal',
		));

		$payment_data = array_merge($cart->to_payment_data(), array(
			'status'        => wu_request('status'),
			'membership_id' => $membership->get_id(),
			'customer_id'   => $membership->get_customer_id(),
		));

		$payment = wu_create_payment($payment_data);

		if (is_wp_error($payment)) {

			return wp_send_json_error($payment);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-edit-payment', array(
				'id' => $payment->get_id(),
			))
		));

	} // end handle_add_new_payment_modal;

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
			'deleted_message' => __('Payment removed successfully.', 'wp-ultimo'),
			'search_label'    => __('Search Payment', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Payments', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Payments', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Payments', 'wp-ultimo');

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
				'label'   => __('Add Payment', 'wp-ultimo'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('add_new_payment'),
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

		return new \WP_Ultimo\List_Tables\Payment_List_Table();

	} // end table;

} // end class Payment_List_Admin_Page;
