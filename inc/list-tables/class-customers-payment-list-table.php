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
			'responsive' => '',
		);

		return $columns;

	} // end get_columns;

	/**
	 * Renders the inside column responsive.
	 *
	 * @since 2.0.0
	 *
	 * @param object $item The item being rendered.
	 * @return void
	 */
	public function column_responsive($item) {

		echo wu_responsive_table_row(array(
			'id'     => $item->get_id(),
			'title'  => $item->get_hash(),
			'url'    => wu_network_admin_url('wp-ultimo-edit-payment', array(
				'id' => $item->get_id(),
			)),
			'status' => $this->column_status($item),
		), array(
			'total'   => array(
				'icon'  => 'dashicons-wu-shopping-bag1 wu-align-middle wu-mr-1',
				'label' => __('Payment Total', 'wp-ultimo'),
				'value' => wu_format_currency($item->get_total()),
			),
			'gateway' => array(
				'icon'  => 'dashicons-wu-credit-card2 wu-align-middle wu-mr-1',
				'label' => __('Gateway', 'wp-ultimo'),
				'value' => wu_slug_to_name($item->get_gateway()),
			),
		),
		array(
			'date_created' => array(
				'icon'  => 'dashicons-wu-calendar1 wu-align-middle wu-mr-1',
				'label' => '',
				'value' => sprintf(__('Created %s', 'wp-ultimo'), wu_human_time_diff(strtotime($item->get_date_created()))),
			),
		));

	} // end column_responsive;

} // end class Customers_Payment_List_Table;
