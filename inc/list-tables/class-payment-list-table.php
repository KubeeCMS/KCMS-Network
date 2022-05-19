<?php
/**
 * Payment List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

use \WP_Ultimo\Database\Payments\Payment_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Payment List Table class.
 *
 * @since 2.0.0
 */
class Payment_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Payments\\Payment_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Payment', 'wp-ultimo'),
			'plural'   => __('Payments', 'wp-ultimo'),
			'ajax'     => true,
			'add_new'  => array(
				'url'     => wu_get_form_url('add_new_payment'),
				'classes' => 'wubox',
			),
		));

	} // end __construct;

	/**
	 * Adds the extra search field when the search element is present.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_query_fields() {

		$_filter_fields = parent::get_extra_query_fields();

		$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : false;

		$_filter_fields['membership_id'] = wu_request('membership_id', false);

		$_filter_fields['customer_id'] = wu_request('customer_id', false);

		$_filter_fields['parent_id__in'] = array('0', 0, '', null);

		return $_filter_fields;

	} // end get_extra_query_fields;

	/**
	 * Displays the payment reference code.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_hash($item) {

		$url_atts = array(
			'id' => $item->get_id(),
		);

		$code = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-payment', $url_atts), $item->get_hash());

		$actions = array(
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-payment', $url_atts), __('Edit', 'wp-ultimo')),
			'delete' => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Delete', 'wp-ultimo'),
				wu_get_form_url(
					'delete_modal',
					array(
						'model' => 'payment',
						'id'    => $item->get_id()
					)
				),
				__('Delete', 'wp-ultimo')
			),
		);

		$html = "<span class='wu-font-mono'><strong>{$code}</strong></span>";

		return $html . $this->row_actions($actions);

	} // end column_hash;

	/**
	 * Displays the membership photo and special status.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_status($item) {

		$label = $item->get_status_label();

		$class = $item->get_status_class();

		return "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-inline-block wu-leading-none wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";

	} // end column_status;

	/**
	 * Returns the number of subscriptions owned by this membership.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_product($item) {

		$product = $item->get_product();

		if (!$product) {

			return __('No product found', 'wp-ultimo');

		} // end if;

		$url_atts = array(
			'product_id' => $product->get_id(),
		);

		$actions = array(
			'view' => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-product', $url_atts), __('View', 'wp-ultimo')),
		);

		$html = $product->get_name();

		return $html . $this->row_actions($actions);

	} // end column_product;

	/**
	 * Displays the column for the total amount of the payment.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Payment $item Payment object.
	 * @return string
	 */
	public function column_total($item) {

		$gateway = wu_slug_to_name($item->get_gateway());

		return wu_format_currency($item->get_total()) . "<small class='wu-block'>{$gateway}</small>";

	} // end column_total;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'hash'         => wu_tooltip(__('Reference Code', 'wp-ultimo'), 'dashicons-wu-hash wu-text-xs'),
			'status'       => __('Status', 'wp-ultimo'),
			'customer'     => __('Customer', 'wp-ultimo'),
			'membership'   => __('Membership', 'wp-ultimo'),
			'total'        => __('Total', 'wp-ultimo'),
			'date_created' => __('Created at', 'wp-ultimo'),
			'id'           => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 * @return boolean|array
	 */
	public function get_filters() {

		return array(
			'filters'      => array(

				/**
				 * Status
				 */
				'status'  => array(
					'label'   => __( 'Status', 'wp-ultimo' ),
					'options' => array(
						'pending'   => __( 'Pending', 'wp-ultimo' ),
						'completed' => __( 'Completed', 'wp-ultimo' ),
						'refund'    => __( 'Refund', 'wp-ultimo' ),
						'partial'   => __( 'Partial', 'wp-ultimo' ),
						'failed'    => __( 'Failed', 'wp-ultimo' ),
					),
				),

				/**
				 * Gateway
				 */
				'gateway' => array(
					'label'   => __( 'Gateway', 'wp-ultimo' ),
					'options' => array(
						'free'   => __( 'Free', 'wp-ultimo' ),
						'manual' => __( 'Manual', 'wp-ultimo' ),
						'paypal' => __( 'Paypal', 'wp-ultimo' ),
						'stripe' => __( 'Stripe', 'wp-ultimo' ),
					),
				),
			),
			'date_filters' => array(

				/**
				 * Created At
				 */
				'date_created' => array(
					'label'   => __( 'Created At', 'wp-ultimo' ),
					'options' => $this->get_default_date_filter_options(),
				),
			),
		);

	} // end get_filters;

	/**
	 * Returns the pre-selected filters on the filter bar.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_views() {

		return array(
			'all'                            => array(
				'field' => 'status',
				'url'   => add_query_arg('status', 'all'),
				'label' => __('All Payments', 'wp-ultimo'),
				'count' => 0,
			),
			Payment_Status::COMPLETED()      => array(
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::COMPLETED()),
				'label' => __('Completed', 'wp-ultimo'),
				'count' => 0,
			),
			Payment_Status::PENDING()        => array(
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::PENDING()),
				'label' => __('Pending', 'wp-ultimo'),
				'count' => 0,
			),
			Payment_Status::PARTIAL_REFUND() => array(
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::PARTIAL_REFUND()),
				'label' => __('Partially Refunded', 'wp-ultimo'),
				'count' => 0,
			),
			Payment_Status::REFUND()         => array(
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::REFUND()),
				'label' => __('Refunded', 'wp-ultimo'),
				'count' => 0,
			),
			Payment_Status::FAILED()         => array(
				'field' => 'status',
				'url'   => add_query_arg('status', Payment_Status::FAILED()),
				'label' => __('Failed', 'wp-ultimo'),
				'count' => 0,
			),
		);

	} // end get_views;

} // end class Payment_List_Table;
