<?php
/**
 * Customer List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer List Table class.
 *
 * @since 2.0.0
 */
class Customer_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Customers\\Customer_Query';

	/**
	 * Initializes the table.
	 *
	 * @param array $args Table attributes.
	 * @since 2.0.0
	 */
	public function __construct($args = array()) {

		$this->modes = array(
			'grid' => __('Grid View'),
			'list' => __('List View'),
		);

		$args = wp_parse_args($args, array(
			'singular' => __('Customer', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Customers', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                         // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_get_form_url('add_new_customer'),
				'classes' => 'wubox',
			),
		));

		parent::__construct($args);

	} // end __construct;

	/**
	 * Adds the extra search field when the search element is present.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_query_fields() {

		$_filter_fields = parent::get_extra_query_fields();

		$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : false;

		if (!empty($search)) {

			// Search relevant users
			$user_ids = get_users(array(
				'number' => -1,
				'search' => '*' . $search . '*',
				'fields' => 'ids',
			));

			// No results, go back
			if (empty($user_ids)) {

				return $_filter_fields;

			} // end if;

			// Finally, include these user IDs in the customers query.
			$_filter_fields['user_id__in'] = $user_ids;

			unset($_filter_fields['search']);

		} // end if;

		$_filter_fields['type'] = 'customer';

		if (wu_request('filter', 'all') === 'vip') {

			$_filter_fields['vip'] = 1;

		} elseif (wu_request('filter', 'all') === 'online') {

			$_filter_fields['last_login_query'] = array(
				'after' => '-3 minutes',
			);

		} // end if;

		return $_filter_fields;

	} // end get_extra_query_fields;

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

		$url_atts = array(
			'id' => $item->get_id(),
		);

		// Check if user exists
		if (!$user) {

			$actions = array(
				'delete' => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete', 'wp-ultimo'), wu_get_form_url('delete_modal', $url_atts), __('Delete', 'wp-ultimo')),
			);

			return sprintf('<strong>#%s</strong> - %s', $item->get_user_id(), __('User not found', 'wp-ultimo')) . $this->row_actions($actions);

		} // end if;

		$customer_id = sprintf('<a href="?page=wp-ultimo-edit-customer&id=%s"><strong>#%s</strong></a>', $item->get_id(), $item->get_id());

		$customer_user = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-customer', array(
			'id' => $item->get_id(),
		)), $user->display_name);

		// Concatenate the two blocks
		$title = "<strong>$customer_user</strong>";

		$desc = sprintf('<a %s href="mailto:%s" class="description wu-ml-1 wu-text-xs">(%s)</a>', wu_tooltip_text(__('Send an email to this customer', 'wp-ultimo')), $user->user_email, $user->user_email);

		// Concatenate switch to url
		$is_modal_switch_to = \WP_Ultimo\User_Switching::get_instance()->check_user_switching_is_activated() ? '' : 'wubox';

		$url_switch_to      = sprintf('<a title="%s" class="%s" href="%s">%s</a>', __('Switch To', 'wp-ultimo'), $is_modal_switch_to, \WP_Ultimo\User_Switching::get_instance()->render($item->get_user_id()), __('Switch To', 'wp-ultimo'));

		$actions = array(
			'edit'      => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-customer', $url_atts), __('Edit', 'wp-ultimo')),
			'switch-to' => $item->get_user_id() !== get_current_user_id() ? $url_switch_to : false,
			'delete'    => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Delete', 'wp-ultimo'),
				wu_get_form_url(
					'delete_modal',
					array(
						'model' => 'customer',
						'id'    => $item->get_id()
					)
				),
				__('Delete', 'wp-ultimo')
			),
		);

		$actions = array_filter($actions);

		return $title . $desc . $this->row_actions($actions);

	} // end column_name;

	/**
	 * Displays the customer photo and special status.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Customer $item Customer object.
	 * @return string
	 */
	public function column_customer_status($item) {

		$html = '<div class="wu-status-container">';

		if ($item->is_vip()) {

			$html .= sprintf('<span class="wu-tag wu-customer-vip">%s</span>', __('VIP', 'wp-ultimo'));

		} // end if;

		$html .= get_avatar($item->get_user_id(), 36, 'identicon', '', array(
			'force_display' => true,
		));

		$html .= '</div>';

		return $html;

	} // end column_customer_status;

	/**
	 * Returns the number of memberships owned by this customer.
	 *
	 * @since 2.0.0
	 *
	 * @todo: Make this works.
	 * @param WP_Ultimo\Models\Customer $item Customer object.
	 * @return string
	 */
	public function column_memberships($item) {

		$subscription_count = count($item->get_memberships());

		$url_atts = array(
			'customer_id' => $item->get_id(),
		);

		$actions = array(
			'view' => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-memberships', $url_atts), __('View', 'wp-ultimo')),
		);

		return $subscription_count . $this->row_actions($actions);

	} // end column_memberships;

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
			'date_registered' => __('Customer Since', 'wp-ultimo'),
			'memberships'     => __('Memberships', 'wp-ultimo'),
			'id'              => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

	/**
	 * Handles the item display for grid mode.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Customer $item The line item being displayed.
	 * @return void
	 */
	public function single_row_grid($item) {

		wu_get_template('base/customers/grid-item', array(
			'item'  => $item,
			'table' => $this,
		));

	} // end single_row_grid;

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 * @return boolean|array
	 */
	public function get_filters() {

		$filters = $this->get_schema_columns(array(
			'searchable' => true,
			'date_query' => true,
		), 'or');

		$labels = $this->get_columns();

		$filters = array_map(function($item) use ($labels) {

			$item->label = wu_get_isset($labels, $item->name);

			if ($item->label) {

				$item->label = sprintf('%s (%s)', $item->label, $item->name);

			} else {

				$item->label = $item->name;

			} // end if;

			if ($item->date_query === true) {

				$item->filter_type = 'date';
				$item->rule        = 'is_after';

			} elseif (in_array(strtolower($item->name), array('smallint'), true)) {

				$item->filter_type = 'bool';
				$item->rule        = 'is_true';

			} else {

				$item->filter_type = 'text';
				$item->rule        = 'is';

			} // end if;

			return array(
				'field' => $item->name,
				'label' => $item->label,
				'type'  => $item->filter_type,
				'rule'  => $item->rule,
				'value' => wu_request($item->name, ''),
			);

		}, $filters);

		return array_values($filters);

	} // end get_filters;

	/**
	 * Returns the pre-selected filters on the filter bar.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_views() {

		return array(
			'all'    => array(
				'field' => 'filter',
				'url'   => add_query_arg(array(
					'filter' => 'all'
				)),
				'label' => __('All Customers', 'wp-ultimo'),
				'count' => 0,
			),
			'vip'    => array(
				'field' => 'filter',
				'url'   => add_query_arg('filter', 'vip'),
				'label' => __('VIP Customers', 'wp-ultimo'),
				'count' => 0,
			),
			'online' => array(
				'field' => 'filter',
				'url'   => add_query_arg('filter', 'online'),
				'label' => __('Online Customers', 'wp-ultimo'),
				'count' => 0,
			),
		);

	} // end get_views;

	/**
	 * Displays the last login.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Customer $item Customer object.
	 * @return string The last login information.
	 */
	public function column_last_login($item) {

		if ($item->is_online()) {

			return '<span class="wu-inline-block wu-mr-1 wu-rounded-full wu-h-2 wu-w-2 wu-bg-green-500"></span>' . __('Online', 'wp-ultimo');

		} // end if;

		return $this->_column_datetime($item->get_last_login());

	} // end column_last_login;

} // end class Customer_List_Table;
