<?php
/**
 * Email List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Email List Table class.
 *
 * @since 2.0.0
 */
class Email_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Emails\\Email_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Email', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Emails', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                         // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_network_admin_url('wp-ultimo-edit-email'),
				'classes' => '',
			),
		));

	} // end __construct;

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

		$query = array(
			'number' => $per_page,
			'offset' => ($page_number - 1) * $per_page,
			'count'  => $count,
		);

		$search = wu_request('s');

		if ($search) {

			$query['search'] = $search;

		} // end if;

		$target = wu_request('target');

		if ($target && $target !== 'all') {

			$query['meta_query'] = array(
				'type' => array(
					'key'   => 'wu_target',
					'value' => $target,
				),
			);

		} // end if;

		$query = apply_filters("wu_{$this->id}_get_items", $query, $this);

		return wu_get_emails($query);

	} // end get_items;

	/**
	 * Displays the title of the email.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Email $item The email object.
	 * @return string
	 */
	public function column_title($item) {

		$url_atts = array(
			'id'    => $item->get_id(),
			'model' => 'email',
		);

		$title = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-email', $url_atts), $item->get_title());

		$title = '<strong>' . $title . '</strong>';

		$content = wp_trim_words(wp_strip_all_tags($item->get_content()), 6);

		$actions = array(
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-email', $url_atts), __('Edit', 'wp-ultimo')),
			'duplicate'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-email', $url_atts), __('Duplicate', 'wp-ultimo')),
			'send-test'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-email', $url_atts), __('Send Test	', 'wp-ultimo')),
			'delete' => sprintf('<a href="%s">%s</a>', '', __('Delete', 'wp-ultimo')),
		);

		return $title . $content . $this->row_actions($actions);

	} // end column_title;

	/**
	 * Displays the event of the email.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Email $item The email object.
	 * @return string
	 */
	public function column_event($item) {

		$event = $item->get_event();

		return "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono'>{$event}</span>";

	} // end column_event;

	/**
	 * Displays the slug of the email.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Email $item The email object.
	 * @return string
	 */
	public function column_slug($item) {

		$slug = $item->get_slug();

		return "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono'>{$slug}</span>";

	} // end column_slug;

	/**
	 * Displays the target of the email.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Email $item The email object.
	 * @return string
	 */
	public function column_target($item) {

		$event = $item->get_target();

		return "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono'>{$event}</span>";

	} // end column_target;

	/**
	 * Displays if the email is schedule for later send or not.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Email $item The email object.
	 * @return string
	 */
	public function column_schedule($item) {

		if ($item->has_schedule()) {

			if ($item->get_schedule_type() === 'hours') {

				$time = explode(':', $item->get_send_hours());

				$text = sprintf(__('%1$s hour(s) and %2$s minute(s) after the event.', 'wp-ultimo'), $time[0], $time[1]);

			} elseif ($item->get_schedule_type() === 'days') {

				$text = sprintf(__('%s day(s) after the event.', 'wp-ultimo'), $item->get_send_days());

			} // end if;

		} else {

			$text = __('Sent immediately after the event.', 'wp-ultimo');

		} // end if;

		return $text;

	} // end column_schedule;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'title'    => __('Content', 'wp-ultimo'),
			'slug'     => __('Event', 'wp-ultimo'),
			'event'    => __('slug', 'wp-ultimo'),
			'target'   => __('Target', 'wp-ultimo'),
			'schedule' => __('When', 'wp-ultimo'),
			'id'       => __('ID', 'wp-ultimo'),
		);

		return $columns;

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
				'type' => array(
					'label'   => __('Email Type', 'wp-ultimo'),
					'options' => array(
						'email_email'     => __('Email', 'wp-ultimo'),
						'broadcast_email' => __('Notices', 'wp-ultimo'),
					),
				),
			),
			'date_filters' => array(
				'date_created' => array(
					'label'   => __('Date', 'wp-ultimo'),
					'options' => $this->get_default_date_filter_options(),
				),
			),
		);

	} // end get_filters;

	/**
	 * Returns the pre-selected filters on the filter bar.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_views() {

		return array(
			'all'      => array(
				'field' => 'target',
				'url'   => add_query_arg('target', 'all'),
				'label' => __('All Emails', 'wp-ultimo'),
				'count' => 0,
			),
			'admin' => array(
				'field' => 'target',
				'url'   => add_query_arg('target', 'admin'),
				'label' => __('Admin Emails', 'wp-ultimo'),
				'count' => 0,
			),
			'customer' => array(
				'field' => 'target',
				'url'   => add_query_arg('target', 'customer'),
				'label' => __('Customer Emails', 'wp-ultimo'),
				'count' => 0,
			),
		);

	} // end get_views;

} // end class Email_List_Table;
