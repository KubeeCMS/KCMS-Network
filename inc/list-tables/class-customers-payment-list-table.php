<?php
/**
 * Customers Payment List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Payment List Table class.
 *
 * @since 2.0.0
 */
class Customers_Payment_List_Table extends Payment_List_Table {

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'hash'         => __('Code', 'wp-ultimo'),
			'status'       => __('Status', 'wp-ultimo'),
			'total'        => __('Total', 'wp-ultimo'),
			'gateway'      => __('Gateway', 'wp-ultimo'),
			'date_created' => __('Created at', 'wp-ultimo'),
			'id'           => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

} // end class Customers_Payment_List_Table;
