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
			'title'  => $item->get_title(),
			'url'    => wu_network_admin_url('wp-ultimo-edit-site', array(
				'id' => $item->get_id(),
			)),
			'image'  => $this->column_featured_image_id($item),
			'status' => $this->column_type($item),
		), array(
			'link'      => array(
				'icon'  => 'dashicons-wu-link1 wu-align-middle wu-mr-1',
				'label' => __('Visit Site', 'wp-ultimo'),
				'value' => __('Homepage', 'wp-ultimo'),
				'url'   => $item->get_active_site_url(),
			),
			'dashboard' => array(
				'icon'  => 'dashicons-wu-browser wu-align-middle wu-mr-1',
				'label' => __('Go to the Dashboard', 'wp-ultimo'),
				'value' => __('Dashboard', 'wp-ultimo'),
				'url'   => get_admin_url($item->get_id()),
			),
		),
		array(
			'date_created' => array(
				'icon'  => 'dashicons-wu-calendar1 wu-align-middle wu-mr-1',
				'label' => '',
				'value' => $item->get_type() === 'pending' ? __('Not Available', 'wp-ultimo') : sprintf(__('Created %s', 'wp-ultimo'), wu_human_time_diff(strtotime($item->get_date_registered()))),
			),
		));

	} // end column_responsive;

} // end class Memberships_Site_List_Table;
