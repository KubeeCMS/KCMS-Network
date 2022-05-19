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
			'title'  => strtolower($item->get_domain()),
			'url'    => wu_network_admin_url('wp-ultimo-edit-domain', array(
				'id' => $item->get_id(),
			)),
			'status' => $this->column_stage($item),
		), array(
			'primary' => array(
				'icon'  => $item->is_primary_domain() ? 'dashicons-wu-filter_1 wu-align-text-bottom wu-mr-1' : 'dashicons-wu-plus-square wu-align-text-bottom wu-mr-1',
				'label' => '',
				'value' => $item->is_primary_domain() ? __('Primary', 'wp-ultimo') : __('Alias', 'wp-ultimo'),
			),
			'secure'  => array(
				'wrapper_classes' => $item->is_secure() ? 'wu-text-green-500' : '',
				'icon'            => $item->is_secure() ? 'dashicons-wu-lock1 wu-align-text-bottom wu-mr-1' : 'dashicons-wu-lock1 wu-align-text-bottom wu-mr-1',
				'label'           => '',
				'value'           => $item->is_secure() ? __('Secure (HTTPS)', 'wp-ultimo') : __('Not Secure (HTTP)', 'wp-ultimo'),
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

} // end class Sites_Domain_List_Table;
