<?php
/**
 * WP Ultimo Customer Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Database\Memberships\Membership_Status;

/**
 * WP Ultimo Customer Edit/Add New Admin Page.
 */
class Customer_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-customer';

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
	public $object_id = 'customer';

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
	protected $highlight_menu_slug = 'wp-ultimo-customers';

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
		'network_admin_menu' => 'wu_edit_customers',
	);

	/**
	 * Allow child classes to add hooks to be run once the page is loaded.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
	 * @since 1.8.2
	 * @return void
	 */
	public function hooks() {

		parent::hooks();

		add_action('wu_page_edit_redirect_handlers', array($this, 'handle_send_verification_notice'));

		add_filter('removable_query_args', array($this, 'remove_query_args'));

	} // end hooks;

	/**
	 * Allow child classes to register scripts and styles that can be loaded on the output function, for example.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		wp_enqueue_style('wu-flags');

		wp_enqueue_editor();

	} // end register_scripts;

	/**
	 * Register ajax forms that we use for membership.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Transfer customer - Confirmation modal
		 */
		wu_register_form('transfer_customer', array(
			'render'     => array($this, 'render_transfer_customer_modal'),
			'handler'    => array($this, 'handle_transfer_customer_modal'),
			'capability' => 'wu_transfer_customer',
		));

		/*
		 * Adds the hooks to handle deletion.
		 */
		add_filter('wu_form_fields_delete_customer_modal', array($this, 'customer_extra_delete_fields'), 10, 2);

		add_filter('wu_form_attributes_delete_customer_modal', array($this, 'customer_extra_form_attributes'));

		add_action('wu_after_delete_customer_modal', array($this, 'customer_after_delete_actions'));

	} // end register_forms;

	/**
	 * Renders the transfer confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_transfer_customer_modal() {

		$user = wu_get_customer(wu_request('id'));

		if (!$user) {

			return;

		} // end if;

		$fields = array(
			'confirm'        => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Transfer', 'wp-ultimo'),
				'desc'      => __('This will start the transfer of assets from one user to another.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button'  => array(
				'type'            => 'submit',
				'title'           => __('Start Transfer', 'wp-ultimo'),
				'placeholder'     => __('Start Transfer', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
			'id'             => array(
				'type'  => 'hidden',
				'value' => $user->get_id(),
			),
			'target_user_id' => array(
				'type'  => 'hidden',
				'value' => wu_request('target_user_id'),
			),
		);

		$form = new \WP_Ultimo\UI\Form('total-actions', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'transfer_customer',
				'data-state'  => json_encode(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_transfer_customer_modal;

	/**
	 * Handles the transfer of customer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_transfer_customer_modal() {

		global $wpdb;

		$customer    = wu_get_customer(wu_request('id'));
		$target_user = get_user_by('id', wu_request('target_user_id'));

		if (!$customer) {

			wp_send_json_error(new \WP_Error('not-found', __('Customer not found.', 'wp-ultimo')));

		} // end if;

		if (!$target_user) {

			wp_send_json_error(new \WP_Error('not-found', __('User not found.', 'wp-ultimo')));

		} // end if;

		$customer->set_user_id($target_user->ID);

		$saved = $customer->save();

		if (is_wp_error($saved)) {

			wp_send_json_error($saved);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-edit-customer', array(
				'id' => $customer->get_id(),
			))
		));

	} // end handle_transfer_customer_modal;

	/**
	 * Adds the extra fields to the customer delete modal.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $fields original array of fields.
	 * @param object $customer The customer object.
	 * @return array
	 */
	public function customer_extra_delete_fields($fields, $customer) {

		$custom_fields = array(
			'delete_all'                => array(
				'type'      => 'toggle',
				'title'     => __('Delete everything', 'wp-ultimo'),
				'desc'      => __('Sites, payments and memberships.', 'wp-ultimo'),
				'html_attr' => array(
					'v-bind:value' => 'delete_all_confirmed',
					'v-model'      => 'delete_all_confirmed',
				),
			),
			're_assignment_customer_id' => array(
				'type'              => 'model',
				'title'             => __('Re-assignment to customer', 'wp-ultimo'),
				'placeholder'       => __('Select Customer...', 'wp-ultimo'),
				'html_attr'         => array(
					'data-model'        => 'customer',
					'data-value-field'  => 'id',
					'data-label-field'  => 'display_name',
					'data-search-field' => 'display_name',
					'data-max-items'    => 1,
					'data-exclude'      => json_encode(array($customer->get_id())),
				),
				'wrapper_html_attr' => array(
					'v-show' => '!delete_all_confirmed',
				),
			),
		);

		return array_merge($custom_fields, $fields);

	} // end customer_extra_delete_fields;

	/**
	 * Adds the extra form attributes to the delete modal.
	 *
	 * @since 2.0.0
	 *
	 * @param array $form_attributes Form attributes.
	 * @return array
	 */
	public function customer_extra_form_attributes($form_attributes) {

		$custom_state = json_decode($form_attributes['html_attr']['data-state'], true);

		$custom_state['delete_all_confirmed'] = false;

		$form_attributes['html_attr']['data-state'] = json_encode($custom_state);

		return $form_attributes;

	} // end customer_extra_form_attributes;

	/**
	 * Enqueues actions to be run after a customer is deleted.
	 *
	 * @since 2.0.0
	 *
	 * @param object $customer The customer object.
	 * @return void
	 */
	public function customer_after_delete_actions($customer) {

		$delete_all = wu_request('delete_all');

		if ($delete_all) {

			foreach ($customer->get_memberships() as $membership) {

				/**
				* Enqueue task
				*/
				wu_enqueue_async_action('wu_async_delete_membership', array(
					'membership_id' => $membership->get_id(),
				), 'membership');

			} // end foreach;

			foreach ($customer->get_payments() as $payment) {

				/**
				* Enqueue task
				*/
				wu_enqueue_async_action('wu_async_delete_payment', array(
					'payment_id' => $payment->get_id(),
				), 'payment');

			} // end foreach;

		} else {

			$re_assignment_customer = wu_get_customer(wu_request('re_assignment_customer_id'));

			if ($re_assignment_customer) {

				foreach ($customer->get_memberships() as $membership) {

					/**
					 * Enqueue task
					 */
					wu_enqueue_async_action('wu_async_transfer_membership', array(
						'membership_id'      => $membership->get_id(),
						'target_customer_id' => $re_assignment_customer->get_id(),
					), 'membership');

				} // end foreach;

				foreach ($customer->get_payments() as $payment) {

					/**
					 * Enqueue to the future
					 */
					wu_enqueue_async_action('wu_async_transfer_payment', array(
						'payment_id'         => $payment->get_id(),
						'target_customer_id' => $re_assignment_customer->get_id(),
					), 'payment');

				} // end foreach;

			} // end if;

		} // end if;

	} // end customer_after_delete_actions;

	/**
	 * Generates the list of meta fields.
	 *
	 * @since 2.0.11
	 * @return array
	 */
	public function generate_customer_meta_fields() {

		$custom_meta_keys = wu_get_all_customer_meta($this->get_object()->get_id(), true);

		$meta_fields_set = array();

		$meta_fields_unset = array();

		foreach ($custom_meta_keys as $key => $value) {

			$field_location_breadcrumbs = array(__('orphan field - the original form no longer exists', 'wp-ultimo'));

			$form = wu_get_isset($value, 'form');

			if ($form) {

				$field_location_breadcrumbs = array(
					$form,
					wu_get_isset($value, 'step'),
					wu_get_isset($value, 'id'),
				);

			} // end if;

			$location = sprintf(
				'<small><strong>%s</strong> %s</small>',
				__('Location:', 'wp-ultimo'),
				implode(' &rarr; ', array_filter($field_location_breadcrumbs))
			);

			$options = wu_get_isset($value, 'options', array());

			if ($options) {

				$options = array_combine(
					array_column($options, 'key'),
					array_column($options, 'label')
				);

			} // end if;

			$options = array_merge(array('' => '--'), $options);

			$field_data = array(
				'title'   => wu_get_isset($value, 'title', wu_slug_to_name($key)),
				'type'    => wu_get_isset($value, 'type', 'text'),
				'desc'    => wu_get_isset($value, 'description', '') . $location,
				'options' => $options,
				'tooltip' => wu_get_isset($value, 'tooltip', ''),
				'value'   => wu_get_customer_meta($this->get_object()->get_id(), $key),
			);

			if ($field_data['type'] === 'hidden') {

				$field_data['type'] = 'text';

			} // end if;

			if (wu_get_isset($value, 'exists')) {

				$meta_fields_set[$key] = $field_data;

			} else {

				$field_data['wrapper_html_attr'] = array(
					'v-show' => 'display_unset_fields',
				);

				$meta_fields_unset[$key] = $field_data;

			} // end if;

		} // end foreach;

		$collapsible_header = array();

		if ($meta_fields_unset) {

			$collapsible_header['display_unset_fields'] = array(
				'title'           => __('Display unset fields', 'wp-ultimo'),
				'desc'            => __('If fields were added after the customer creation or onto a different form, they will not have a set value for this customer. You can manually set those here.', 'wp-ultimo'),
				'type'            => 'toggle',
				'wrapper_classes' => 'wu-bg-gray-100',
				'html_attr'       => array(
					'v-model' => 'display_unset_fields',
				)
			);

		} // end if;

		$final_fields = array_merge($meta_fields_set, $collapsible_header, $meta_fields_unset);

		if (empty($final_fields)) {

			$final_fields['empty'] = array(
				'type'    => 'note',
				'desc'    => __('No custom meta data collected and no custom fields found.', 'wp-ultimo'),
				'classes' => 'wu-text-center',
			);

		} // end if;

		return $final_fields;

	} // end generate_customer_meta_fields;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		$labels = $this->get_labels();

		$this->add_fields_widget('at_a_glance', array(
			'title'                 => __('At a Glance', 'wp-ultimo'),
			'position'              => 'normal',
			'classes'               => 'wu-overflow-hidden wu-m-0 wu--mt-1 wu--mx-3 wu--mb-3',
			'field_wrapper_classes' => 'wu-w-1/3 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t-0 wu-border-l-0 wu-border-r wu-border-b-0 wu-border-gray-300 wu-border-solid wu-float-left wu-relative',
			'html_attr'             => array(
				'style' => 'margin-top: -6px;',
			),
			'fields'                => array(
				'id'            => array(
					'type'          => 'text-display',
					'copy'          => true,
					'title'         => __('Customer ID', 'wp-ultimo'),
					'display_value' => $this->get_object()->get_id(),
					'tooltip'       => '',
				),
				'last_login'    => array(
					'edit'          => false,
					'title'         => __('Last Login', 'wp-ultimo'),
					'type'          => 'text-edit',
					'value'         => $this->edit ? $this->get_object()->get_last_login(false) : __('No date', 'wp-ultimo'),
					'display_value' => $this->edit ? $this->get_object()->get_last_login(false) : false,
				),
				'total_grossed' => array(
					'type'          => 'text-display',
					'title'         => __('Total Grossed', 'wp-ultimo'),
					'display_value' => wu_format_currency($this->get_object()->get_total_grossed()),
					'tooltip'       => '',
				),
			),
		));

		$this->add_list_table_widget('memberships', array(
			'title'        => __('Memberships', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Customers_Membership_List_Table(),
			'query_filter' => array($this, 'memberships_query_filter'),
		));

		$this->add_tabs_widget('options', array(
			'title'    => __('Customer Options', 'wp-ultimo'),
			'position' => 'normal',
			'sections' => apply_filters('wu_customer_options_sections', array(
				'general'      => array(
					'title'  => __('General', 'wp-ultimo'),
					'desc'   => __('General options for the customer.', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-globe',
					'fields' => array(
						'vip' => array(
							'type'    => 'toggle',
							'title'   => __('VIP', 'wp-ultimo'),
							'desc'    => __('Set this customer as a VIP.', 'wp-ultimo'),
							'tooltip' => '',
							'value'   => $this->get_object()->is_vip(),
						),
					),
				),
				'billing_info' => array(
					'title'  => __('Billing Info', 'wp-ultimo'),
					'desc'   => __('Billing information for this particular customer', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-address',
					'fields' => $this->get_object()->get_billing_address()->get_fields(),
				),
				'custom_meta'  => array(
					'title'  => __('Custom Meta', 'wp-ultimo'),
					'desc'   => __('Custom data collected via WP Ultimo forms.', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-database wu-pt-px',
					'fields' => $this->generate_customer_meta_fields(),
					'state'  => array(
						'display_unset_fields' => false,
					),
				),
				// @todo: bring these back
				// phpcs:disable
				// 'payment_methods' => array(
				// 	'title'  => __('Payment Methods', 'wp-ultimo'),
				// 	'desc'   => __('Add extra information to this customer.', 'wp-ultimo'),
				// 	'icon'   => 'dashicons-wu-credit-card',
				// 	'fields' => apply_filters('wu_customer_payment_methods', array(), $this->get_object(), $this),
				// ),
				// 'custom_fields'   => array(
				// 	'title'  => __('Custom Fields', 'wp-ultimo'),
				// 	'desc'   => __('Add extra information to this customer.', 'wp-ultimo'),
				// 	'icon'   => 'dashicons-wu-new-message',
				// 	'fields' => array(
				// 		'extra_information' => array(
				// 			'type'   => 'repeater',
				// 			'fields' => array(
				// 				'name'  => array(
				// 					'title'           => __('Name', 'wp-ultimo'),
				// 					'type'            => 'text',
				// 					'value'           => '',
				// 					'wrapper_classes' => 'wu-w-1/2',
				// 				),
				// 				'value' => array(
				// 					'title'           => __('Value', 'wp-ultimo'),
				// 					'type'            => 'text',
				// 					'value'           => '',
				// 					'wrapper_classes' => 'wu-w-1/2 wu-ml-4',
				// 				),
				// 			),
				// 			'values' => $this->get_object()->get_extra_information()
				// 		),
				// 	),
				// ),
				// phpcs:enable
			), $this->get_object()),
		));

		$this->add_list_table_widget('payments', array(
			'title'        => __('Payments', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Customers_Payment_List_Table(),
			'query_filter' => array($this, 'memberships_query_filter'),
		));

		$this->add_list_table_widget('sites', array(
			'title'        => __('Sites', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Customers_Site_List_Table(),
			'query_filter' => array($this, 'sites_query_filter'),
		));

		$this->add_list_table_widget('events', array(
			'title'        => __('Events', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
			'query_filter' => array($this, 'events_query_filter'),
		));

		$this->add_fields_widget('save', array(
			'html_attr' => array(
				'data-wu-app' => 'customer_save',
				'data-state'  => json_encode(array(
					'original_user_id'            => $this->get_object()->get_user_id(),
					'user_id'                     => $this->get_object()->get_user_id(),
					'original_email_verification' => $this->get_object()->get_email_verification(),
					'email_verification'          => $this->get_object()->get_email_verification(),
				)),
			),
			'before'    => wu_get_template_contents('customers/widget-avatar', array(
				'customer' => $this->get_object(),
				'user'     => $this->get_object()->get_user(),
			)),
			'fields'    => array(
				'user_id'            => array(
					'type'              => 'model',
					'title'             => __('User', 'wp-ultimo'),
					'placeholder'       => __('Search WordPress user...', 'wp-ultimo'),
					'desc'              => __('The WordPress user associated to this customer.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_user_id(),
					'tooltip'           => '',
					'min'               => 1,
					'html_attr'         => array(
						'v-model'           => 'user_id',
						'data-model'        => 'user',
						'data-value-field'  => 'ID',
						'data-label-field'  => 'display_name',
						'data-search-field' => 'display_name',
						'data-max-items'    => 1,
						'data-selected'     => json_encode($this->get_object()->get_user()->data),
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'transfer_note'      => array(
					'type'              => 'note',
					'desc'              => __('Changing the user will transfer the customer and all its assets to the new user.', 'wp-ultimo'),
					'classes'           => 'wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
					'wrapper_html_attr' => array(
						'v-show'  => '(original_user_id != user_id) && user_id',
						'v-cloak' => '1',
					),
				),
				'email_verification' => array(
					'type'              => 'select',
					'title'             => __('Email Verification', 'wp-ultimo'),
					'placeholder'       => __('Select Status', 'wp-ultimo'),
					'desc'              => __('The email verification status. This gets automatically switched to Verified when the customer verifies their email address.', 'wp-ultimo'),
					'options'           => array(
						'none'     => __('None', 'wp-ultimo'),
						'pending'  => __('Pending', 'wp-ultimo'),
						'verified' => __('Verified', 'wp-ultimo'),
					),
					'value'             => $this->get_object()->get_email_verification(),
					'tooltip'           => '',
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-model' => 'email_verification',
					),
				),
				'confirm_membership' => array(
					'type'              => 'toggle',
					'title'             => __('Activate Memberships', 'wp-ultimo'),
					'desc'              => __('If you toggle this option, this change in status will also activate the related pending memberships. If any sites are pending, they are also going to be published automatically.', 'wp-ultimo'),
					'value'             => 0,
					'wrapper_html_attr' => array(
						'v-if'    => 'email_verification !== original_email_verification && email_verification === "verified" && original_email_verification === "pending"',
						'v-cloak' => '1',
					),
				),
				'send_verification'  => array(
					'type'              => 'submit',
					'title'             => __('Re-send Verification Email &rarr;', 'wp-ultimo'),
					'value'             => 'send_verification',
					'classes'           => 'button wu-w-full',
					'wrapper_html_attr' => array(
						'v-if'    => 'email_verification === "pending" && original_email_verification == "pending"',
						'v-cloak' => '1',
					),
				),
				'submit_save'        => array(
					'type'              => 'submit',
					'title'             => $labels['save_button_label'],
					'placeholder'       => $labels['save_button_label'],
					'value'             => 'save',
					'classes'           => 'button button-primary wu-w-full',
					'wrapper_html_attr' => array(
						'v-show'  => 'original_user_id == user_id || !user_id',
						'v-cloak' => '1',
					),
				),
				'transfer'           => array(
					'type'              => 'link',
					'display_value'     => __('Transfer Customer', 'wp-ultimo'),
					'wrapper_classes'   => 'wu-bg-gray-200',
					'classes'           => 'button wubox wu-w-full wu-text-center',
					'wrapper_html_attr' => array(
						'v-show'  => 'original_user_id != user_id && user_id',
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-bind:href' => "'" . wu_get_form_url('transfer_customer', array(
							'id'             => $this->get_object()->get_id(),
							'target_user_id' => '',
						)) . "=' + user_id",
						'title'       => __('Transfer Customer', 'wp-ultimo'),
					),
				),
			),
		));

		$this->add_fields_widget('last-login', array(
			'title'  => __('Last Login & IPs', 'wp-ultimo'),
			'fields' => array(
				'last_login' => array(
					'edit'          => true,
					'title'         => __('Last Login', 'wp-ultimo'),
					'type'          => 'text-edit',
					'date'          => true,
					'value'         => $this->edit ? $this->get_object()->get_last_login(false) : __('No date', 'wp-ultimo'),
					'display_value' => $this->edit ? $this->get_object()->get_last_login(false) : false,
					'placeholder'   => '2020-04-04 12:00:00',
					'html_attr'     => array(
						'wu-datepicker'   => 'true',
						'data-format'     => 'Y-m-d H:i:S',
						'data-allow-time' => 'true',
					),
				),
				'ips'        => array(
					'title'         => __('IP Address', 'wp-ultimo'),
					'type'          => 'text-edit',
					'display_value' => $this->get_object()->get_last_ip(),
				),
				'country'    => array(
					'title'         => __('IP Address Country', 'wp-ultimo'),
					'type'          => 'text-edit',
					'display_value' => array($this, 'render_country'),
				),
			),
		));

	} // end register_widgets;

	/**
	 * Render the IP info flag.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function render_country() {

		$country_code = $this->get_object()->get_meta('ip_country');

		$country_name = wu_get_country_name($country_code);

		if ($country_code) {

			$html = sprintf('<span>%s</span><span class="wu-flag-icon wu-flag-icon-%s wu-w-5 wu-ml-1" %s></span>',
				$country_name,
				strtolower($country_code),
				wu_tooltip_text($country_name)
			);

		} else {

			$html = $country_name;

		} // end if;

		return $html;

	} // end render_country;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Customer', 'wp-ultimo') : __('Add new Customer', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Customer', 'wp-ultimo');

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
			'edit_label'          => __('Edit Customer', 'wp-ultimo'),
			'add_new_label'       => __('Add new Customer', 'wp-ultimo'),
			'updated_message'     => __('Customer updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Customer', 'wp-ultimo'),
			'title_description'   => '',
			'save_button_label'   => __('Save Customer', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Customer', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function memberships_query_filter($args) {

		$args['customer_id'] = $this->get_object()->get_id();

		return $args;

	} // end memberships_query_filter;

	/**
	 * Filters the list table to return only relevant sites.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function sites_query_filter($args) {

		$args['meta_query'] = array(
			'customer_id' => array(
				'key'   => 'wu_customer_id',
				'value' => $this->get_object()->get_id(),
			),
		);

		return $args;

	} // end sites_query_filter;

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function events_query_filter($args) {

		$extra_args = array(
			'object_type' => 'customer',
			'object_id'   => absint($this->get_object()->get_id()),
		);

		return array_merge($args, $extra_args);

	} // end events_query_filter;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Customer
	 */
	public function get_object() {

		if ($this->object !== null) {

			return $this->object;

		} // end if;

		$item_id = wu_request('id', 0);

		$item = wu_get_customer($item_id);

		if (!$item || $item->get_type() !== 'customer') {

			wp_redirect(wu_network_admin_url('wp-ultimo-customers'));

			exit;

		} // end if;

		$this->object = $item;

		return $this->object;

	} // end get_object;

	/**
	 * Customers have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return false;

	} // end has_title;

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save() {

		if ($_POST['submit_button'] === 'send_verification') {

			$customer = $this->get_object();

			$customer->send_verification_email();

			$redirect_url = wu_network_admin_url('wp-ultimo-edit-customer', array(
				'id'                       => $customer->get_id(),
				'notice_verification_sent' => 1
			));

			wp_redirect($redirect_url);

		 	exit;

		} // end if;

		$object = $this->get_object();

		$_POST['vip'] = wu_request('vip');

		$billing_address = $object->get_billing_address();

		$billing_address->attributes($_POST);

		$valid_address = $billing_address->validate();

		if (is_wp_error($valid_address)) {

			$errors = implode('<br>', $valid_address->get_error_messages());

			WP_Ultimo()->notices->add($errors, 'error', 'network-admin');

			return;

		} // end if;

		$object->set_billing_address($billing_address);

		/**
		 * Deal with publishing sites and memberships
		 */
		$should_confirm_membership = wu_request('confirm_membership', false);

		if ($should_confirm_membership) {

			$this->confirm_memberships();

		} // end if;

		/**
		 * Deal with custom metadata
		 */
		$custom_meta_keys = wu_get_all_customer_meta($this->get_object()->get_id(), true);

		foreach ($custom_meta_keys as $key => $value) {

			wu_update_customer_meta($object->get_id(), $key, wu_request($key), $value['type'], $value['title']);

		} // end foreach;

		parent::handle_save();

	} // end handle_save;

	/**
	 * Handles the email verification sent notice
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_send_verification_notice() {

		if (isset($_GET['notice_verification_sent'])) : ?>

			<div id="message" class="updated notice wu-admin-notice notice-success is-dismissible below-h2">
				<p><?php _e('Verification email sent!', 'wp-ultimo'); ?></p>
			</div>

			<?php

		endif;

	} // end handle_send_verification_notice;

	/**
	 * Adds removable query args to the WP database.
	 *
	 * @param array $removable_query_args Contains the removable args.
	 * @return array
	 */
	public function remove_query_args($removable_query_args) {

		if (!is_array($removable_query_args)) {

			return $removable_query_args;

		} // end if;

		$removable_query_args[] = 'notice_verification_sent';

		return $removable_query_args;

	} // end remove_query_args;

	/**
	 * Confirms the memberships related to a customer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function confirm_memberships() {

		$memberships = $this->get_object()->get_memberships();

		foreach ($memberships as $membership) {

			if ($membership->get_status() === Membership_Status::PENDING) {

				$membership->set_status(Membership_Status::ACTIVE);

				$membership->save();

			} // end if;

		} // end foreach;

	} // end confirm_memberships;

} // end class Customer_Edit_Admin_Page;
