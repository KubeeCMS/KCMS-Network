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
class Site_Customer_List_Table extends Customer_List_Table {

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct();

		$this->current_mode = 'list';

	} // end __construct;

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

		$last_login = sprintf(__('Last login %s', 'wp-ultimo'), wu_human_time_diff(strtotime($item->get_last_login())));

		if ($item->is_online()) {

			$last_login = '<span class="wu-inline-block wu-mr-1 wu-rounded-full wu-h-2 wu-w-2 wu-bg-green-500"></span>' . __('Online', 'wp-ultimo');

		} // end if;

		echo wu_responsive_table_row(array(
			'id'     => $item->get_id(),
			'title'  => $item->get_display_name(),
			'url'    => wu_network_admin_url('wp-ultimo-edit-customer', array(
				'id' => $item->get_id(),
			)),
			'image'  => get_avatar($item->get_user_id(), 36, 'identicon', '', array(
				'force_display' => true,
				'class'         => 'wu-rounded-full',
			)),
			'status' => $this->column_status($item),
		), array(
			'total'    => array(
				'icon'  => 'dashicons-wu-at-sign wu-align-middle wu-mr-1',
				'label' => __('Email Address', 'wp-ultimo'),
				'value' => $item->get_email_address(),
			),
		),
		array(
			'date_expiration' => array(
				'icon'  => $item->is_online() === false ? 'dashicons-wu-calendar1 wu-align-middle wu-mr-1' : '',
				'label' => __('Last Login', 'wp-ultimo'),
				'value' => $last_login,
			),
			'date_created'    => array(
				'icon'  => 'dashicons-wu-calendar1 wu-align-middle wu-mr-1',
				'label' => '',
				'value' => sprintf(__('Registered %s', 'wp-ultimo'), wu_human_time_diff(strtotime($item->get_date_registered()))),
			),
		));

	} // end column_responsive;

} // end class Site_Customer_List_Table;
