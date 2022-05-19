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

		$first_row = array(
			'id'   => array(
				'icon'  => 'dashicons-wu-hash wu-align-middle wu-mr-1',
				'label' => __('Event ID', 'wp-ultimo'),
				'value' => $item->get_id(),
			),
			'slug' => array(
				'icon'  => 'dashicons-wu-bookmark1 wu-align-middle wu-mr-1',
				'label' => __('Event Type', 'wp-ultimo'),
				'value' => wu_slug_to_name($item->get_slug()),
			),
		);

		$object_initiator = $item->get_initiator();

		if ($object_initiator === 'system') {

			$value = sprintf('<span class="dashicons-wu-wp-ultimo wu-align-middle wu-mr-1 wu-text-lg"></span><span class="wu-text-gray-600">%s</span>', __('Automatically processed by WP Ultimo', 'wp-ultimo'));

		} elseif ($object_initiator === 'manual') {

			$avatar = get_avatar($item->get_author_id(), 16, 'identicon', '', array(
				'force_display' => true,
				'class'         => 'wu-rounded-full wu-mr-1 wu-align-text-bottom',
			));

			$display_name = $item->get_author_display_name();

			$value = sprintf('<span class="wu-text-gray-600">%s%s</span>', $avatar, $display_name);

		} // end if;

		echo wu_responsive_table_row(array(
			'id'     => '',
			'title'  => sprintf('<span class="wu-font-normal">%s</span>', wp_trim_words($item->get_message(), 15)),
			'url'    => wu_network_admin_url('wp-ultimo-view-event', array(
				'id' => $item->get_id(),
			)),
			'status' => $value,
		),
		$first_row,
		array(
			'date_created' => array(
				'icon'  => 'dashicons-wu-calendar1 wu-align-middle wu-mr-1',
				'label' => '',
				'value' => sprintf(__('Processed %s', 'wp-ultimo'), wu_human_time_diff($item->get_date_created(), '-1 day')),
			),
		));

	} // end column_responsive;

} // end class Inside_Events_List_Table;
