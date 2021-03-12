<?php
/**
 * Support Agent List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Support Agent List Table class.
 *
 * @since 2.0.0
 */
class Support_Agent_List_Table extends Customer_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Customers\\Support_Agent_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->modes = array(
			'list' => __('List View'),
			'grid' => __('Grid View'),
		);

		parent::__construct(array(
			'singular' => __('Support Agent', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Support Agents', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                              // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_get_form_url('add_new_support_agent'),
				'classes' => 'wubox',
			),
		));

	} // end __construct;

	/**
	 * Adds the extra search field when the search element is present.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_query_fields() {

		$_filter_fields = parent::get_extra_query_fields();

		$_filter_fields['type'] = 'support-agent';

		return $_filter_fields;

	} // end get_extra_query_fields;

	/**
	 * Handles the item display for grid mode.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Support_Agent $item The line item being displayed.
	 * @return void
	 */
	public function single_row_grid($item) {

		wu_get_template('base/support-agents/grid-item', array(
			'item'  => $item,
			'table' => $this,
		));

	} // end single_row_grid;


	/**
	 * Displays the content of the name column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Customer $item Customer object.
	 * @return string
	 */
	public function column_name($item) {

		// Get user info
		$user = get_user_by('id', $item->get_user_id());

		// Check if user exists
		if (!$user) {

			$actions = array(
				'delete' => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete', 'wp-ultimo'), wu_get_form_url('delete_customer', $url_atts), __('Delete', 'wp-ultimo')),
			);

			return sprintf('<strong>#%s</strong> - %s', $item->get_user_id(), __('User not found', 'wp-ultimo')) . $this->row_actions($actions);

		} // end if;

		$customer_id = sprintf('<a href="?page=wp-ultimo-edit-support-agent&id=%s"><strong>#%s</strong></a>', $item->get_id(), $item->get_id());

		$customer_user = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-customer', array(
			'id' => $item->get_id(),
		)), $user->display_name);

		// Concatenate the two blocks
		$title = "<strong>$customer_user</strong>";

		$desc = sprintf('<a %s href="mailto:%s" class="description wu-ml-1 wu-text-xs">(%s)</a>', wu_tooltip_text(__('Send an email to this customer', 'wp-ultimo')), $user->user_email, $user->user_email);

		$url_atts = array(
			'id' => $item->get_id(),
		);

		// Concatenate switch to url
		$is_modal_switch_to = \WP_Ultimo\User_Switching::get_instance()->check_user_switching_is_activated() ? '' : 'wubox';
		$url_switch_to      = sprintf('<a class="%s" href="%s">%s</a>', $is_modal_switch_to, \WP_Ultimo\User_Switching::get_instance()->render($item->get_user_id()), __('Switch To', 'wp-ultimo'));

		$actions = array(
			'edit'      => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-support-agent', $url_atts), __('Edit', 'wp-ultimo')),
			'switch-to' => $url_switch_to,
			'delete'    => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete', 'wp-ultimo'), wu_get_form_url('delete_customer', $url_atts), __('Delete', 'wp-ultimo')),
		);

		return $title . $desc . $this->row_actions($actions);

	} // end column_name;

	/**
	 * Method for caps column
	 *
	 * @since 2.0.0
	 *
	 * @param array $item an array of DB data.
	 *
	 * @return string
	 */
	function column_caps($item) {

		$support_agent_caps = wu_get_support_agent_capabilities($item->get_user_id());

		$user_existing_caps = array();

		$caps_default_wordpress = \WP_Ultimo\Permission_Control::get_instance()->get_capabilities_default_wordpress();

		$caps_custom_wu = \WP_Ultimo\Permission_Control::get_instance()->registered_capabilities();

		$caps = '';

		foreach (get_userdata(get_current_user_id())->allcaps as $key => $cap) {

			if (array_key_exists($key, $caps_default_wordpress)) {

				$user_existing_caps[] = $key;

			} // end if;

		} // end foreach;

		$support_agent_caps = $support_agent_caps + $user_existing_caps;

		foreach ($support_agent_caps as $key => $cap) {

			if ($cap) {

				if (array_key_exists($key, $caps_default_wordpress['wordpress']['capabilities'])) {

					$caps .= $caps_default_wordpress['wordpress']['capabilities'][$key]['title'] . '<br />';

				} else {

					foreach ($caps_custom_wu as $each) {

						if (array_key_exists($key, $each['capabilities'])) {

							$caps .= '<span class="dashicons-before dashicons-wu-wp-ultimo text-xs"></span> ' . $each['capabilities'][$key]['title'] . '<br />';

						} // end if;

					} // end foreach;

				} // end if;

			} // end if;

		} // end foreach;

		return !empty($caps) ? $caps : '';

	}  // end column_caps;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'customer_status' => '',
			'name'            => __('Name', 'wp-ultimo'),
			'last_login'      => __('Last Login', 'wp-ultimo'),
			'date_registered' => __('Agent Since', 'wp-ultimo'),
			'caps'            => __('Capabilities', 'wp-ultimo'),
			'id'              => __('ID', 'wp-ultimo'),
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


	/**
	 * Returns the pre-selected filters on the filter bar.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_views() {

		return array(
			'all'      => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'all'),
				'label' => __('All Support Agents', 'wp-ultimo'),
				'count' => 0,
			),
		);

	} // end get_views;

} // end class Support_Agent_List_Table;
