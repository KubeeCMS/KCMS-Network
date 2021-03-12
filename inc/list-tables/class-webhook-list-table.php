<?php
/**
 * Webhook List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Webhook List Table class.
 *
 * @since 2.0.0
 */
class Webhook_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Webhooks\\Webhook_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Webhook', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Webhooks', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                        // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_get_form_url('add_new_webhook_modal'),
				'classes' => 'wubox',
			),
		));
	} // end __construct;

	/**
	 * Displays the content of the name column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Webhook $item Webhook object.
	 * @return string
	 */
	public function column_name($item) {

		$url_atts = array(
			'id'    => $item->get_id(),
			'model' => 'webhook',
		);

		$title = sprintf('<a href="%s"><strong>%s</strong></a>
				<span data-loading="wu_action_button_loading_%s" id="wu_action_button_loading" class="wu-blinking-animation wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold hidden" >%s</span>', wu_network_admin_url('wp-ultimo-edit-webhook', $url_atts), $item->get_name(), $item->get_id(), __('Sending Test..', 'wp-ultimo'));

		$actions = array(
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-webhook', $url_atts), __('Edit', 'wp-ultimo')),
			'test'   => sprintf('<a id="action_button" data-title="' . $item->get_name() . '" data-page="list" data-action="wu_send_test_event" data-event="' . $item->get_event() . '" data-object="' . $item->get_id() . '" data-url="%s" href="">%s</a>', $item->get_webhook_url(), __('Send Test', 'wp-ultimo')),
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

		return $title . $this->row_actions($actions);

	} // end column_name;

	/**
	 * Displays the content of the webhook url column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Webhook $item Webhook object.
	 * @return string
	 */
	public function column_webhook_url($item) {

		$trimmed_url = mb_strimwidth($item->get_webhook_url(), 0, 50, '...');

		return "<span class='wu-py-1 wu-px-2 wu-bg-gray-200 wu-rounded-sm wu-text-gray-700 wu-text-xs wu-font-mono'>{$trimmed_url}</span>";

	} // end column_webhook_url;

	/**
	 * Displays the content of the event column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Webhook $item Webhook object.
	 * @return string
	 */
	public function column_event($item) {

		$event = $item->get_event();

		return "<span class='wu-py-1 wu-px-2 wu-bg-gray-200 wu-rounded-sm wu-text-gray-700 wu-text-xs wu-font-mono'>{$event}</span>";

	} // end column_event;

	/**
	 * Displays the content of the count column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Webhook $item Webhook object.
	 * @return string
	 */
	public function column_count($item) {

		$count = $item->get_count();

		$actions = array(
			'edit' => sprintf('<a href="%s">%s</a>', '', __('See Events', 'wp-ultimo')),
		);

		return $count . $this->row_actions($actions);

	} // end column_count;

	/**
	 * Displays the content of the integration column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Webhook $item Webhook object.
	 * @return string
	 */
	public function column_integration($item) {

		return ucwords(str_replace(array('_', '-'), ' ', $item->get_integration()));

	} // end column_integration;

	/**
	 * Displays the content of the active column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Webhook $item Webhook object.
	 * @return string
	 */
	public function column_active($item) {

		return $item->is_active() ? __('Yes', 'wp-ultimo') : __('No', 'wp-ultimo');

	} // end column_active;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'name'        => __('Name', 'wp-ultimo'),
			'webhook_url' => __('Target URL', 'wp-ultimo'),
			'event'       => __('Trigger Event', 'wp-ultimo'),
			'event_count' => __('Count', 'wp-ultimo'),
			'integration' => __('Integration', 'wp-ultimo'),
			'active'      => __('Active', 'wp-ultimo'),
			'id'          => __('ID', 'wp-ultimo'),
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
			'filters'      => array(),
			'date_filters' => array(),
		);

	} // end get_filters;

} // end class Webhook_List_Table;
