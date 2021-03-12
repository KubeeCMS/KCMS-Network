<?php
/**
 * Customers Site List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Site List Table class.
 *
 * @since 2.0.0
 */
class Inside_Events_List_Table extends Event_List_Table {

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = parent::get_columns();

		unset($columns['cb']);
		unset($columns['object_type']);
		unset($columns['slug']);

		return $columns;

	} // end get_columns;

} // end class Inside_Events_List_Table;
