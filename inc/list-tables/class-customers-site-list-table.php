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
			'featured_image_id' => '<span class="dashicons-wu-image"></span>',
			'path'              => __('URL', 'wp-ultimo'),
			'type'              => __('Type', 'wp-ultimo'),
			'membership'        => __('Membership', 'wp-ultimo'),
			'blog_id'           => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

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
