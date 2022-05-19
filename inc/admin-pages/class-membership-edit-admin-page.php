<?php
/**
 * WP Ultimo Membership Edit/Add New Admin Page.
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
 * WP Ultimo Membership Edit/Add New Admin Page.
 */
class Membership_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-membership';

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
	public $object_id = 'membership';

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
	protected $highlight_menu_slug = 'wp-ultimo-memberships';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Marks the page as a swap preview.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $is_swap_preview = false;

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
		'network_admin_menu' => 'wu_edit_memberships',
	);

	/**
	 * Override the page load.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function page_loaded() {

		parent::page_loaded();

		/*
		 * Adds the swap notices, if needed.
		 */
		$this->add_swap_notices();

	} // end page_loaded;

	/**
	 * Displays swap notices, if necessary.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function add_swap_notices() {

		$swap_order = $this->get_object()->get_scheduled_swap();

		if (!$swap_order || wu_request('preview-swap')) {

			return;

		} // end if;

		$actions = array(
			'preview' => array(
				'title' => __('Preview', 'wp-ultimo'),
				'url'   => add_query_arg('preview-swap', 1),
			),
		);

		$date = wu_date($swap_order->scheduled_date);

		// translators: %s is the date, using the site format options
		$message = sprintf(__('There is a change scheduled to take place on this membership in <strong>%s</strong>. You can preview the changes here. Scheduled changes are usually created by downgrades.', 'wp-ultimo'), $date->format(get_option('date_format')));

		WP_Ultimo()->notices->add($message, 'warning', 'network-admin', false, $actions);

	} // end add_swap_notices;

	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.4
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

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
		 * Transfer membership - Confirmation modal
		 */
		wu_register_form('transfer_membership', array(
			'render'     => array($this, 'render_transfer_membership_modal'),
			'handler'    => array($this, 'handle_transfer_membership_modal'),
			'capability' => 'wu_transfer_memberships',
		));

		/*
		 * Edit/Add product
		 */
		wu_register_form('edit_membership_product', array(
			'render'  => array($this, 'render_edit_membership_product_modal'),
			'handler' => array($this, 'handle_edit_membership_product_modal'),
		));

		/*
		 * Change Plan
		 */
		wu_register_form('change_membership_plan', array(
			'render'  => array($this, 'render_change_membership_plan_modal'),
			'handler' => array($this, 'handle_change_membership_plan_modal'),
		));

		/*
		 * Delete Product
		 */
		wu_register_form('remove_membership_product', array(
			'render'  => array($this, 'render_remove_membership_product'),
			'handler' => array($this, 'handle_remove_membership_product'),
		));

		add_filter('wu_data_json_success_delete_membership_modal', function($data_json) {
			return array(
				'redirect_url' => wu_network_admin_url('wp-ultimo-memberships', array('deleted' => 1))
			);
		});

	} // end register_forms;

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function render_transfer_membership_modal() {

		$membership = wu_get_membership(wu_request('id'));

		if (!$membership) {

			return;

		} // end if;

		$fields = array(
			'confirm'            => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Transfer', 'wp-ultimo'),
				'desc'      => __('This will start the transfer of assets from one customer to another.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button'      => array(
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
			'id'                 => array(
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			),
			'target_customer_id' => array(
				'type'  => 'hidden',
				'value' => wu_request('target_customer_id'),
			),
		);

		$form = new \WP_Ultimo\UI\Form('total-actions', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'true',
				'data-state'  => json_encode(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_transfer_membership_modal;

	/**
	 * Handles the deletion of line items.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_transfer_membership_modal() {

		$membership = wu_get_membership(wu_request('id'));

		if (!$membership) {

			wp_send_json_error(new \WP_Error('not-found', __('Membership not found.', 'wp-ultimo')));

		} // end if;

		$target_customer = wu_get_customer(wu_request('target_customer_id'));

		if (!$target_customer) {

			wp_send_json_error(new \WP_Error('not-found', __('Target customer not found.', 'wp-ultimo')));

		} // end if;

		if ($target_customer->get_id() === $membership->get_customer_id()) {

			wp_send_json_error(new \WP_Error('not-found', __('Cannot transfer to the same customer.', 'wp-ultimo')));

		} // end if;

		/*
		 * Lock the membership to prevent memberships.
		 */
		$membership->lock();

		/*
		 * Enqueue task
		 */
		wu_enqueue_async_action('wu_async_transfer_membership', array(
			'membership_id'      => $membership->get_id(),
			'target_customer_id' => $target_customer->get_id(),
		), 'membership');

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-edit-membership', array(
				'id'               => $membership->get_id(),
				'transfer-started' => 1,
			))
		));

	} // end handle_transfer_membership_modal;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		$labels = $this->get_labels();

		$label = $this->get_object()->get_status_label();

		$class = $this->get_object()->get_status_class();

		$tag = "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";

		$this->add_fields_widget('at_a_glance', array(
			'title'                 => __('At a Glance', 'wp-ultimo'),
			'position'              => 'normal',
			'classes'               => 'wu-overflow-hidden wu-widget-inset',
			'field_wrapper_classes' => 'wu-w-1/3 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t-0 wu-border-l-0 wu-border-r wu-border-b-0 wu-border-gray-300 wu-border-solid wu-float-left wu-relative',
			'fields'                => array(
				'status'        => array(
					'type'          => 'text-display',
					'title'         => __('Membership Status', 'wp-ultimo'),
					'display_value' => $tag,
					'tooltip'       => '',
				),
				'hash'          => array(
					'copy'          => true,
					'type'          => 'text-display',
					'title'         => __('Reference ID', 'wp-ultimo'),
					'display_value' => $this->get_object()->get_hash(),
				),
				'total_grossed' => array(
					'type'            => 'text-display',
					'title'           => __('Total Grossed', 'wp-ultimo'),
					'display_value'   => wu_format_currency($this->get_object()->get_total_grossed(), $this->get_object()->get_currency()),
					'wrapper_classes' => 'sm:wu-border-r-0',
				),
			),
		));

		$this->add_list_table_widget('membership-products', array(
			'position' => 'normal',
			'title'    => __('Products', 'wp-ultimo'),
			'table'    => new \WP_Ultimo\List_Tables\Membership_Line_Item_List_Table(),
			'after'    => $this->output_widget_products(),
		));

		$this->add_list_table_widget('payments', array(
			'title'        => __('Payments', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Customers_Payment_List_Table(),
			'query_filter' => array($this, 'payments_query_filter'),
		));

		$this->add_list_table_widget('sites', array(
			'title'        => __('Sites', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Memberships_Site_List_Table(),
			'query_filter' => array($this, 'sites_query_filter'),
		));

		$this->add_list_table_widget('customer', array(
			'title'        => __('Linked Customer', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Site_Customer_List_Table(),
			'query_filter' => array($this, 'customer_query_filter'),
		));

		$this->add_tabs_widget('options', array(
			'title'    => __('Membership Options', 'wp-ultimo'),
			'position' => 'normal',
			'sections' => apply_filters('wu_membership_options_sections', array(
				'general'      => array(
					'title'  => __('General', 'wp-ultimo'),
					'desc'   => __('General membership options', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-globe',
					'fields' => array(
						'blocking' => array(
							'type'  => 'toggle',
							'title' => __('Is Blocking?', 'wp-ultimo'),
							'desc'  => __('Should we block access to the site, plugins, themes, and services after the expiration date is reached?', 'wp-ultimo'),
							'value' => true,
						),
					),
				),
				'billing_info' => array(
					'title'  => __('Billing Info', 'wp-ultimo'),
					'desc'   => __('Billing information for this particular membership.', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-address',
					'fields' => $this->get_object()->get_billing_address()->get_fields(),
				),
			), $this->get_object()),
		));

		/*
		 * Hide sensitive things when in swap preview.
		 */
		if (!$this->is_swap_preview) {

			$this->add_list_table_widget('events', array(
				'title'        => __('Events', 'wp-ultimo'),
				'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
				'query_filter' => array($this, 'events_query_filter'),
			));

		} // end if;

		$regular_fields = array(
			'status'         => array(
				'type'              => 'select',
				'title'             => __('Status', 'wp-ultimo'),
				'desc'              => __('The membership current status.', 'wp-ultimo'),
				'value'             => $this->get_object()->get_status(),
				'options'           => Membership_Status::to_array(),
				'tooltip'           => '',
				'html_attr'         => array(
					'v-model' => 'status',
				),
				'wrapper_html_attr' => array(
					'v-cloak' => '1',
				),
			),
			'cancel_gateway' => array(
				'type'              => 'toggle',
				'title'             => __('Cancel on gateway', 'wp-ultimo'),
				'desc'              => __('If enable we will also cancel the subscription on payment method', 'wp-ultimo'),
				'value'             => false,
				'wrapper_html_attr' => array(
					'v-show'  => !empty($this->get_object()->get_gateway_customer_id()) ? 'status == \'cancelled\'' : 'false',
					'v-cloak' => '1',
				),
			),
			'preview-swap'   => array(
				'type'  => 'hidden',
				'value' => wu_request('preview-swap', 0),
			),
			'customer_id'    => array(
				'type'              => 'model',
				'title'             => __('Customer', 'wp-ultimo'),
				'placeholder'       => __('Search a Customer...', 'wp-ultimo'),
				'desc'              => __('The owner of this membership.', 'wp-ultimo'),
				'value'             => $this->get_object()->get_customer_id(),
				'tooltip'           => '',
				'html_attr'         => array(
					'data-base-link'    => wu_network_admin_url('wp-ultimo-edit-customer', array('id' => '')),
					'v-model'           => 'customer_id',
					'data-model'        => 'customer',
					'data-value-field'  => 'id',
					'data-label-field'  => 'display_name',
					'data-search-field' => 'display_name',
					'data-max-items'    => 1,
					'data-selected'     => $this->get_object()->get_customer() ? json_encode($this->get_object()->get_customer()->to_search_results()) : '',
				),
				'wrapper_html_attr' => array(
					'v-cloak' => '1',
				),
			),
			'transfer_note'  => array(
				'type'              => 'note',
				'desc'              => __('Changing the customer will transfer this membership and all its assets, including sites, to the new customer.', 'wp-ultimo'),
				'classes'           => 'wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
				'wrapper_html_attr' => array(
					'v-show'  => '(original_customer_id != customer_id) && customer_id',
					'v-cloak' => '1',
				),
			),
			'submit_save'    => array(
				'type'              => 'submit',
				'title'             => $labels['save_button_label'],
				'placeholder'       => $labels['save_button_label'],
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'html_attr'         => array(),
				'wrapper_html_attr' => array(
					'v-show'  => 'original_customer_id == customer_id || !customer_id',
					'v-cloak' => '1',
				),
			),
			'transfer'       => array(
				'type'              => 'link',
				'display_value'     => __('Transfer Membership', 'wp-ultimo'),
				'wrapper_classes'   => 'wu-bg-gray-200',
				'classes'           => 'button wubox wu-w-full wu-text-center',
				'wrapper_html_attr' => array(
					'v-show'  => 'original_customer_id != customer_id && customer_id',
					'v-cloak' => '1',
				),
				'html_attr'         => array(
					'v-bind:href' => "'" . wu_get_form_url('transfer_membership', array(
						'id'                 => $this->get_object()->get_id(),
						'target_customer_id' => '',
					)) . "=' + customer_id",
					'title'       => __('Transfer Membership', 'wp-ultimo'),
				),
			),
		);

		if ($this->get_object()->is_locked()) {

			unset($regular_fields['transfer_note']);

			unset($regular_fields['transfer']);

			$regular_fields['submit_save']['title']                 = __('Locked', 'wp-ultimo');
			$regular_fields['submit_save']['value']                 = 'none';
			$regular_fields['submit_save']['html_attr']['disabled'] = 'disabled';

		} // end if;

		$this->add_fields_widget('save', array(
			'html_attr' => array(
				'data-wu-app' => 'membership_save',
				'data-state'  => json_encode(array(
					'status'               => $this->get_object()->get_status(),
					'original_customer_id' => $this->get_object()->get_customer_id(),
					'customer_id'          => $this->get_object()->get_customer_id(),
					'plan_id'              => $this->get_object()->get_plan_id(),
				)),
			),
			'fields'    => $regular_fields,
		));

		$this->add_fields_widget('pricing', array(
			'title'     => __('Billing Amount', 'wp-ultimo'),
			'html_attr' => array(
				'data-wu-app' => 'true',
				'data-state'  => json_encode(array(
					'is_recurring'   => $this->get_object()->is_recurring(),
					'is_auto_renew'  => $this->get_object()->should_auto_renew(),
					'amount'         => $this->get_object()->get_amount(),
					'initial_amount' => $this->get_object()->get_initial_amount(),
					'duration'       => $this->get_object()->get_duration(),
					'duration_unit'  => $this->get_object()->get_duration_unit(),
					'gateway'        => $this->get_object()->get_gateway(),
				)),
			),
			'fields'    => array(
				// Fields for price
				'_initial_amount'               => array(
					'type'              => 'text',
					'title'             => __('Initial Amount', 'wp-ultimo'),
					'placeholder'       => sprintf(__('E.g. %s', 'wp-ultimo'), wu_format_currency(199)),
					'desc'              => __('The initial amount collected on the first payment.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_initial_amount(),
					'money'             => true,
					'html_attr'         => array(
						'v-model' => 'initial_amount',
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'initial_amount'                => array(
					'type'      => 'hidden',
					'html_attr' => array(
						'v-model' => 'initial_amount',
					),
				),
				'recurring'                     => array(
					'type'              => 'toggle',
					'title'             => __('Is Recurring', 'wp-ultimo'),
					'desc'              => __('Use this option to manually enable or disable this membership.', 'wp-ultimo'),
					'value'             => $this->get_object()->is_recurring(),
					'html_attr'         => array(
						'v-model' => 'is_recurring',
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'amount'                        => array(
					'type'      => 'hidden',
					'html_attr' => array(
						'v-model' => 'amount',
					),
				),
				'recurring_amount_group'        => array(
					'type'              => 'group',
					'title'             => __('Recurring Amount', 'wp-ultimo'),
					// translators: placeholder %1$s is the amount, %2$s is the duration (such as 1, 2, 3), and %3$s is the unit (such as month, year, week)
					'desc'              => sprintf(__('The customer will be charged %1$s every %2$s %3$s(s).', 'wp-ultimo'), '{{ wu_format_money(amount) }}', '{{ duration }}', '{{ duration_unit }}'),
					'wrapper_html_attr' => array(
						'v-show'  => 'is_recurring',
						'v-cloak' => '1',
					),
					'fields'            => array(
						'_amount'       => array(
							'type'            => 'text',
							'value'           => $this->get_object()->get_amount(),
							'placeholder'     => wu_format_currency('99'),
							'wrapper_classes' => '',
							'money'           => true,
							'html_attr'       => array(
								'v-model' => 'amount',
							),
						),
						'duration'      => array(
							'type'            => 'number',
							'value'           => $this->get_object()->get_duration(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-mx-2 wu-w-1/3',
							'min'             => 0,
							'html_attr'       => array(
								'v-model' => 'duration',
								'steps'   => 1,
							),
						),
						'duration_unit' => array(
							'type'            => 'select',
							'value'           => $this->get_object()->get_duration_unit(),
							'placeholder'     => '',
							'wrapper_classes' => 'wu-w-2/3',
							'html_attr'       => array(
								'v-model' => 'duration_unit',
							),
							'options'         => array(
								'day'   => __('Days', 'wp-ultimo'),
								'week'  => __('Weeks', 'wp-ultimo'),
								'month' => __('Months', 'wp-ultimo'),
								'year'  => __('Years', 'wp-ultimo'),
							),
						),
					),
				),
				'billing_cycles'                => array(
					'type'              => 'number',
					'title'             => __('Billing Cycles', 'wp-ultimo'),
					'placeholder'       => __('E.g. 0', 'wp-ultimo'),
					'desc'              => __('How many times should we bill this customer. Leave 0 to charge until cancelled.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_billing_cycles(),
					'min'               => 0,
					'wrapper_html_attr' => array(
						'v-show'  => 'is_recurring',
						'v-cloak' => '1',
					),
				),
				'times_billed'                  => array(
					'type'              => 'number',
					'title'             => __('Times Billed', 'wp-ultimo'),
					'desc'              => __('The number of times this membership was billed so far.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_times_billed(),
					'min'               => 0,
					'wrapper_html_attr' => array(
						'v-show'  => 'is_recurring',
						'v-cloak' => '1',
					),
				),

				'auto_renew'                    => array(
					'type'              => 'toggle',
					'title'             => __('Auto-Renew?', 'wp-ultimo'),
					'desc'              => __('Activating this will tell the gateway to try to automatically charge for this membership.', 'wp-ultimo'),
					'value'             => $this->get_object()->should_auto_renew(),
					'wrapper_html_attr' => array(
						'v-show'  => 'is_recurring',
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-model' => 'is_auto_renew',
					),
				),
				'gateway'                       => array(
					'type'              => 'text',
					'title'             => __('Gateway', 'wp-ultimo'),
					'placeholder'       => __('e.g. stripe', 'wp-ultimo'),
					'description'       => __('e.g. stripe', 'wp-ultimo'),
					'desc'              => __('Payment gateway used to process the payment.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_gateway(),
					'wrapper_classes'   => 'wu-w-full',
					'html_attr'         => array(
						'v-on:input'   => 'gateway = $event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, "")',
						'v-bind:value' => 'gateway',
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'gateway_customer_id_group'     => array(
					'type'              => 'group',
					'desc'              => function() {

						$gateway_id = $this->get_object()->get_gateway();

						if (empty($this->get_object()->get_gateway_customer_id())) {

							return '';

						} // end if;

						$url = apply_filters("wu_{$gateway_id}_remote_customer_url", $this->get_object()->get_gateway_customer_id());

						if ($url) {

							return sprintf('<a class="wu-text-gray-800 wu-text-center wu-w-full wu-no-underline" href="%s" target="_blank">%s</a>', esc_attr($url), __('View on Gateway &rarr;', 'wp-ultimo'));

						} // end if;

						return '';

					},
					'wrapper_html_attr' => array(
						'v-show'  => 'is_recurring && is_auto_renew',
						'v-cloak' => '1',
					),
					'fields'            => array(
						'gateway_customer_id' => array(
							'type'              => 'text',
							'title'             => __('Gateway Customer ID', 'wp-ultimo'),
							'placeholder'       => __('Gateway Customer ID', 'wp-ultimo'),
							'value'             => $this->get_object()->get_gateway_customer_id(),
							'tooltip'           => '',
							'wrapper_classes'   => 'wu-w-full',
							'html_attr'         => array(),
							'wrapper_html_attr' => array(),
						),
					),
				),

				'gateway_subscription_id_group' => array(
					'type'              => 'group',
					'desc'              => function() {

						$gateway_id = $this->get_object()->get_gateway();

						if (empty($this->get_object()->get_gateway_subscription_id())) {

							return '';

						} // end if;

						$url = apply_filters("wu_{$gateway_id}_remote_subscription_url", $this->get_object()->get_gateway_subscription_id());

						if ($url) {

							return sprintf('<a class="wu-text-gray-800 wu-text-center wu-w-full wu-no-underline" href="%s" target="_blank">%s</a>', esc_attr($url), __('View on Gateway &rarr;', 'wp-ultimo'));

						} // end if;

						return '';

					},
					'wrapper_html_attr' => array(
						'v-show'  => 'is_recurring && is_auto_renew',
						'v-cloak' => '1',
					),
					'fields'            => array(
						'gateway_subscription_id' => array(
							'type'              => 'text',
							'title'             => __('Gateway Subscription ID', 'wp-ultimo'),
							'placeholder'       => __('Gateway Subscription ID', 'wp-ultimo'),
							'value'             => $this->get_object()->get_gateway_subscription_id(),
							'tooltip'           => '',
							'wrapper_classes'   => 'wu-w-full',
							'html_attr'         => array(),
							'wrapper_html_attr' => array(),
						),
					),
				),

				/*
				 * @todo: re-add this in the future.
				 */
				'gateway_sync_button'           => array(
					'type'              => 'submit',
					'title'             => __('Sync with Gateway', 'wp-ultimo'),
					'value'             => 'save',
					'classes'           => 'button wu-w-full',
					'wrapper_html_attr' => array(
						'v-show'  => 'is_recurring && is_auto_renew && false',
						'v-cloak' => '1',
					),
				),

			),
		));

		$timestamp_fields = array();

		$timestamps = array(
			'date_expiration'   => __('Expires at', 'wp-ultimo'),
			'date_renewed'      => __('Last Renewed at', 'wp-ultimo'),
			'date_trial_end'    => __('Trial Ends at', 'wp-ultimo'),
			'date_cancellation' => __('Cancelled at', 'wp-ultimo'),
		);

		foreach ($timestamps as $timestamp_name => $timestamp_label) {

			$value = $this->get_object()->{"get_$timestamp_name"}();

			$timestamp_fields[$timestamp_name] = array(
				'title'         => $timestamp_label,
				'type'          => 'text-edit',
				'date'          => true,
				'edit'          => true,
				'display_value' => $this->edit ? $value : '',
				'value'         => $value,
				'placeholder'   => '2020-04-04 12:00:00',
				'html_attr'     => array(
					'wu-datepicker'   => 'true',
					'data-format'     => 'Y-m-d H:i:S',
					'data-allow-time' => 'true',
				),
			);

		} // end foreach;

		if (!$this->get_object()->is_lifetime()) {

			$timestamp_fields['convert_to_lifetime'] = array(
				'type'              => 'submit',
				'title'             => __('Convert to Lifetime', 'wp-ultimo'),
				'value'             => 'convert_to_lifetime',
				'classes'           => 'button wu-w-full',
				'wrapper_html_attr' => array(),
			);

		} // end if;

		$this->add_fields_widget('membership-timestamps', array(
			'title'  => __('Important Timestamps', 'wp-ultimo'),
			'fields' => $timestamp_fields,
		));

	} // end register_widgets;

	/**
	 * Renders the widget used to display the product list.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function output_widget_products() {

		return wu_get_template_contents('memberships/product-list', array(
			'membership' => $this->get_object(),
		));

	} // end output_widget_products;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Membership', 'wp-ultimo') : __('Add new Membership', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Membership', 'wp-ultimo');

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
			'edit_label'          => __('Edit Membership', 'wp-ultimo'),
			'add_new_label'       => __('Add new Membership', 'wp-ultimo'),
			'updated_message'     => __('Membership updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Membership Name', 'wp-ultimo'),
			'title_description'   => __('This name will be used on pricing tables, invoices, and more.', 'wp-ultimo'),
			'save_button_label'   => __('Save Membership', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Membership', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Filters the list table to return only relevant payments.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function payments_query_filter($args) {

		$args['membership_id'] = $this->get_object()->get_id();

		return $args;

	} // end payments_query_filter;

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
			'membership_id' => array(
				'key'   => 'wu_membership_id',
				'value' => $this->get_object()->get_id(),
			),
		);

		return $args;

	} // end sites_query_filter;

	/**
	 * Filters the list table to return only relevant customer.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function customer_query_filter($args) {

		$args['id'] = $this->get_object()->get_customer_id();

		return $args;

	} // end customer_query_filter;

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
			'object_type' => 'membership',
			'object_id'   => absint($this->get_object()->get_id()),
		);

		return array_merge($args, $extra_args);

	} // end events_query_filter;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Membership
	 */
	public function get_object() {

		if ($this->object !== null) {

			return $this->object;

		} // end if;

		$item_id = wu_request('id', 0);

		$item = wu_get_membership($item_id);

		if (!$item) {

			wp_redirect(wu_network_admin_url('wp-ultimo-memberships'));

			exit;

		} // end if;

		$this->object = $item;

		/**
		 * Deal with scheduled swaps.
		 */
		if (wu_request('preview-swap')) {

			$swap_order = $this->get_object()->get_scheduled_swap();

			if (!$swap_order) {

				return $this->object;

			} // end if;

			$this->is_swap_preview = true;

			$actions = array(
				'preview' => array(
					'title' => __('&larr; Go back', 'wp-ultimo'),
					'url'   => remove_query_arg('preview-swap', wu_get_current_url()),
				),
			);

			$date = wu_date($swap_order->scheduled_date);

			// translators: %s is the date, using the site format options
			$message = sprintf(__('This is a <strong>preview</strong>. This page displays the final stage of the membership after the changes scheduled for <strong>%s</strong>. Saving here will persist these changes, so be careful.', 'wp-ultimo'), $date->format(get_option('date_format')));

			WP_Ultimo()->notices->add($message, 'info', 'network-admin', false, $actions);

			$this->object->swap($swap_order->order);

		} // end if;

		return $this->object;

	} // end get_object;

	/**
	 * Memberships have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return false;

	} // end has_title;

	/**
	 * Handle convert to lifetime.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function handle_convert_to_lifetime() {

		$object = $this->get_object();

		$object->set_date_expiration(null);

		$save = $object->save();

		if (is_wp_error($save)) {

			$errors = implode('<br>', $save->get_error_messages());

			WP_Ultimo()->notices->add($errors, 'error', 'network-admin');

			return false;

		} // end if;

		$array_params = array(
			'updated' => 1,
		);

		if ($this->edit === false) {

			$array_params['id'] = $object->get_id();

		} // end if;

		$url = add_query_arg($array_params);

		wp_redirect($url);

		return true;

	} // end handle_convert_to_lifetime;

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return true
	 */
	public function handle_save() {

		$object = $this->get_object();

		// Cancel membership on gateway
		if ((bool) wu_request('cancel_gateway', false) && wu_request('status', Membership_Status::CANCELLED)) {

			$gateway = wu_get_gateway(wu_request('gateway'));

			if ($gateway) {

				$gateway->process_cancellation($object, $object->get_customer());

				$_POST['gateway'] = '';

			} // end if;

		} // end if;

		if (wu_request('submit_button') === 'convert_to_lifetime') {

			return $this->handle_convert_to_lifetime();

		} // end if;

		$_POST['auto_renew'] = (bool) wu_request('auto_renew', false);

		$billing_address = $object->get_billing_address();

		$billing_address->attributes($_POST);

		$valid_address = $billing_address->validate();

		if (is_wp_error($valid_address)) {

			$errors = implode('<br>', $valid_address->get_error_messages());

			WP_Ultimo()->notices->add($errors, 'error', 'network-admin');

			return false;

		} // end if;

		$object->set_billing_address($billing_address);

		ob_start();

		$status = parent::handle_save();

		if ($this->is_swap_preview) {

			ob_clean();

			$object->delete_scheduled_swap();

			$array_params = array(
				'updated' => 1,
			);

			$url = add_query_arg($array_params);

			$url = remove_query_arg('preview-swap', $url);

			wp_redirect($url);

			return true;

		} // end if;

		return $status;

	} // end handle_save;

	/**
	 * Renders the add/edit line items form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_edit_membership_product_modal() {

		$membership = wu_get_membership(wu_request('id'));

		if (!$membership) {

			return;

		} // end if;

		$fields = array(
			'product_id'        => array(
				'type'        => 'model',
				'title'       => __('Product', 'wp-ultimo'),
				'placeholder' => __('Search product...', 'wp-ultimo'),
				'value'       => '',
				'tooltip'     => '',
				'html_attr'   => array(
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 1,
					'data-selected'     => '',
				),
			),
			'quantity'          => array(
				'type'            => 'number',
				'title'           => __('Quantity', 'wp-ultimo'),
				'value'           => 1,
				'placeholder'     => 1,
				'wrapper_classes' => 'wu-w-1/2',
				'html_attr'       => array(
					'min'      => 1,
					'required' => 'required',
				),
			),
			'update_price'      => array(
				'type'      => 'toggle',
				'title'     => __('Update Pricing', 'wp-ultimo'),
				'desc'      => __('Checking this box will update the membership pricing. Otherwise, the products will be added without changing the membership prices.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'update_pricing',
				),
			),
			'sync_with_gateway' => array(
				'type'              => 'toggle',
				'title'             => __('Sync with Payment Gateway', 'wp-ultimo'),
				'desc'              => __('Checking this box will trigger a sync event to update the membership on the payment gateway associated with this membership. Not all payment gateways offer support to this feature.', 'wp-ultimo'),
				'html_attr'         => array(
					'v-model' => 'sync_with_gateway',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'update_pricing',
					'v-cloak' => '1',
				),
			),
			'transfer_note'     => array(
				'type'              => 'note',
				'desc'              => __('The payment gateway currently linked to this membership does not support subscription updates. If you move forward, the auto-renew status of this membership will be disabled and your customer will be required to renew it manually.', 'wp-ultimo'),
				'classes'           => 'sm:wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
				'wrapper_html_attr' => array(
					'v-show'  => 'update_pricing && sync_with_gateway',
					'v-cloak' => '1',
				),
			),
			'submit_button'     => array(
				'type'            => 'submit',
				'title'           => __('Add Product', 'wp-ultimo'),
				'placeholder'     => __('Add Product', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
			),
			'id'                => array(
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			),
		);

		$form = new \WP_Ultimo\UI\Form('edit_membership_product', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'edit_membership_product',
				'data-state'  => wu_convert_to_state(array(
					'update_pricing'    => 0,
					'sync_with_gateway' => 1,
				)),
			),
		));

		$form->render();

	} // end render_edit_membership_product_modal;

	/**
	 * Handles the add/edit of line items.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_edit_membership_product_modal() {

		$membership = wu_get_membership(wu_request('id'));

		if (!$membership) {

			$error = new \WP_Error('membership-not-found', __('Membership not found.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$product = wu_get_product(wu_request('product_id'));

		if (!$product) {

			$error = new \WP_Error('product-not-found', __('Product not found.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$membership->add_product($product->get_id(), (int) wu_request('quantity', 1));

		$saved = $membership->save();

		if (is_wp_error($saved)) {

			wp_send_json_error($saved);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('updated', 1, $_SERVER['HTTP_REFERER']),
		));

	} // end handle_edit_membership_product_modal;

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_remove_membership_product() {

		$membership = wu_get_membership(wu_request('id'));

		if (!$membership) {

			return;

		} // end if;

		$fields = array(
			'quantity'          => array(
				'type'            => 'number',
				'title'           => __('Quantity', 'wp-ultimo'),
				'value'           => 1,
				'placeholder'     => 1,
				'wrapper_classes' => 'wu-w-1/2',
				'html_attr'       => array(
					'min'      => 1,
					'required' => 'required',
				),
			),
			'update_price'      => array(
				'type'      => 'toggle',
				'title'     => __('Update Pricing?', 'wp-ultimo'),
				'desc'      => __('Checking this box will update the membership pricing. Otherwise, the products will be added without changing the membership prices.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'update_pricing',
				),
			),
			'sync_with_gateway' => array(
				'type'              => 'toggle',
				'title'             => __('Sync with Payment Gateway?', 'wp-ultimo'),
				'desc'              => __('Checking this box will trigger a sync event to update the membership on the payment gateway associated with this membership. Not all payment gateways offer support to this feature.', 'wp-ultimo'),
				'html_attr'         => array(
					'v-model' => 'sync_with_gateway',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'update_pricing',
					'v-cloak' => '1',
				),
			),
			'transfer_note'     => array(
				'type'              => 'note',
				'desc'              => __('The payment gateway currently linked to this membership does not support subscription updates. If you move forward, the auto-renew status of this membership will be disabled and your customer will be required to renew it manually.', 'wp-ultimo'),
				'classes'           => 'sm:wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
				'wrapper_html_attr' => array(
					'v-show'  => 'update_pricing && sync_with_gateway',
					'v-cloak' => '1',
				),
			),
			'submit_button'     => array(
				'type'            => 'submit',
				'title'           => __('Remove Product', 'wp-ultimo'),
				'placeholder'     => __('Remove Product', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
			),
			'id'                => array(
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			),
			'product_id'        => array(
				'type'  => 'hidden',
				'value' => wu_request('product_id', 0),
			),
		);

		$form = new \WP_Ultimo\UI\Form('edit_membership_product', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'edit_membership_product',
				'data-state'  => wu_convert_to_state(array(
					'update_pricing'    => 0,
					'sync_with_gateway' => 1,
				)),
			),
		));

		$form->render();

	} // end render_remove_membership_product;

	/**
	 * Handles the deletion of line items.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_remove_membership_product() {

		$membership = wu_get_membership(wu_request('id'));

		if (!$membership) {

			$error = new \WP_Error('membership-not-found', __('Membership not found.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$product = wu_get_product(wu_request('product_id'));

		if (!$product) {

			$error = new \WP_Error('product-not-found', __('Product not found.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$membership->remove_product($product->get_id(), (int) wu_request('quantity', 1));

		$saved = $membership->save();

		if (is_wp_error($saved)) {

			wp_send_json_error($saved);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('updated', 1, $_SERVER['HTTP_REFERER']),
		));

	} // end handle_remove_membership_product;

	/**
	 * Renders the add/edit line items form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_change_membership_plan_modal() {

		$membership = wu_get_membership(wu_request('id'));

		if (!$membership) {

			return;

		} // end if;

		$product = wu_get_product(wu_request('product_id'));

		if (!$product) {

			return;

		} // end if;

		$fields = array(
			'plan_id'           => array(
				'type'        => 'model',
				'title'       => __('Plan', 'wp-ultimo'),
				'placeholder' => __('Search new Plan...', 'wp-ultimo'),
				'desc'        => __('Select a new plan for this membership.', 'wp-ultimo'),
				'value'       => $product->get_id(),
				'tooltip'     => '',
				'html_attr'   => array(
					'data-model'        => 'plan',
					'v-model'           => 'plan_id',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 1,
					'data-selected'     => json_encode($product->to_search_results()),
				),
			),
			'update_price'      => array(
				'type'      => 'toggle',
				'title'     => __('Update Pricing', 'wp-ultimo'),
				'desc'      => __('Checking this box will update the membership pricing. Otherwise, the products will be added without changing the membership prices.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'update_pricing',
				),
			),
			'sync_with_gateway' => array(
				'type'              => 'toggle',
				'title'             => __('Sync with Payment Gateway', 'wp-ultimo'),
				'desc'              => __('Checking this box will trigger a sync event to update the membership on the payment gateway associated with this membership. Not all payment gateways offer support to this feature.', 'wp-ultimo'),
				'html_attr'         => array(
					'v-model' => 'sync_with_gateway',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'update_pricing',
					'v-cloak' => '1',
				),
			),
			'transfer_note'     => array(
				'type'              => 'note',
				'desc'              => __('The payment gateway currently linked to this membership does not support subscription updates. If you move forward, the auto-renew status of this membership will be disabled and your customer will be required to renew it manually.', 'wp-ultimo'),
				'classes'           => 'sm:wu-p-2 wu-bg-red-100 wu-text-red-600 wu-rounded wu-w-full',
				'wrapper_html_attr' => array(
					'v-show'  => 'update_pricing && sync_with_gateway',
					'v-cloak' => '1',
				),
			),
			'submit_button'     => array(
				'type'            => 'submit',
				'title'           => __('Change Product', 'wp-ultimo'),
				'placeholder'     => __('Change Product', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:class'    => 'plan_id == original_plan_id ? "button-disabled" : ""',
					'v-bind:disabled' => 'plan_id == original_plan_id',
				),
			),
			'id'                => array(
				'type'  => 'hidden',
				'value' => $membership->get_id(),
			),
		);

		$form = new \WP_Ultimo\UI\Form('change_membership_plan', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'change_membership_plan',
				'data-state'  => wu_convert_to_state(array(
					'update_pricing'    => 0,
					'sync_with_gateway' => 1,
					'original_plan_id'  => $product->get_id(),
					'plan_id'           => $product->get_id(),
				)),
			),
		));

		$form->render();

	} // end render_change_membership_plan_modal;

	/**
	 * Handles the add/edit of line items.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_change_membership_plan_modal() {

		$membership = wu_get_membership(wu_request('id'));

		if (!$membership) {

			$error = new \WP_Error('membership-not-found', __('Membership not found.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$plan = wu_get_product(wu_request('plan_id'));

		if (!$plan) {

			$error = new \WP_Error('plan-not-found', __('Plan not found.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$original_plan_id = $membership->get_plan_id();

		if (absint($original_plan_id) === absint($plan->get_id())) {

			$error = new \WP_Error('same-plan', __('No change performed. The same plan selected.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$membership->set_plan_id($plan->get_id());

		$saved = $membership->save();

		if (is_wp_error($saved)) {

			wp_send_json_error($saved);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('updated', 1, $_SERVER['HTTP_REFERER']),
		));

	} // end handle_change_membership_plan_modal;

} // end class Membership_Edit_Admin_Page;
