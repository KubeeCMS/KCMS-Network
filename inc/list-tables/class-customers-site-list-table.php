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
class Customers_Site_List_Table extends Site_List_Table {

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

		$m = $item->get_membership();

		echo wu_responsive_table_row(array(
			'id'     => $item->get_id(),
			'title'  => $item->get_title(),
			'url'    => wu_network_admin_url('wp-ultimo-edit-site', array(
				'id' => $item->get_id(),
			)),
			'image'  => $this->column_featured_image_id($item),
			'status' => $this->column_type($item),
		), array(
			'link'       => array(
				'icon'  => 'dashicons-wu-link1 wu-align-middle wu-mr-1',
				'label' => __('Visit Site', 'wp-ultimo'),
				'url'   => $item->get_active_site_url(),
				'value' => $item->get_active_site_url(),
			),
			'dashboard'  => array(
				'icon'  => 'dashicons-wu-browser wu-align-middle wu-mr-1',
				'label' => __('Go to the Dashboard', 'wp-ultimo'),
				'value' => __('Dashboard', 'wp-ultimo'),
				'url'   => get_admin_url($item->get_id()),
			),
			'membership' => array(
				'icon'  => 'dashicons-wu-rotate-ccw wu-align-middle wu-mr-1',
				'label' => __('Go to the Membership', 'wp-ultimo'),
				'value' => $m ? $m->get_hash() : '',
				'url'   => $m ? wu_network_admin_url('wp-ultimo-edit-membership', array(
					'id' => $m->get_id(),
				)) : '',
			),
		),
		array(
			'date_created' => array(
				'icon'  => 'dashicons-wu-calendar1 wu-align-middle wu-mr-1',
				'label' => '',
				/* translators: the placeholder is a date */
				'value' => $item->get_type() === 'pending' ? __('Not Available', 'wp-ultimo') : sprintf(__('Created %s', 'wp-ultimo'), wu_human_time_diff(strtotime($item->get_date_registered()))),
			),
		));

	} // end column_responsive;

	/**
	 * Overrides the parent method to add pending sites.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $per_page Number of items to display per page.
	 * @param integer $page_number Current page.
	 * @param boolean $count If we should count records or return the actual records.
	 * @return array
	 */
	public function get_items($per_page = 5, $page_number = 1, $count = false) {

		$sites = parent::get_items($per_page, $page_number, $count);

		if ($count) {

			return $sites;

		} // end if;

		$pending_sites = array();

		$page = wu_request('page');

		$id = wu_request('id');

		if (!$id) {

			return $sites;

		} // end if;

		switch ($page) {

			case 'wp-ultimo-edit-membership':
				$membership    = wu_get_membership($id);
				$pending_sites = $membership && $membership->get_pending_site() ? array($membership->get_pending_site()) : array();
				break;
			case 'wp-ultimo-edit-customer':
				$customer      = wu_get_customer($id);
				$pending_sites = $customer ? $customer->get_pending_sites() : array();
				break;

		} // end switch;

		foreach ($pending_sites as &$site) {

			$site->set_type('pending');
			$site->set_blog_id('--');

		} // end foreach;

		return array_merge($pending_sites, $sites);

	} // end get_items;

} // end class Customers_Site_List_Table;
