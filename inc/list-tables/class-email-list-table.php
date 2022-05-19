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
			'model' => 'email'
		);

		$title = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-email', $url_atts), $item->get_title());

		$target = $item->get_target();

		$title = '<div><strong class="wu-inline-block wu-pr-1">' . $title . '</strong> <span class="wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono">' . $target . '</span></div>';

		$content = wp_trim_words(wp_strip_all_tags($item->get_content()), 6);

		$actions = array(
			'edit'      => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-email', $url_atts), __('Edit', 'wp-ultimo')),
			'duplicate' => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-email', $url_atts), __('Duplicate', 'wp-ultimo')),
			'send-test' => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Send Test Email', 'wp-ultimo'), wu_get_form_url('send_new_test', $url_atts), __('Send Test Email', 'wp-ultimo'))
		);

		$slug = $item->get_slug();

		$default_system_emails = wu_get_default_system_emails();

		if (isset($default_system_emails[$slug])) {

			$actions['reset'] = sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Reset', 'wp-ultimo'), wu_get_form_url('reset_confirmation', $url_atts), __('Reset', 'wp-ultimo'));

		} // end if;

		$actions['delete'] = sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete', 'wp-ultimo'), wu_get_form_url('delete_modal', $url_atts), __('Delete', 'wp-ultimo'));

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
			'schedule' => __('When', 'wp-ultimo'),
			'id'       => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

    /**
	 * Handles the bulk processing adding duplication.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_single_action() {

		$bulk_action = $this->current_action();

		if ($bulk_action === 'duplicate') {

			$email_id = wu_request('id');

			$email = wu_get_email($email_id);

			if (!$email) {

				WP_Ultimo()->notices->add(__('Email not found.', 'wp-ultimo'), 'error', 'network-admin');

				return;

			} // end if;

			$new_email = $email->duplicate();

			$new_name = sprintf(__('Copy of %s', 'wp-ultimo'), $email->get_name());

			$new_email->set_name($new_name);

			$new_email->set_slug(sanitize_title($new_name));

			$new_email->set_target($email->get_target());

			$new_email->set_style($email->get_style());

			$new_email->set_event($email->get_event());

			if ($email->has_schedule()) {

				$new_email->set_schedule($email->has_schedule());

				if ($email->get_schedule_type() === 'hours') {

					$new_email->set_send_hours($email->get_send_hours());

				} elseif ($email->get_schedule_type() === 'days') {

					$new_email->set_send_days($email->get_send_days());

				} // end if;

			} // end if;

			if ($email->get_custom_sender()) {

				$new_email->set_custom_sender($email->get_custom_sender());

				$new_email->set_custom_sender_name($email->get_custom_sender_name());

				$new_email->set_custom_sender_email($email->get_custom_sender_email());

			} // end if;

			$new_email->set_send_copy_to_admin($email->get_send_copy_to_admin());

			$new_email->set_date_created(wu_get_current_time('mysql', true));

			$result = $new_email->save();

			if (is_wp_error($result)) {

				WP_Ultimo()->notices->add($result->get_error_message(), 'error', 'network-admin');

				return;

			} // end if;

			$redirect_url = wu_network_admin_url('wp-ultimo-edit-email', array(
				'id'      => $new_email->get_id(),
				'updated' => 1,
			));

			wp_redirect($redirect_url);

			exit;

		} // end if;

	} // end process_single_action;

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
