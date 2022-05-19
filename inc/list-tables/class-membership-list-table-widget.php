<?php
/**
 * Membership List Table Widget class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Helpers\Hash;

/**
 * Membership List Table class.
 *
 * @since 2.0.0
 */
class Membership_List_Table_Widget extends Base_List_Table {

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
			'ajax'     => true                         // does this table support ajax?
		));

	} // end __construct;

	/**
	 * Uses the query class to return the items to be displayed.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $per_page Number of items to display per page.
	 * @param integer $page_number Current page.
	 * @param boolean $count If we should count records or return the actual records.
	 * @return array
	 */
	public function get_items($per_page = 5, $page_number = 1, $count = false) {

		$query_class = new $this->query_class();

		$query_args = array(
			'number'  => 5,
			'offset'  => 1,
			'orderby' => wu_request('orderby', 'date_created'),
			'order'   => wu_request('order', 'DESC'),
			'search'  => wu_request('s', false),
			'count'   => $count,
		);

		/**
		 * Accounts for hashes
		 */
		if (isset($query_args['search']) && strlen($query_args['search']) === Hash::LENGTH) {

			$item_id = Hash::decode($query_args['search']);

			if ($item_id) {

				unset($query_args['search']);

				$query_args['id'] = $item_id;

			} // end if;

		} // end if;

		$query_args = array_merge($query_args, $this->get_extra_query_fields());

		$query_args = apply_filters("wu_{$this->id}_get_items", $query_args, $this);

		$function_name = 'wu_get_' . $query_class->get_plural_name();

		if (function_exists($function_name)) {

			$query = $function_name($query_args);

		} else {

			$query = $query_class->query($query_args);

		} // end if;

		return $query;

	} // end get_items;

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
			'id' => $item->get_id(),
		);

		$code = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-membership', $url_atts), $item->get_hash());

		$actions = array(
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-membership', $url_atts), __('Edit', 'wp-ultimo')),
			'delete' => sprintf('<a href="%s">%s</a>', '', __('Delete', 'wp-ultimo')),
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
        _n('every %2$s', 'every %1$s %2$s', $duration, 'wp-ultimo'), // phpcs:ignore
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
	 * Displays the customer of the membership.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Membership $item Membership object.
	 * @return string
	 */
	public function column_customer($item) {

		$customer = $item->get_customer();

		if (!$customer) {

			$not_found = __('No customer found', 'wp-ultimo');

			return "<div class='wu-py-1 wu-px-2 wu-flex-grow wu-block wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-bg-gray-100 wu-relative wu-overflow-hidden'>
				<span class='dashicons dashicons-wu-block wu-text-gray-600 wu-px-1 wu-pr-3'>&nbsp;</span>
				<div class=''>
					<span class='wu-block wu-py-3 wu-text-gray-600 wu-text-2xs wu-font-bold wu-uppercase'>{$not_found}</span>
				</div>
			</div>";

		} // end if;

		$url_atts = array(
			'id' => $customer->get_id(),
		);

		$avatar = get_avatar($customer->get_user_id(), 32, 'identicon', '', array(
			'force_display' => true,
			'class'         => 'wu-rounded-full wu-mr-2',
		));

		$display_name = $customer->get_display_name();

		$id = $customer->get_id();

		$email = $customer->get_email_address();

		$customer_link = wu_network_admin_url('wp-ultimo-edit-customer', $url_atts);

		$html = "<a href='{$customer_link}' class='wu-p-1 wu-flex-grow wu-bg-gray-100 wu-block wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300'>
			<div class=''>
				<strong class='wu-block'>{$display_name} <small class='wu-font-normal'>(#{$id})</small></strong>

			</div>
		</a>";

		return $html;

	} // end column_customer;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'hash'     => __('Ref.', 'wp-ultimo'),
			'status'   => __('Status', 'wp-ultimo'),
			'customer' => __('Customer', 'wp-ultimo'),
			'amount'   => __('Price', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;
	/**
	 * Overrides the parent method to include the custom ajax functionality for WP Ultimo.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function _js_vars() {} // end _js_vars;

} // end class Membership_List_Table_Widget;
