<?php
/**
 * WP Ultimo Payment Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Payment;
use \WP_Ultimo\Database\Payments\Payment_Status;

/**
 * WP Ultimo Payment Edit/Add New Admin Page.
 */
class Payment_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-payment';

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
	public $object_id = 'payment';

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
	protected $highlight_menu_slug = 'wp-ultimo-payments';

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
		'network_admin_menu' => 'wu_edit_payments',
	);

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
	 * Register ajax forms that we use for payments.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Edit/Add Line Item
		 */
		wu_register_form('edit_line_item', array(
			'render'     => array($this, 'render_edit_line_item_modal'),
			'handler'    => array($this, 'handle_edit_line_item_modal'),
			'capability' => 'wu_edit_payments',
		));

		/*
		 * Delete Line Item - Confirmation modal
		 */
		wu_register_form('delete_line_item', array(
			'render'     => array($this, 'render_delete_line_item_modal'),
			'handler'    => array($this, 'handle_delete_line_item_modal'),
			'capability' => 'wu_delete_payments',
		));

		/*
		 * Refund Line Item
		 */
		wu_register_form('refund_payment', array(
			'render'     => array($this, 'render_refund_payment_modal'),
			'handler'    => array($this, 'handle_refund_payment_modal'),
			'capability' => 'wu_refund_payments',
		));

		/*
		 * Delete - Confirmation modal
		 */
		add_filter('wu_data_json_success_delete_payment_modal', function($data_json) {
			return array(
				'redirect_url' => wu_network_admin_url('wp-ultimo-payments', array('deleted' => 1))
			);
		});

	} // end register_forms;

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_delete_line_item_modal() {

		$payment = wu_get_payment(wu_request('id'));

		$line_item = wu_get_line_item(wu_request('line_item_id'), $payment->get_id());

		if (!$line_item || !$payment) {

			return;

		} // end if;

		$fields = array(
			'confirm'       => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Deletion', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => __('Delete', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
			'id'            => array(
				'type'  => 'hidden',
				'value' => $payment->get_id(),
			),
			'line_item_id'  => array(
				'type'  => 'hidden',
				'value' => $line_item->get_id(),
			),
		);

		$form = new \WP_Ultimo\UI\Form('total-actions', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'true',
				'data-state'  => wu_convert_to_state(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_delete_line_item_modal;

	/**
	 * Handles the deletion of line items.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_delete_line_item_modal() {

		$payment = wu_get_payment(wu_request('id'));

		$line_item = wu_get_line_item(wu_request('line_item_id'), $payment->get_id());

		if (!$payment || !$line_item) {

			wp_send_json_error(new \WP_Error('not-found', __('Payment not found.', 'wp-ultimo')));

		} // end if;

		$line_items = $payment->get_line_items();

		unset($line_items[$line_item->get_id()]);

		$payment->set_line_items($line_items);

		$saved = $payment->recalculate_totals()->save();

		if (is_wp_error($saved)) {

			wp_send_json_error($saved);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('updated', 1, $_SERVER['HTTP_REFERER']),
		));

	} // end handle_delete_line_item_modal;

	/**
	 * Renders the refund line item modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function render_refund_payment_modal() {

		$payment = wu_get_payment(wu_request('id'));

		if (!$payment) {

			return;

		} // end if;

		$fields = array(
			'_amount'                    => array(
				'type'              => 'text',
				'title'             => __('Refund Amount', 'wp-ultimo'),
				'placeholder'       => __('Refund Amount', 'wp-ultimo'),
				'money'             => true,
				'min'               => 0,
				'html_attr'         => array(
					'v-model'    => 'amount',
					'step'       => '0.01',
					'v-bind:max' => 'total'
				),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 1',
				),
			),
			'amount'           => array(
				'type'      => 'hidden',
				'html_attr' => array(
					'v-model' => 'amount',
				),
			),
			'cancel_membership'         => array(
				'type'              => 'toggle',
				'title'             => __('Cancel Related Membership?', 'wp-ultimo'),
				'desc'              => __('Checking this option will cancel the membership as well.', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 1',
				),
			),
			'refund_not_immediate_note' => array(
				'type'              => 'note',
				'desc'              => __('Confirming the refund might not immediately change the status of the payment, as each gateway handles refunds differently and WP Ultimo relies on the gateway reporting a successful refund before changing the status.', 'wp-ultimo'),
				'classes'           => 'wu-p-2 wu-bg-yellow-200 wu-text-yellow-700 wu-rounded wu-w-full',
				'wrapper_html_attr' => array(
					'v-show'  => 'step === 2',
					'v-cloak' => '1',
				),
			),
			'confirm'                   => array(
				'type'              => 'toggle',
				'title'             => __('Confirm Refund', 'wp-ultimo'),
				'desc'              => __('This action can not be undone.', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 2',
				),
				'html_attr'         => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button'             => array(
				'type'              => 'submit',
				'title'             => __('Next Step', 'wp-ultimo'),
				'placeholder'       => __('Next Step', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'wrapper_html_attr' => array(
					'v-show' => 'step === 1',
				),
				'html_attr'         => array(
					'v-bind:disabled'    => 'amount <= 0 || amount > total',
					'v-on:click.prevent' => 'step = 2'
				),
			),
			'submit_button_2'           => array(
				'type'              => 'submit',
				'title'             => __('Issue Refund', 'wp-ultimo'),
				'placeholder'       => __('Issue Refund', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'html_attr'         => array(
					'v-bind:disabled' => '!confirmed',
				),
				'wrapper_html_attr' => array(
					'v-show' => 'step === 2',
				),
			),
			'id'                        => array(
				'type'  => 'hidden',
				'value' => $payment->get_id(),
			),
		);

		$form = new \WP_Ultimo\UI\Form('total-actions', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'refund',
				'data-state'  => wu_convert_to_state(array(
					'step'      => 1,
					'confirmed' => false,
					'total'     => round($payment->get_total(), 2),
					'amount'    => round($payment->get_total(), 2),
				)),
			),
		));

		$form->render();

	} // end render_refund_payment_modal;

	/**
	 * Handles the deletion of line items.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_refund_payment_modal() {

		$amount = wu_to_float(wu_request('amount'));

		$payment = wu_get_payment(wu_request('id'));

		if (!$payment) {

			wp_send_json_error(new \WP_Error('not-found', __('Payment not found.', 'wp-ultimo')));

		} // end if;

		/*
		 * Checks for a valid amount.
		 */
		if (empty($amount) || $amount > $payment->get_total()) {

			wp_send_json_error(new \WP_Error('invalid-amount', __('The refund amount is out of bounds.', 'wp-ultimo')));

		} // end if;

		/*
		 * Check if the payment is in a
		 * refundable status.
		 */
		$is_refundable = in_array($payment->get_status(), wu_get_refundable_payment_types(), true);

		if (!$is_refundable) {

			wp_send_json_error(new \WP_Error('payment-not-refunded', __('This payment is not in a refundable state.', 'wp-ultimo')));

		} // end if;

		/*
		 * First we set the flag to cancel membership
		 * if we need to.
		 *
		 * This MUST be handled by the gateway when
		 * receiving the webhook call confirming
		 * the refund was successful.
		 */
		$should_cancel_membership_on_refund = wu_request('cancel_membership');

		$payment->set_cancel_membership_on_refund($should_cancel_membership_on_refund);

		/*
		 * Get the gateway.
		 */
		$gateway_id = $payment->get_gateway();

		if (!$gateway_id) {
			/*
			 * The payment does not have a
			 * gateway attached to it.
			 * Immediately refunds.
			 */
			$status = $payment->refund($amount, $should_cancel_membership_on_refund);

			if (is_wp_error($status)) {

				wp_send_json_error($status);

			} // end if;

			/*
			 * Done! Redirect back.
			 */
			wp_send_json_success(array(
				'redirect_url' => wu_network_admin_url('wp-ultimo-edit-payment', array(
					'id'      => $payment->get_id(),
					'updated' => 1,
				)),
			));

		} // end if;

		$gateway = wu_get_gateway($gateway_id);

		if (!$gateway) {

			wp_send_json_error(new \WP_Error('gateway-not-found', __('Payment gateway not found.', 'wp-ultimo')));

		} // end if;

		/*
		 * Process the refund on the gateway.
		 */
		try {
			/*
			 * We set the cancel membership flag, so we
			 * need to save it so the gateway can use it
			 * later.
			 */
			$payment->save();

			/*
			 * After that, we create the objects we need to pass over
			 * to the gateway.
			 */
			$membership = $payment->get_membership();
			$customer   = $payment->get_customer();

			/*
			 * Passes it over to the gateway
			 */
			$status = $gateway->process_refund($amount, $payment, $membership, $customer);

			if (is_wp_error($status)) {

				// translators: %s is the exception error message.
				$error = new \WP_Error('refund-error', sprintf(__('An error occurred: %s', 'wp-ultimo'), $status->get_error_message()));

				wp_send_json_error($error);

			} // end if;

		} catch (\Throwable $e) {

			// translators: %s is the exception error message.
			$error = new \WP_Error('refund-error', sprintf(__('An error occurred: %s', 'wp-ultimo'), $e->getMessage()));

			wp_send_json_error($error);

		} // end try;

		/*
		 * Done! Redirect back.
		 */
		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url('wp-ultimo-edit-payment', array(
				'id'      => $payment->get_id(),
				'updated' => 1,
			)),
		));

	} // end handle_refund_payment_modal;

	/**
	 * Handles the add/edit of line items.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_edit_line_item_modal() {

		$payment = wu_get_payment(wu_request('payment_id'));

		$line_item = wu_get_line_item(wu_request('line_item_id'), $payment->get_id());

		if (!$line_item) {

			$line_item = new \WP_Ultimo\Checkout\Line_Item(array());

		} // end if;

		/*
		 * First, we get the type.
		 *
		 * We basically have 4 types:
		 * 1. Product
		 * 2. Fee
		 * 3. Credit
		 * 4. Refund
		 */
		$type = wu_request('type', 'product');

		if ($type === 'product') {

			$product = wu_get_product(wu_request('product_id'));

			if (empty($product)) {

				$error = new \WP_Error('missing-product', __('The product was not found.', 'wp-ultimo'));

				wp_send_json_error($error);

			} // end if;

			/*
			 * Constructs the arguments
			 * for the product line item.
			 */
			$atts = array(
				'product'     => $product,
				'quantity'    => wu_request('quantity', 1),
				'unit_price'  => wu_to_float(wu_request('unit_price')),
				'title'       => wu_request('title'),
				'description' => wu_request('description'),
				'tax_rate'    => wu_request('tax_rate', 0),
				'tax_type'    => wu_request('tax_type', 'percentage'),
				'tax_label'   => wu_request('tax_label', ''),
			);

		} else {

			/**
			 * Now, we deal with all the
			 * types.
			 *
			 * First, check the valid types.
			 */
			$allowed_types = apply_filters('wu_allowed_line_item_types', array(
				'fee',
				'refund',
				'credit',
			));

			if (!in_array($type, $allowed_types, true)) {

				$error = new \WP_Error('invalid-type', __('The line item type is invalid.', 'wp-ultimo'));

				wp_send_json_error($error);

			} // end if;

			/*
			 * Set the new attributes
			 */
			$atts = array(
				'quantity'    => 1,
				'title'       => wu_request('title', ''),
				'description' => wu_request('description', '--'),
				'unit_price'  => wu_to_float(wu_request('unit_price')),
				'tax_rate'    => 0,
				'tax_type'    => 'percentage',
				'tax_label'   => '',
			);

		} // end if;

		$line_item->attributes($atts);

		$line_item->recalculate_totals();

		$line_items = $payment->get_line_items();

		$line_items[$line_item->get_id()] = $line_item;

		$payment->set_line_items($line_items);

		$saved = $payment->recalculate_totals()->save();

		if (!$saved) {

			wp_send_json_error(new \WP_Error('error', __('Something wrong happened.', 'wp-ultimo')));

		} // end if;

		if (is_wp_error($saved)) {

			wp_send_json_error($saved);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('updated', 1, $_SERVER['HTTP_REFERER']),
		));

	} // end handle_edit_line_item_modal;

	/**
	 * Renders the add/edit line items form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_edit_line_item_modal() {
		/*
		 * Get the payment line item.
		 */
		$line_item = wu_get_line_item(wu_request('line_item_id'), wu_request('id'));

		if (!$line_item) {
			/*
			 * If that doesn't work,
			 * we start a new line.
			 */
			$line_item = new \WP_Ultimo\Checkout\Line_Item(array());

		} // end if;

		$fields = array(
			'tab'                => array(
				'type'      => 'tab-select',
				'options'   => array(
					'type' => __('Type', 'wp-ultimo'),
					'info' => __('Additional Info', 'wp-ultimo'),
					'tax'  => __('Tax Info', 'wp-ultimo'),
				),
				'html_attr' => array(
					'v-model' => 'tab',
				),
			),
			'type'               => array(
				'type'              => 'select',
				'title'             => __('Line Item Type', 'wp-ultimo'),
				'desc'              => __('Select the line item type.', 'wp-ultimo'),
				'options'           => array(
					'product' => __('Product', 'wp-ultimo'),
					'refund'  => __('Refund', 'wp-ultimo'),
					'fee'     => __('Fee', 'wp-ultimo'),
					'credit'  => __('Credit', 'wp-ultimo'),
				),
				'wrapper_html_attr' => array(
					'v-show' => 'tab === "type"',
				),
				'html_attr'         => array(
					'v-model' => 'type',
				),
			),
			'product_id'         => array(
				'type'              => 'model',
				'title'             => __('Product', 'wp-ultimo'),
				'desc'              => __('Product associated with this line item.', 'wp-ultimo'),
				'placeholder'       => __('Search Products', 'wp-ultimo'),
				'value'             => $line_item->get_product_id(),
				'tooltip'           => '',
				'wrapper_html_attr' => array(
					'v-show' => 'type === "product" && tab === "type"',
				),
				'html_attr'         => array(
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 1,
					'data-selected'     => $line_item->get_product() ? json_encode($line_item->get_product()->to_search_results()) : '',
				),
			),
			'title'              => array(
				'type'              => 'text',
				'title'             => __('Line Item Title', 'wp-ultimo'),
				'placeholder'       => __('E.g. Extra Charge', 'wp-ultimo'),
				'desc'              => __('This is used when generating invoices.', 'wp-ultimo'),
				'value'             => $line_item->get_title(),
				'wrapper_html_attr' => array(
					'v-show' => 'tab === "info"',
				),
			),
			'description'        => array(
				'type'              => 'textarea',
				'title'             => __('Line Item Description', 'wp-ultimo'),
				'placeholder'       => __('E.g. This service was done to improve performance.', 'wp-ultimo'),
				'desc'              => __('This is used when generating invoices.', 'wp-ultimo'),
				'value'             => $line_item->get_description(),
				'html_attr'         => array(
					'rows' => 4,
				),
				'wrapper_html_attr' => array(
					'v-show' => 'tab === "info"',
				),
			),
			'quantity'           => array(
				'type'              => 'number',
				'title'             => __('Quantity', 'wp-ultimo'),
				'desc'              => __('Item quantity.', 'wp-ultimo'),
				'value'             => $line_item->get_quantity(),
				'placeholder'       => __('E.g. 1', 'wp-ultimo'),
				'wrapper_classes'   => 'wu-w-1/2',
				'wrapper_html_attr' => array(
					'v-show' => 'type === "product" && tab === "type"',
				),
				'html_attr'         => array(
					'min'      => 1,
					'required' => 'required',
				),
			),
			'unit_price'         => array(
				'type'      => 'hidden',
				'html_attr' => array(
					'v-model' => 'unit_price',
				),
			),
			'_unit_price'        => array(
				'type'              => 'text',
				'title'             => __('Unit Price', 'wp-ultimo'),
				'desc'              => __('Item unit price. This is multiplied by the quantity to calculate the sub-total.', 'wp-ultimo'),
				'placeholder'       => sprintf(__('E.g. %s', 'wp-ultimo'), wu_format_currency(99)),
				'value'             => $line_item->get_unit_price(),
				'money'             => true,
				'wrapper_classes'   => 'wu-w-1/2',
				'wrapper_html_attr' => array(
					'v-if' => 'type === "product" && tab === "type"',
				),
				'html_attr'         => array(
					'required' => 'required',
					'step'     => '0.01',
					'v-model'  => 'unit_price',
				),
			),
			'_unit_price_amount' => array(
				'type'              => 'text',
				'title'             => __('Amount', 'wp-ultimo'),
				'desc'              => __('Refund, credit or fee amount.', 'wp-ultimo'),
				'placeholder'       => sprintf(__('E.g. %s', 'wp-ultimo'), wu_format_currency(99)),
				'value'             => $line_item->get_unit_price(),
				'money'             => true,
				'wrapper_classes'   => 'wu-w-1/2',
				'wrapper_html_attr' => array(
					'v-if' => 'type !== "product" && tab === "type"',
				),
				'html_attr'         => array(
					'required' => 'required',
					'step'     => '0.01',
					'v-model'  => 'unit_price',
				),
			),
			'taxable'            => array(
				'type'              => 'toggle',
				'title'             => __('Is Taxable?', 'wp-ultimo'),
				'desc'              => __('Checking this box will toggle the tax controls.', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-bind:class' => 'type !== "product" ? "wu-opacity-50" : ""',
					'v-show'       => 'tab === "tax"',
				),
				'html_attr'         => array(
					'v-model'         => 'taxable',
					'v-bind:disabled' => 'type !== "product"',
				),
			),
			'tax_label'          => array(
				'type'              => 'text',
				'title'             => __('Tax Label', 'wp-ultimo'),
				'placeholder'       => __('E.g. ES VAT', 'wp-ultimo'),
				'desc'              => __('Tax description. This is shown on invoices to end customers.', 'wp-ultimo'),
				'value'             => $line_item->get_tax_label(),
				'wrapper_html_attr' => array(
					'v-show' => 'taxable &&  tab === "tax"',
				),
			),
			'tax_rate_group'     => array(
				'type'              => 'group',
				'title'             => __('Tax Rate', 'wp-ultimo'),
				'desc'              => __('Tax rate and type to apply to this item.', 'wp-ultimo'),
				'wrapper_html_attr' => array(
					'v-show' => 'taxable && tab === "tax"',
				),
				'fields'            => array(
					'tax_rate' => array(
						'type'            => 'number',
						'value'           => $line_item->get_tax_rate(),
						'placeholder'     => '',
						'wrapper_classes' => 'wu-mr-2 wu-w-1/3',
						'html_attr'       => array(
							'required' => 'required',
							'step'     => '0.01',
						),
					),
					'tax_type' => array(
						'type'            => 'select',
						'value'           => $line_item->get_tax_type(),
						'placeholder'     => '',
						'wrapper_classes' => 'wu-w-2/3',
						'options'         => array(
							'percentage' => __('Percentage (%)', 'wp-ultimo'),
							'absolute'   => __('Flat Rate ($)', 'wp-ultimo'),
						),
					),
				),
			),
			'submit_button'      => array(
				'type'            => 'submit',
				'title'           => __('Save', 'wp-ultimo'),
				'placeholder'     => __('Save', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
			),
			'line_item_id'       => array(
				'type'  => 'hidden',
				'value' => $line_item->get_id(),
			),
			'payment_id'         => array(
				'type'  => 'hidden',
				'value' => wu_request('id'),
			),
		);

		$form = new \WP_Ultimo\UI\Form('edit_line_item', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'edit_line_item',
				'data-state'  => wu_convert_to_state(array(
					'tab'        => 'type',
					'type'       => $line_item->get_type(),
					'taxable'    => $line_item->get_tax_rate() > 0,
					'unit_price' => $line_item->get_unit_price(),
				)),
			),
		));

		$form->render();

	} // end render_edit_line_item_modal;

	/**
	 * Display the payment actions.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function display_payment_actions() {

		$actions = array();

		$is_refundable = in_array($this->get_object()->get_status(), wu_get_refundable_payment_types(), true);

		if ($is_refundable) {

			$actions['refund_payment'] = array(
				'label'        => __('Refund Payment', 'wp-ultimo'),
				'icon_classes' => 'dashicons-wu-ccw wu-align-text-bottom',
				'classes'      => 'button wubox',
				'href'         => wu_get_form_url('refund_payment', array(
					'id' => $this->get_object()->get_id(),
				)),
			);

		} // end if;

		$actions['add_line_item'] = array(
			'label'        => __('Add Line Item', 'wp-ultimo'),
			'icon_classes' => 'dashicons-wu-circle-with-plus wu-align-text-bottom',
			'classes'      => 'button wubox',
			'href'         => wu_get_form_url('edit_line_item', array(
				'id' => $this->get_object()->get_id(),
			)),
		);

		return wu_get_template_contents('payments/line-item-actions', array(
			'payment' => $this->get_object(),
			'actions' => $actions,
		));

	} // end display_payment_actions;

	/**
	 * Displays the tax tax breakthrough.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_tax_breakthrough() {

		$tax_breakthrough = $this->get_object()->get_tax_breakthrough();

		wu_get_template('payments/tax-details', array(
			'tax_breakthrough' => $tax_breakthrough,
			'payment'          => $this->get_object(),
		));

	} // end display_tax_breakthrough;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		$label = $this->get_object()->get_status_label();

		$class = $this->get_object()->get_status_class();

		$tag = "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";

		$this->add_fields_widget('at_a_glance', array(
			'title'                 => __('At a Glance', 'wp-ultimo'),
			'position'              => 'normal',
			'classes'               => 'wu-overflow-hidden wu-widget-inset',
			'field_wrapper_classes' => 'wu-w-1/3 wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t-0 wu-border-l-0 wu-border-r wu-border-b-0 wu-border-gray-300 wu-border-solid wu-float-left wu-relative',
			'fields'                => array(
				'status' => array(
					'type'          => 'text-display',
					'title'         => __('Payment Status', 'wp-ultimo'),
					'display_value' => $tag,
					'tooltip'       => '',
				),
				'hash'   => array(
					'copy'          => true,
					'type'          => 'text-display',
					'title'         => __('Reference ID', 'wp-ultimo'),
					'display_value' => $this->get_object()->get_hash(),
				),
				'total'  => array(
					'type'            => 'text-display',
					'title'           => __('Total', 'wp-ultimo'),
					'display_value'   => wu_format_currency($this->get_object()->get_total(), $this->get_object()->get_currency()),
					'wrapper_classes' => 'sm:wu-border-r-0',
				),
			),
		));

		$this->add_list_table_widget('line-items', array(
			'title'        => __('Line Items', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Payment_Line_Item_List_Table(),
			'position'     => 'normal',
			'query_filter' => array($this, 'payments_query_filter'),
			'after'        => $this->display_payment_actions(),
		));

		$this->add_widget('tax-rates', array(
			'title'    => __('Tax Rate Breakthrough', 'wp-ultimo'),
			'position' => 'normal',
			'display'  => array($this, 'display_tax_breakthrough'),
		));

		$this->add_tabs_widget('options', array(
			'title'    => __('Payment Options', 'wp-ultimo'),
			'position' => 'normal',
			'sections' => apply_filters('wu_payments_options_sections', array(), $this->get_object()),
		));

		$this->add_list_table_widget('events', array(
			'title'        => __('Events', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
			'query_filter' => array($this, 'events_query_filter'),
		));

		$membership = $this->get_object()->get_membership();

		$this->add_save_widget('save', array(
			'html_attr' => array(
				'data-wu-app' => 'payment_save',
				'data-state'  => wu_convert_to_state(array(
					'status'            => $this->get_object()->get_status(),
					'original_status'   => $this->get_object()->get_status(),
					'membership_id'     => $membership ? $this->get_object()->get_membership_id() : '',
					'membership_status' => $membership ? $membership->get_status() : 'active',
					'gateway'           => $this->get_object()->get_gateway(),
				)),
			),
			'fields'    => array(
				'status'                   => array(
					'type'              => 'select',
					'title'             => __('Status', 'wp-ultimo'),
					'placeholder'       => __('Status', 'wp-ultimo'),
					'desc'              => __('The payment current status.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_status(),
					'options'           => Payment_Status::to_array(),
					'tooltip'           => '',
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
					'html_attr'         => array(
						'v-model' => 'status',
					),
				),
				'confirm_membership'       => array(
					'type'              => 'toggle',
					'title'             => __('Activate Membership?', 'wp-ultimo'),
					'desc'              => __('This payment belongs to a pending membership. If you toggle this option, this change in status will also apply to the membership. If any sites are pending, they are also going to be published automatically.', 'wp-ultimo'),
					'value'             => 0,
					'wrapper_html_attr' => array(
						'v-if'    => 'status !== original_status && status === "completed" && membership_status === "pending"',
						'v-cloak' => '1',
					),
				),
				'membership_id'            => array(
					'type'              => 'model',
					'title'             => __('Membership', 'wp-ultimo'),
					'desc'              => __('The membership associated with this payment.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_membership_id(),
					'tooltip'           => '',
					'html_attr'         => array(
						'v-model'          => 'membership_id',
						'data-base-link'   => wu_network_admin_url('wp-ultimo-edit-membership', array('id' => '')),
						'data-model'       => 'membership',
						'data-value-field' => 'id',
						'data-label-field' => 'reference_code',
						'data-max-items'   => 1,
						'data-selected'    => $this->get_object()->get_membership() ? json_encode($this->get_object()->get_membership()->to_search_results()) : '',
					),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
				),
				'gateway'                  => array(
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
				'gateway_payment_id_group' => array(
					'type'              => 'group',
					'desc'              => function() {

						$gateway_id = $this->get_object()->get_gateway();

						if (empty($this->get_object()->get_gateway_payment_id())) {

							return '';

						} // end if;

						$url = apply_filters("wu_{$gateway_id}_remote_payment_url", $this->get_object()->get_gateway_payment_id());

						if ($url) {

							return sprintf('<a class="wu-text-gray-800 wu-text-center wu-w-full wu-no-underline" href="%s" target="_blank">%s</a>', esc_attr($url), __('View on Gateway &rarr;', 'wp-ultimo'));

						} // end if;

						return '';

					},
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
						'v-show'  => 'gateway',
					),
					'fields'            => array(
						'gateway_payment_id' => array(
							'type'              => 'text',
							'title'             => __('Gateway Payment ID', 'wp-ultimo'),
							'placeholder'       => __('e.g. EX897540987913', 'wp-ultimo'),
							'description'       => __('e.g. EX897540987913', 'wp-ultimo'),
							'tooltip'           => __('This will usually be set automatically by the payment gateway.', 'wp-ultimo'),
							'value'             => $this->get_object()->get_gateway_payment_id(),
							'wrapper_classes'   => 'wu-w-full',
							'html_attr'         => array(),
							'wrapper_html_attr' => array(),
						),
					),
				),
				'invoice_number'           => array(
					'type'              => 'number',
					'min'               => 0,
					'title'             => __('Invoice Number', 'wp-ultimo'),
					'placeholder'       => __('e.g. 20', 'wp-ultimo'),
					'tooltip'           => __('This number gets saved automatically when a payment transitions to a complete state. You can change it to generate invoices with a particular number. The number chosen here has no effect on other invoices in the platform.', 'wp-ultimo'),
					'desc'              => __('The invoice number for this particular payment.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_saved_invoice_number(),
					'wrapper_classes'   => 'wu-w-full',
					'wrapper_html_attr' => array(
						'v-show'  => json_encode(wu_get_setting('invoice_numbering_scheme', 'reference_code') === 'sequential_number'),
						'v-cloak' => '1',
					),
				),
			),
		));

	} // end register_widgets;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Payment', 'wp-ultimo') : __('Add new Payment', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Payment', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		$actions = array();

		$payment = $this->get_object();

		if ($payment) {

			$actions[] = array(
				'url'   => $payment->get_invoice_url(),
				'label' => __('Generate Invoice', 'wp-ultimo'),
				'icon'  => 'wu-attachment',
			);

			if ($payment->is_payable()) {

				$actions[] = array(
					'url'   => $payment->get_payment_url(),
					'label' => __('Payment URL', 'wp-ultimo'),
					'icon'  => 'wu-credit-card',
				);

			} // end if;

		} // end if;

		return $actions;

	} // end action_links;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return array(
			'edit_label'          => __('Edit Payment', 'wp-ultimo'),
			'add_new_label'       => __('Add new Payment', 'wp-ultimo'),
			'updated_message'     => __('Payment updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Payment Name', 'wp-ultimo'),
			'title_description'   => __('This name will be used on pricing tables, invoices, and more.', 'wp-ultimo'),
			'save_button_label'   => __('Save Payment', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Payment', 'wp-ultimo'),
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
	public function events_query_filter($args) {

		$extra_args = array(
			'object_type' => 'payment',
			'object_id'   => absint($this->get_object()->get_id()),
		);

		return array_merge($args, $extra_args);

	} // end events_query_filter;

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function payments_query_filter($args) {

		$extra_args = array(
			'parent'     => absint($this->get_object()->get_id()),
			'parent__in' => false,
		);

		return array_merge($args, $extra_args);

	} // end payments_query_filter;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Payment
	 */
	public function get_object() {

		static $payment;

		if ($payment !== null) {

			return $payment;

		} // end if;

		if (isset($_GET['id'])) {

			$query = new \WP_Ultimo\Database\Payments\Payment_Query;

			$item = $query->get_item_by('id', $_GET['id']);

			if (!$item || $item->get_parent_id()) {

				wp_redirect(wu_network_admin_url('wp-ultimo-payments'));

				exit;

			} // end if;

			$payment = $item;

			return $item;

		} // end if;

		return new Payment;

	} // end get_object;

	/**
	 * Payments have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return false;

	} // end has_title;

	/**
	 * WIP: Handles saving by recalculating totals for a payment.
	 *
	 * @todo: This can not be handled here.
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save() {

		$this->get_object()->recalculate_totals()->save();

		$should_confirm_membership = wu_request('confirm_membership');

		if ($should_confirm_membership) {

			$membership = $this->get_object()->get_membership();

			if ($membership) {

				$membership->add_to_times_billed(1);

				$membership->renew(false, 'active');

			} // end if;

		} // end if;

		parent::handle_save();

	} // end handle_save;

} // end class Payment_Edit_Admin_Page;
