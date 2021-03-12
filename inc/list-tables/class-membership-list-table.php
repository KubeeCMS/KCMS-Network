<?php
/**
 * Membership List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Membership List Table class.
 *
 * @since 2.0.0
 */
class Membership_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Memberships\\Membership_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Membership', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Memberships', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                           // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_get_form_url('add_new_membership'),
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

		$_filter_fields['customer_id'] = wu_request('customer_id');

		return $_filter_fields;

	} // end get_extra_query_fields;

	/**
	 * Displays the membership reference code.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Membership $item Membership object.
	 * @return string
	 */
	public function column_hash($item) {

		$url_atts = array(
			'id'    => $item->get_id(),
			'model' => 'membership',
		);

		$code = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-membership', $url_atts), $item->get_hash());

		$actions = array(
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-membership', $url_atts), __('Edit', 'wp-ultimo')),
			'delete' => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Delete', 'wp-ultimo'),
				wu_get_form_url(
					'delete_modal',
					$url_atts
				),
				__('Delete', 'wp-ultimo')
			),
		);

		$html = "<span class='wu-font-mono'><strong>{$code}</strong></span>";

		return $html . $this->row_actions($actions);

	} // end column_hash;

	/**
	 * Displays the status of the membership.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Membership $item Membership object.
	 * @return string
	 */
	public function column_status($item) {

		$label = $item->get_status_label();

		$class = $item->get_status_class();

		return "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";

	} // end column_status;

	/**
	 * Displays the price of the membership.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Membership $item Membership object.
	 * @return string
	 */
	public function column_amount($item) {

		if (empty($item->get_amount())) {

			return __('Free', 'wp-ultimo');

		} // end if;

		$amount = wu_format_currency($item->get_amount(), $item->get_currency());

		if ($item->is_recurring()) {

			$duration = $item->get_duration();

			$message = sprintf(
				// translators: %1$s is the formatted price, %2$s the duration, and %3$s the duration unit (day, week, month, etc)
				_n('every %2$s', 'every %1$s %2$ss', $duration, 'wp-ultimo'), // phpcs:ignore
				$duration,
				$item->get_duration_unit()
			);

			if (!$item->is_forever_recurring()) {

				$billing_cycles_message = sprintf(
					// translators: %s is the number of billing cycles.
					_n('for %s cycle', 'for %s cycles', $item->get_billing_cycles(), 'wp-ultimo'),
					$item->get_billing_cycles()
				);

				$message .= ' ' . $billing_cycles_message;

			} // end if;

		} else {

			$message = __('one time payment', 'wp-ultimo');

		} // end if;

		return sprintf('%s<br><small>%s</small>', $amount, $message);

	} // end column_amount;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'hash'            => wu_tooltip(__('Reference Code', 'wp-ultimo'), 'dashicons-wu-hash wu-text-xs'),
			'status'          => __('Status', 'wp-ultimo'),
			'customer'        => __('Customer', 'wp-ultimo'),
			'product'         => __('Product', 'wp-ultimo'),
			'amount'          => __('Price', 'wp-ultimo'),
			// 'sites'              => __('Sites', 'wp-ultimo'),
			'date_created'    => __('Created at', 'wp-ultimo'),
			'date_expiration' => __('Expiration', 'wp-ultimo'),
			'id'              => __('ID', 'wp-ultimo'),
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
				'status' => array(
					'label'   => __( 'Status', 'wp-ultimo' ),
					'options' => array(
						'active'   => __( 'Active', 'wp-ultimo' ),
						'canceled' => __( 'Canceled', 'wp-ultimo' ),
						'disabled' => __( 'Disabled', 'wp-ultimo' ),
						'pending'  => __( 'Pending', 'wp-ultimo' ),
						'other'    => __( 'Other', 'wp-ultimo' ),
					),
				),

			),
			'date_filters' => array(

				/**
				 * Created At
				 */
				'date_created'    => array(
					'label'   => __('Created At', 'wp-ultimo'),
					'options' => $this->get_default_date_filter_options(),
				),

				/**
				 * Expiration Date
				 */
				'date_expiration' => array(
					'label'   => __('Expiration Date', 'wp-ultimo'),
					'options' => $this->get_default_date_filter_options(),
				),

				/**
				 * Renewal Date
				 */
				'date_renewed'    => array(
					'label'   => __('Renewal Date', 'wp-ultimo'),
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
			'all'      => array(
				'field' => 'status',
				'url'   => add_query_arg('status', 'all'),
				'label' => __('All Memberships', 'wp-ultimo'),
				'count' => 0,
			),
			'active'   => array(
				'field' => 'status',
				'url'   => add_query_arg('status', 'active'),
				'label' => __('Active', 'wp-ultimo'),
				'count' => 0,
			),
			'pending'  => array(
				'field' => 'status',
				'url'   => add_query_arg('status', 'pending'),
				'label' => __('Pending', 'wp-ultimo'),
				'count' => 0,
			),
			'on-hold'  => array(
				'field' => 'status',
				'url'   => add_query_arg('status', 'on-hold'),
				'label' => __('On Hold', 'wp-ultimo'),
				'count' => 0,
			),
			'expired'  => array(
				'field' => 'status',
				'url'   => add_query_arg('status', 'expired'),
				'label' => __('Expired', 'wp-ultimo'),
				'count' => 0,
			),
			'canceled' => array(
				'field' => 'status',
				'url'   => add_query_arg('status', 'canceled'),
				'label' => __('Canceled', 'wp-ultimo'),
				'count' => 0,
			),
		);

	} // end get_views;

} // end class Membership_List_Table;
