<?php
/**
 * WP Ultimo Discount_Code Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Discount_Code;
use \WP_Ultimo\Managers\Discount_Code_Manager;

/**
 * WP Ultimo Discount_Code Edit/Add New Admin Page.
 */
class Discount_Code_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-discount-code';

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
	public $object_id = 'discount_code';

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
	protected $highlight_menu_slug = 'wp-ultimo-discount-codes';

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
		'network_admin_menu' => 'wu_edit_discount_codes',
	);

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		$this->add_fields_widget('description', array(
			'title'    => __('Description', 'wp-ultimo'),
			'position' => 'normal',
			'fields'   => array(
				'description' => array(
					'type'        => 'textarea',
					'title'       => __('Description', 'wp-ultimo'),
					'placeholder' => __('Tell your customers what this product is about.', 'wp-ultimo'),
					'value'       => $this->get_object()->get_description(),
					'html_attr'   => array(
						'rows' => 3,
					),
				),
			),
		));

		$this->add_tabs_widget('options', array(
			'title'    => __('Advanced Options', 'wp-ultimo'),
			'position' => 'normal',
			'sections' => array(
				'general'  => array(
					'title'  => __('Limit Uses', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-lock',
					'desc'   => __('Rules and limitations to the applicability of this discount code.', 'wp-ultimo'),
					'fields' => array(
						'uses'     => array(
							'title'         => __('Uses', 'wp-ultimo'),
							'type'          => 'text-edit',
							'display_value' => sprintf(__('This discount code was used %d times.', 'wp-ultimo'), $this->get_object()->get_uses()), // translators: %d is the number of times the coupon was used. // phpcs:ignore
							'tooltip'       => __('The number of times that this discount code was used so far.', 'wp-ultimo'),
						),
						'max_uses' => array(
							'title'       => __('Max Uses', 'wp-ultimo'),
							'type'        => 'number',
							'min'         => 0,
							'placeholder' => 0,
							'value'       => $this->get_object()->has_max_uses() ? $this->get_object()->get_max_uses() : __('Unlimited', 'wp-ultimo'),
							'tooltip'     => __('Use this option to set a limit on how many times this discount code can be used.', 'wp-ultimo'),
						),
					),
				),
				'time'     => array(
					'title'  => __('Start & Expiration Dates', 'wp-ultimo'),
					'desc'   => __('Define a start and end date for this discount code. Useful when running campaigns for a pre-determined period.', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-calendar',
					'fields' => array(
						'date_start'      => array(
							'title'       => __('Start Date', 'wp-ultimo'),
							'type'        => 'text',
							'date'        => true,
							'value'       => $this->edit ? $this->get_object()->get_date_start() : __('No date', 'wp-ultimo'),
							'placeholder' => '2020-04-04 12:00:00',
							'html_attr'   => array(
								'wu-datepicker'   => 'true',
								'data-format'     => 'Y-m-d H:i:S',
								'data-allow-time' => 'true',
							),
						),
						'date_expiration' => array(
							'title'       => __('Expiration Date', 'wp-ultimo'),
							'type'        => 'text',
							'date'        => true,
							'value'       => $this->edit ? $this->get_object()->get_date_expiration() : __('Never Expires', 'wp-ultimo'),
							'placeholder' => '2020-04-04 12:00:00',
							'html_attr'   => array(
								'wu-datepicker'   => 'true',
								'data-format'     => 'Y-m-d H:i:S',
								'data-allow-time' => 'true',
							),
						),
					),
				),
				'products' => array(
					'title'  => __('Limit Products', 'wp-ultimo'),
					'desc'   => __('Determine if you want this discount code to apply to all discountable products or not.', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-price-tag',
					'state'  => array(
						'quotas' => array(
							'select_products' => true,
						),
					),
					'fields' => array(
						'select_products' => array(
							'type'      => 'toggle',
							'title'     => __('Select Products', 'wp-ultimo'),
							'desc'      => __('Manually select to which products this discount code should be applicable.', 'wp-ultimo'),
							'value'     => 1,
							'html_attr' => array(
								'v-model' => 'select_products',
							),
						),
					),
				),
			),
		));

		/*
		 * Handle legacy options for backcompat.
		 */
		$this->handle_legacy_options();

		$this->add_list_table_widget('events', array(
			'title'        => __('Events', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
			'query_filter' => array($this, 'query_filter'),
		));

		$this->add_save_widget('save', array(
			'html_attr' => array(
				'data-wu-app' => 'save_discount_code',
				'data-state'  => json_encode(array(
					'apply_to_setup_fee' => $this->get_object()->get_setup_fee_value() > 0,
				)),
			),
			'fields'    => array(
				'code'                  => array(
					'title'             => __('Coupon Code', 'wp-ultimo'),
					'type'              => 'text',
					'placeholder'       => __('XMAS10OFF', 'wp-ultimo'),
					'value'             => $this->get_object()->get_code(),
					'tooltip'           => '',
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'value_group'           => array(
					'type'              => 'group',
					'title'             => __('Discount', 'wp-ultimo'),
					'tooltip'           => __('The initial amount collected at registration.', 'wp-ultimo'),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
					'fields'            => array(
						'type'  => array(
							'type'            => 'select',
							'value'           => $this->get_object()->get_type(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-w-2/3',
							'options'         => array(
								'percentage' => __('Percentage (%)', 'wp-ultimo'),
								'absolute'   => sprintf(__('Absolute (%s)', 'wp-ultimo'), wu_get_currency_symbol()),
							),
						),
						'value' => array(
							'type'            => 'number',
							'value'           => $this->get_object()->get_value(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-ml-2 wu-w-1/3',
						),
					),
				),
				'apply_to_renewals'     => array(
					'type'              => 'toggle',
					'title'             => __('Apply to Renewals?', 'wp-ultimo'),
					'desc'              => __('By default, discounts are only applied to the first payment.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_apply_to_renewals(),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'apply_to_setup_fee'    => array(
					'type'              => 'toggle',
					'title'             => __('Setup Fee Discount?', 'wp-ultimo'),
					'desc'              => __('Also set a discount for setup fee?', 'wp-ultimo'),
					'value'             => $this->get_object()->get_setup_fee_value() > 0,
					'html_attr'         => array(
						'v-model' => 'apply_to_setup_fee',
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'setup_fee_value_group' => array(
					'type'              => 'group',
					'title'             => __('Setup Fee Discount', 'wp-ultimo'),
					'tooltip'           => __('The initial amount collected at registration.', 'wp-ultimo'),
					'wrapper_html_attr' => array(
						'v-show'  => 'apply_to_setup_fee',
						'v-cloak' => '1',
					),
					'fields'            => array(
						'setup_fee_type'  => array(
							'type'            => 'select',
							'value'           => $this->get_object()->get_setup_fee_type(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-w-2/3',
							'options'         => array(
								'percentage' => __('Percentage (%)', 'wp-ultimo'),
								'absolute'   => sprintf(__('Absolute (%s)', 'wp-ultimo'), wu_get_currency_symbol()),
							),
						),
						'setup_fee_value' => array(
							'type'            => 'number',
							'value'           => $this->get_object()->get_setup_fee_value(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-ml-2 wu-w-1/3',
						),
					),
				),
			),
		));

		$this->add_fields_widget('active', array(
			'title'  => __('Active', 'wp-ultimo'),
			'fields' => array(
				'active' => array(
					'type'  => 'toggle',
					'title' => __('Is Active?', 'wp-ultimo'),
					'desc'  => __('Deactivate this discount code for new sign-ups.', 'wp-ultimo'),
					'value' => $this->get_object()->is_active(),
				),
			),
		));

	} // end register_widgets;

	/**
	 * Handles legacy advanced options for coupons.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_legacy_options() {

		global $wp_filter;

		$tabs = array(__('Legacy Add-ons', 'wp-ultimo'));

		if (!isset($wp_filter['wp_ultimo_coupon_advanced_options'])) {

			return;

		} // end if;

		wp_enqueue_style('wu-legacy-admin-tabs', wu_get_asset('legacy-admin-tabs.css', 'css'), false, wu_get_version());

		$priorities = $wp_filter['wp_ultimo_coupon_advanced_options']->callbacks;

		$fields = array(
			'heading' => array(
				'type'  => 'header',
				'title' => __('Legacy Options', 'wp-ultimo'),
				'desc'  => sprintf(__('Options for %s, and others.', 'wp-ultimo'), implode(', ', $tabs)),
			),
		);

		foreach ($priorities as $priority => $callbacks) {

			foreach ($callbacks as $id => $callable) {

				$fields[$id] = array(
					'type'    => 'html',
					'classes' => 'wu--mt-2',
					'content' => function() use ($callable) {

						call_user_func($callable['function'], $this->get_object());

					},
				);

			} // end foreach;

		} // end foreach;

		$this->add_fields_widget('legacy-options', array(
			'title'                 => __('Legacy Options', 'wp-ultimo'),
			'position'              => 'normal',
			'fields'                => $fields,
			'classes'               => 'wu-legacy-options-panel',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'style' => 'margin-top: -5px;',
			),
		));

	} // end handle_legacy_options;

	/**
	 * Register ajax forms that we use for discount code.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Delete Discount code - Confirmation modal
		 */

		add_filter('wu_data_json_success_delete_discount_code_modal', function($data_json) {
			return array(
				'redirect_url' => wu_network_admin_url('wp-ultimo-discount-codes', array('deleted' => 1))
			);
		});

	} // end register_forms;

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function query_filter($args) {

		$extra_args = array(
			'object_type' => 'discount_code',
			'object_id'   => abs($this->get_object()->get_id()),
		);

		return array_merge($args, $extra_args);

	} // end query_filter;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Discount Code', 'wp-ultimo') : __('Add new Discount Code', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Discount Code', 'wp-ultimo');

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
			'edit_label'          => __('Edit Discount Code', 'wp-ultimo'),
			'add_new_label'       => __('Add new Discount Code', 'wp-ultimo'),
			'updated_message'     => __('Discount Code updated successfully!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Discount Code', 'wp-ultimo'),
			'title_description'   => '',
			'save_button_label'   => __('Save Discount Code', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Discount Code', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Discount_Code
	 */
	public function get_object() {

		if ($this->object !== null) {

			return $this->object;

		} // end if;

		if (isset($_GET['id'])) {

			$item_id = wu_request('id', 0);

			$item = wu_get_discount_code($item_id);

			if (!$item) {

				wp_redirect(wu_network_admin_url('wp-ultimo-discount_codes'));

				exit;

			} // end if;

			$this->object = $item;

			return $this->object;

		} // end if;

		$this->object = new Discount_Code;

		return $this->object;

	} // end get_object;

	/**
	 * Discount_Codes have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return true;

	} // end has_title;

} // end class Discount_Code_Edit_Admin_Page;
