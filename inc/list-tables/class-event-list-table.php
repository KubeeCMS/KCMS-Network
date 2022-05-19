<?php
/**
 * Event List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

use \WP_Ultimo\Models\Event;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Event List Table class.
 *
 * @since 2.0.0
 */
class Event_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Events\\Event_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Event', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Events', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true                       // does this table support ajax?
		));

	} // end __construct;

	/**
	 * Returns the markup for the object_type column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Event $item The event being displayed.
	 * @return string
	 */
	public function column_object_type($item) {

		$object_type = $item->get_object_type();

		return "<span class='wu-py-1 wu-px-2 wu-bg-gray-200 wu-rounded-sm wu-leading-none wu-text-gray-700 wu-text-xs wu-font-mono'>{$object_type}</span>";

	} // end column_object_type;

	/**
	 * Returns the markup for the initiator column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Event $item The event being displayed.
	 * @return string
	 */
	public function column_initiator($item) {

		$object_initiator = $item->get_initiator();

		$object_severity_label = $item->get_severity_label();

		$object_severity_class = $item->get_severity_class();

		$object_label_tooltip = substr($object_severity_label, 0, 1);

		if ($object_initiator === 'system') {

			$avatar = '<span class="dashicons-wu-tools wu-text-gray-700 wu-text-xl"></span>';

			$system_text = ucfirst($object_initiator);

			// phpcs:disable
			$html = "<div class='wu-table-card wu-text-gray-700 wu-p-2 wu-flex wu-flex-grow wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300'>
				<div class='wu-flex wu-relative wu-h-7 wu-w-7 wu-rounded-full wu-ring-2 wu-ring-white wu-bg-gray-300 wu-items-center wu-justify-center wu-mr-3'>
					{$avatar}

					<span role='tooltip' aria-label='{$object_initiator} - {$object_severity_label}' class='wu-absolute wu-rounded-full wu--mb-2 wu-flex wu-items-center wu-justify-center wu-font-mono wu-bottom-0 wu-right-0 wu-font-bold wu-h-4 wu-w-4 wu-uppercase wu-text-2xs wu-border-solid wu-border-2 wu-border-white {$object_severity_class}'>{$object_label_tooltip}</span>

				</div>
				<div class=''>
					<strong class='wu-block'>{$system_text}</strong>
					<small>" . __('Automatically started', 'wp-ultimo') . "</small>
				</div>
			</div>";
			// phpcs:enable

		} elseif ($object_initiator === 'manual') {

			$avatar = get_avatar($item->get_author_id(), 32, 'identicon', '', array(
				'force_display' => true,
				'class'         => 'wu-rounded-full',
			));

			$display_name = $item->get_author_display_name();

			$id = $item->get_author_id();

			$url_atts = array(
				'id' => $item->get_author_id(),
			);

			$initiator_link = wu_network_admin_url('wp-ultimo-edit-customer', $url_atts);

			$email = $item->get_author_email_address();

			$html = "<a href='{$initiator_link}' class='wu-table-card wu-text-gray-700 wu-flex wu-p-2 wu-flex-grow wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300'>

				<div class='wu-flex wu-relative wu-rounded-full wu-ring-2 wu-ring-white wu-items-center wu-justify-center'>

					{$avatar}

					<span role='tooltip' aria-label='{$object_initiator} - {$object_severity_label}' class='wu-absolute wu-rounded-full wu--mb-2 wu-flex wu-items-center wu-justify-center wu-font-mono wu-bottom-0 wu-right-0 wu-font-bold wu-h-4 wu-w-4 wu-uppercase wu-text-2xs wu-border-solid wu-border-2 wu-border-white {$object_severity_class}'>{$object_label_tooltip}</span>

				</div>

				<div class='wu-pl-2'>
					<strong class='wu-block'> {$display_name} <small class='wu-font-normal'>(#{$id})</small></strong>
					<small>{$email}</small>
				</div>
			</a>";

		} else {

			$not_found = __('No initiator found', 'wp-ultimo');

			$html = "<div class='wu-table-card  wu-text-gray-700 wu-py-1 wu-px-2 wu-flex wu-flex-grow wu-rounded wu-items-center wu-border wu-border-solid wu-border-gray-300 wu-relative wu-overflow-hidden'>
				<span class='dashicons dashicons-wu-block wu-text-gray-600 wu-px-1 wu-pr-3'>&nbsp;</span>
				<div class='wu-pl-2'>
					<span class='wu-block wu-py-3 wu-text-gray-600 wu-text-2xs wu-font-bold wu-uppercase'>{$not_found}</span>
				</div>
			</div>";

		} // end if;

		return $html;

	} // end column_initiator;

	/**
	 * Returns the markup for the initiator column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Event $item The event being displayed.
	 * @return string
	 */
	public function column_slug($item) {

		$object_slug = $item->get_slug();

		return "<span class='wu-py-1 wu-px-2 wu-bg-gray-200 wu-rounded-sm wu-text-gray-700 wu-text-xs wu-font-mono'>{$object_slug}</span>";

	} // end column_slug;

	/**
	 * Returns the markup for the message column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Event $item The event being displayed.
	 * @return string
	 */
	public function column_message($item) {

		$message = wp_trim_words($item->get_message(), 13);

		$url_atts = array(
			'id'    => $item->get_id(),
			'model' => 'event'
		);

		$actions = array(
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-view-event', $url_atts), __('View', 'wp-ultimo')),
			'delete' => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Delete', 'wp-ultimo'),
				wu_get_form_url(
					'delete_modal',
					$url_atts
				),
				__('Delete', 'wp-ultimo')
			),
		);

		return $message . $this->row_actions($actions);

	} // end column_message;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'initiator'    => __('Initiator', 'wp-ultimo'),
			'message'      => __('Event Message', 'wp-ultimo'),
			'slug'         => __('SLug', 'wp-ultimo'),
			'object_type'  => __('Type', 'wp-ultimo'),
			'date_created' => __('Created at', 'wp-ultimo'),
			'id'           => __('ID', 'wp-ultimo'),
		);

		return apply_filters('wu_events_list_table_get_columns', $columns, $this);

	} // end get_columns;

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 * @return boolean|array
	 */
	public function get_filters() {

		return array(
			'filters'      => array(
				'severity' => array(
					'label'   => __('Severity', 'wp-ultimo'),
					'options' => array(
						Event::SEVERITY_SUCCESS => __('Success', 'wp-ultimo'),
						Event::SEVERITY_NEUTRAL => __('Neutral', 'wp-ultimo'),
						Event::SEVERITY_INFO    => __('Info', 'wp-ultimo'),
						Event::SEVERITY_WARNING => __('Warning', 'wp-ultimo'),
						Event::SEVERITY_FATAL   => __('Fatal', 'wp-ultimo'),
					),
				),
			),
			'date_filters' => array(
				'date_created' => array(
					'label'   => __('Created At', 'wp-ultimo'),
					'options' => $this->get_default_date_filter_options(),
				),
			),
		);

	} // end get_filters;

} // end class Event_List_Table;
