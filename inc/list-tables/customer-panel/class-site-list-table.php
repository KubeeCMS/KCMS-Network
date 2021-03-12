<?php
/**
 * Customers Site List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables\Customer_Panel;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\List_Tables\Site_List_Table as Parent_Site_List_Table;

/**
 * Site List Table class.
 *
 * @since 2.0.0
 */
class Site_List_Table extends Parent_Site_List_Table {

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct();

		$this->modes = array(
			'grid' => __('Grid View'),
		);

		$this->current_mode = 'grid';

	} // end __construct;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		return array();

	} // end get_columns;

	/**
	 * Clears filters.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_filters() {

		return array(
			'filters'      => array(),
			'date_filters' => array(),
		);

	} // end get_filters;

	/**
	 * Clears views.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_views() {

		return array(
			'all' => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'all'),
				'label' => __('Your Sites', 'wp-ultimo'),
				'count' => 0,
			),
		);

	} // end get_views;

	/**
	 * Get the extra fields based on the request.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_fields() {

		$customer = wu_get_current_customer();

		if (!$customer) {

			return array(
				'blog_id__in' => array('null_id'), // pass absurd value to make sure the query returns nothing.
			);

		} // end if;

		$fields = parent::get_extra_fields();

		$fields = array(
			'meta_query' => array(
				'customer_id' => array(
					'key'   => 'wu_customer_id',
					'value' => $customer->get_id(),
				),
			),
		);

		return $fields;

	} // end get_extra_fields;

} // end class Site_List_Table;
