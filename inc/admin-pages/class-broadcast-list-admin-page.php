<?php
/**
 * WP Ultimo Broadcast Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Managers\Broadcast_Manager;

/**
 * WP Ultimo Broadcast Admin Page.
 */
class Broadcast_List_Admin_Page extends List_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-broadcasts';

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
		'network_admin_menu' => 'wu_read_broadcasts',
	);

	/**
	 * Register ajax forms that we use for send broadcasts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Add new broadcast notice.
		 */
		wu_register_form('add_new_broadcast_message', array(
			'render'     => array($this, 'render_add_new_broadcast_modal'),
			'handler'    => array($this, 'handle_add_new_broadcast_modal'),
			'capability' => 'wu_add_broadcasts',
		));

		/**
		 * Ajax to render the broadcast targets modal.
		 */
		add_action('wu_ajax_wu_modal_targets_display', array($this, 'display_targets_modal'));

		/**
		 * Ajax to render the targets modal with customers from a specific membership.
		 */
		add_action('wu_ajax_wu_modal_product_targets_display', array($this, 'display_product_targets_modal'));

	} // end register_forms;

	/**
	 * Enqueue the necessary scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		wp_enqueue_editor();

	} // end register_scripts;

	/**
	 * Renders the broadcast targets modal, when requested.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_targets_modal() {

		$broadcast_id = wu_request('object_id');

		$object = \WP_Ultimo\Models\Broadcast::get_by_id($broadcast_id);

		$target_type = wu_request('target_type');

		$targets = wu_get_broadcast_targets($object->get_id(), $target_type);

		$display_targets = array();

		if ($targets) {

			if ($target_type == 'customers') {

				foreach ($targets as $key => $value) {

					$customer = wu_get_customer($value);

					$url_atts = array(
						'id' => $customer->get_id(),
					);

					$link = wu_network_admin_url('wp-ultimo-edit-customer', $url_atts);

					$avatar = get_avatar($customer->get_user_id(), 48, 'identicon', '', array(
						'force_display' => true,
						'class'         => 'wu-rounded-full',
					));

					$display_targets[$key] = array(
						'link'         => $link,
						'avatar'       => $avatar,
						'display_name' => $customer->get_display_name(),
						'id'           => $customer->get_id(),
						'description'  => $customer->get_email_address(),
					);

				} // end foreach;

			} // end if;

			if ($target_type == 'products') {

				foreach ($targets as $key => $value) {

					$product = wu_get_product($value);

					$url_atts = array(
						'id' => $product->get_id(),
					);

					$link = wu_network_admin_url('wp-ultimo-edit-product', $url_atts);

					$avatar = $product->get_featured_image('thumbnail');

					if ($avatar) {

						$avatar = sprintf('<img class="wu-w-8 wu-h-8 wu-bg-gray-200 wu-rounded-full wu-text-gray-600 wu-flex wu-items-center wu-justify-center" src="%s">', esc_attr($avatar));

					} else {

						$avatar = '<span class="dashicons-wu-image wu-p-1 wu-rounded-full"></span>';

					} // end if;

					$plan_customers = wu_get_membership_customers($product->get_id());

					$customer_count = (int) 0;

					if ($plan_customers) {

						$customer_count = count($plan_customers);

					} // end if;

					$description = sprintf(__('%s customer(s) targeted.', 'wp-ultimo'), $customer_count);

					$display_targets[$key] = array(
						'link'         => $link,
						'avatar'       => $avatar,
						'display_name' => $product->get_name(),
						'id'           => $product->get_id(),
						'description'  => $description,
					);

				} // end foreach;

			} // end if;

		} // end if;

		$args = array(
			'targets'       => $display_targets,
			'wrapper_class' => 'wu-bg-gray-100 wu--mt-3 wu--mb-6 wu-max-h-2',
			'modal_class'   => '',
		);

		wu_get_template('broadcast/widget-targets', $args);

		exit;

	} // end display_targets_modal;

	/**
	 * Renders the broadcast targets modal, when requested.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_product_targets_modal() {

		$product_id = wu_request('product_id');

		$customers = wu_get_membership_customers($product_id);

		$display_targets = array();

		if ($customers) {

			foreach ($customers as $key => $value) {

				$customer = wu_get_customer($value);

				$url_atts = array(
					'id' => $customer->get_id(),
				);

				$link = wu_network_admin_url('wp-ultimo-edit-customer', $url_atts);

				$avatar = get_avatar($customer->get_user_id(), 48, 'identicon', '', array(
					'force_display' => true,
					'class'         => 'wu-rounded-full',
				));

				$display_targets[$key] = array(
					'link'         => $link,
					'avatar'       => $avatar,
					'display_name' => $customer->get_display_name(),
					'id'           => $customer->get_id(),
					'description'  => $customer->get_email_address(),
				);

			} // end foreach;

		} // end if;

		$args = array(
			'targets'       => $display_targets,
			'wrapper_class' => 'wu-bg-gray-100 wu--mt-3 wu--mb-6 wu-max-h-2',
			'modal_class'   => '',
		);

		wu_get_template('broadcast/widget-targets', $args);

		exit;

	} // end display_product_targets_modal;

	/**
	 * Renders the add new broadcast message modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_new_broadcast_modal() {

		$fields = array(
			'type'             => array(
				'type'              => 'select-icon',
				'title'             => __('Broadcast Type', 'wp-ultimo'),
				'desc'              => __('Select the type of message you want to send.', 'wp-ultimo'),
				'placeholder'       => '',
				'tooltip'           => '',
				'value'             => '',
				'classes'           => 'wu-w-1/2',
				'html_attr'         => array(
					'v-model' => 'type',
				),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 1',
				),
				'options'           => array(
					'broadcast_notice' => array(
						'title'   => __('Message', 'wp-ultimo'),
						'tooltip' => __('Display a message on your customers\' dashboard.', 'wp-ultimo'),
						'icon'    => 'dashicons-before dashicons-excerpt-view',
					),
					'broadcast_email'  => array(
						'title'   => __('Email', 'wp-ultimo'),
						'tooltip' => __('Send an email to your customers.', 'wp-ultimo'),
						'icon'    => 'dashicons-before dashicons-email',
					),
				),
			),
			'step_note'        => array(
				'type'              => 'note',
				'desc'              => sprintf('<a href="#" class="wu-no-underline wu-mt-1 wu-uppercase wu-text-2xs wu-font-semibold wu-text-gray-600" v-show="step === 2" v-on:click.prevent="step = 1">%s</a>', __('&larr; Back to Type Selection', 'wp-ultimo')),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 2',
				),
			),
			'target_customers' => array(
				'type'              => 'model',
				'title'             => __('Target Customers', 'wp-ultimo'),
				'desc'              => __('This broadcast will be sent to the user or users that are selected here. You can select more than one.', 'wp-ultimo'),
				'placeholder'       => __('Search a customer...', 'wp-ultimo'),
				'min'               => 1,
				'html_attr'         => array(
					'v-model'           => 'target_customers',
					'data-model'        => 'customer',
					'data-value-field'  => 'id',
					'data-label-field'  => 'display_name',
					'data-search-field' => 'display_name',
					'data-max-items'    => 10000,
				),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 2',
				),
			),
			'target_products'  => array(
				'type'              => 'model',
				'title'             => __('Target Product', 'wp-ultimo'),
				'desc'              => __('This broadcast will be sent to the users that have this product. You can select more than one.', 'wp-ultimo'),
				'placeholder'       => __('Search for a product..', 'wp-ultimo'),
				'html_attr'         => array(
					'v-model'           => 'target_products',
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 99,
				),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 2',
				),
			),
			'notice_type'      => array(
				'title'             => __('Message Type', 'wp-ultimo'),
				'desc'              => __('The color of the notice is based on the type.', 'wp-ultimo'),
				'type'              => 'select',
				'default'           => 'success',
				'options'           => array(
					'success' => __('Success (green)', 'wp-ultimo'),
					'info'    => __('Info (blue)', 'wp-ultimo'),
					'warning' => __('Warning (orange)', 'wp-ultimo'),
					'error'   => __('Error (red)', 'wp-ultimo'),
				),
				'wrapper_html_attr' => array(
					'v-show'  => "step === 2 && require('type', 'broadcast_notice')",
					'v-cloak' => 1
				),
			),
			'step_note_2'      => array(
				'type'              => 'note',
				'desc'              => sprintf('<a href="#" class="wu-no-underline wu-mt-1 wu-uppercase wu-text-2xs wu-font-semibold wu-text-gray-600" v-show="step === 3" v-on:click.prevent="step = 2">%s</a>', __('&larr; Back to Target Selection', 'wp-ultimo')),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 3',
				),
			),
			'subject'          => array(
				'type'              => 'text',
				'title'             => __('Message Subject', 'wp-ultimo'),
				'desc'              => __('The title will appear above the main content in the notice or used as subject of the email.', 'wp-ultimo'),
				'placeholder'       => __('Enter a title for your broadcast.', 'wp-ultimo'),
				'html_attr'         => array(
					'v-model' => 'subject',
				),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 3',
				),
			),
			'content'          => array(
				'id'                => 'content',
				'title'             => __('Content', 'wp-ultimo'),
				'desc'              => __('The main content of your broadcast.', 'wp-ultimo'),
				'type'              => 'wp-editor',
				'settings'          => array(
					'tinymce' => array('toolbar1' => 'bold,italic,strikethrough,link,unlink,undo,redo,pastetext'),
				),
				'html_attr'         => array(
					'v-model' => 'content',
				),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 3',
				),
			),
			'submit_button'    => array(
				'type'              => 'submit',
				'title'             => __('Next Step &rarr;', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'wrapper_html_attr' => array(
					'v-show' => 'step === 1',
				),
				'html_attr'         => array(
					'v-bind:disabled'    => 'type === ""',
					'v-on:click.prevent' => 'step = 2'
				),
			),
			'submit_button_2'  => array(
				'type'              => 'submit',
				'title'             => __('Next Step &rarr;', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'wrapper_html_attr' => array(
					'v-show' => 'step === 2',
				),
				'html_attr'         => array(
					'v-bind:disabled'    => 'target_customers === "" && target_products === ""', // phpcs:ignore
					'v-on:click.prevent' => 'step = 3'
				),
			),
			'submit_button_3'  => array(
				'type'              => 'submit',
				'title'             => __('Send &rarr;', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'html_attr'         => array(
					'v-bind:disabled' => 'subject === ""',
				),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 3',
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('add_new_broadcast', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'add_new_broadcast',
				'data-state'  => wu_convert_to_state(array(
					'type'             => 'broadcast_notice',
					'content'          => '',
					'step'             => 1,
					'confirmed'        => false,
					'target_customers' => '',
					'target_products'  => '',
					'subject'          => '',
				)),
			),
		));

		$form->render();

	} // end render_add_new_broadcast_modal;

	/**
	 * Handles the add new broadcast modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_new_broadcast_modal() {

		$broadcast = Broadcast_Manager::get_instance();

		$broadcast->handle_broadcast();

	} // end handle_add_new_broadcast_modal;

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
			'deleted_message' => __('Broadcast removed successfully.', 'wp-ultimo'),
			'search_label'    => __('Search Broadcast', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Broadcast', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Broadcasts', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Broadcasts', 'wp-ultimo');

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
				'label'   => __('Add Broadcast', 'wp-ultimo'),
				'icon'    => 'wu-circle-with-plus',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('add_new_broadcast_message'),
			),
			array(
				'url'   => wu_network_admin_url('wp-ultimo-emails'),
				'label' => __('System Emails'),
				'icon'  => 'wu-mail',
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

		return new \WP_Ultimo\List_Tables\Broadcast_List_Table();

	} // end table;

} // end class Broadcast_List_Admin_Page;
