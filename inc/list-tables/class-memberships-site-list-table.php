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
class Memberships_Site_List_Table extends Customers_Site_List_Table {

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'featured_image_id' => '<span class="dashicons-wu-image"></span>',
			'path'              => __('URL', 'wp-ultimo'),
			'type'              => __('Type', 'wp-ultimo'),
			'blog_id'           => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

} // end class Memberships_Site_List_Table;
