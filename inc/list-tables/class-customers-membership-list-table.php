<?php
/**
 * Customers' Membership List Table class.
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
class Customers_Membership_List_Table extends Membership_List_Table {

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'hash'            => __('Code', 'wp-ultimo'),
			'status'          => __('Status', 'wp-ultimo'),
			'product'         => __('Product', 'wp-ultimo'),
			'amount'          => __('Price', 'wp-ultimo'),
			'date_created'    => __('Created at', 'wp-ultimo'),
			'date_expiration' => __('Expiration', 'wp-ultimo'),
			'id'              => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

} // end class Customers_Membership_List_Table;
