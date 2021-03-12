<?php
/**
 * Domain List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Domain List Table class.
 *
 * @since 2.0.0
 */
class Sites_Domain_List_Table extends Domain_List_Table {

	/**
	 * Context widget.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $context = 'widget';

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'domain'         => __('Domain', 'wp-ultimo'),
			'active'         => __('Is Active?', 'wp-ultimo'),
			'primary_domain' => __('Is Primary?', 'wp-ultimo'),
			'secure'         => __('HTTPS', 'wp-ultimo'),
			'stage'          => __('Stage', 'wp-ultimo'),
			'id'             => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

} // end class Sites_Domain_List_Table;
