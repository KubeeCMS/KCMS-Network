<?php
/**
 * Site List Table class.
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
class Site_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Sites\\Site_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->modes = array(
			'grid' => __('Grid View'),
			'list' => __('List View'),
		);

		parent::__construct(array(
			'singular' => __('Site', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Sites', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                     // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_get_form_url('add_new_site'),
				'classes' => 'wubox',
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

		$type = wu_request('type');

		if ($type === 'pending') {

			$pending_sites = \WP_Ultimo\Models\Site::get_all_by_type('pending');

			return $count ? count($pending_sites) : $pending_sites;

		} // end if;

		$query = array(
			'number' => $per_page,
			'offset' => ($page_number - 1) * $per_page,
			'count'  => $count,
			'search' => wu_request('s'),
		);

		if ($type && $type !== 'all') {

			$query['meta_query'] = array(
				'type' => array(
					'key'   => 'wu_type',
					'value' => $type,
				),
			);

		} // end if;

		$query = apply_filters("wu_{$this->id}_get_items", $query, $this);

		return wu_get_sites($query);

	} // end get_items;

	/**
	 * Render the bulk edit checkbox.
	 *
	 * @param WP_Ultimo\Models\Site $item Site object.
	 *
	 * @return string
	 */
	public function column_cb($item) {

		if ($item->get_type() === 'pending') {

			return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->get_membership_id());

		} // end if;

		return sprintf('<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->get_id());

	} // end column_cb;

	/**
	 * Displays the content of the name column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Site $item Site object.
	 * @return string
	 */
	public function column_path($item) {

		$url_atts = array(
			'id'    => $item->get_id(),
			'model' => 'site'
		);

		$title = $item->get_title();

		$title = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-site', $url_atts), $item->get_title());

		// Concatenate the two blocks
		$title = "<strong>$title</strong>";

		$actions = array(
			'edit'      => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-site', $url_atts), __('Edit', 'wp-ultimo')),
			'duplicate' => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Duplicate Site', 'wp-ultimo'),
				wu_get_form_url(
					'add_new_site',
					$url_atts
				),
				__('Duplicate', 'wp-ultimo')
			),
			'delete'    => sprintf(
				'<a title="%s" class="wubox" href="%s">%s</a>',
				__('Delete', 'wp-ultimo'),
				wu_get_form_url(
					'delete_modal',
					$url_atts
				),
				__('Delete', 'wp-ultimo')
			),
		);

		if ($item->get_type() === 'pending') {

			$actions = array(
				'duplicate' => sprintf(
					'<a title="%s" class="wubox" href="%s">%s</a>',
					__('Publish Site', 'wp-ultimo'),
					wu_get_form_url(
						'publish_pending_site', array('membership_id' => $item->get_membership_id())
					),
					__('Publish', 'wp-ultimo')
				),
				'delete'    => sprintf(
					'<a title="%s" class="wubox" href="%s">%s</a>',
					__('Delete', 'wp-ultimo'),
					wu_get_form_url(
						'delete_modal',
						array(
							'id'          => $item->get_membership_id(),
							'model'       => 'membership_meta_pending_site',
							'redirect_to' => urlencode(wu_network_admin_url('wp-ultimo-sites', array(
								'type' => 'pending',
								'page' => wu_request('page', 1),
							))),
						)
					),
					__('Delete', 'wp-ultimo')
				),
			);

		} // end if;

		return $title . sprintf('<span class="wu-block">%s</span>', make_clickable($item->get_active_site_url())) . $this->row_actions($actions);

	} // end column_path;

	/**
	 * Returns the date of the customer registration.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Site $item Site object.
	 * @return string
	 */
	public function column_date_registered($item) {

		$time = strtotime($item->get_last_login(false));

		return $item->get_date_registered() . sprintf('<br><small>%s</small>', human_time_diff($time));

	} // end column_date_registered;

	/**
	 * Returns the blog_id.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Site $item Site object.
	 * @return string
	 */
	public function column_blog_id($item) {

		return $item->get_type() === \WP_Ultimo\Database\Sites\Site_Type::PENDING ? '--' : $item->get_blog_id();

	} // end column_blog_id;

	/**
	 * Displays the type of the site.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Site $item Site object.
	 * @return string
	 */
	public function column_type($item) {

		$label = $item->get_type_label();

		$class = $item->get_type_class();

		return "<span class='wu-bg-gray-200 wu-py-1 wu-px-2 wu-leading-none wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";

	} // end column_type;

	/**
	 * Column for the domains associated with this site.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Site $item Site object.
	 * @return string
	 */
	public function column_domains($item) {

		$domain = wu_get_domains(array(
			'blog_id' => $item->get_id(),
			'count'   => true,
		));

		$url_atts = array(
			'blog_id' => $item->get_id(),
		);

		$actions = array(
			'view' => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-domains', $url_atts), __('View', 'wp-ultimo')),
		);

		return $domain . $this->row_actions($actions);

	} // end column_domains;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'featured_image_id' => '<span class="dashicons-wu-image"></span>',
			'path'              => __('URL', 'wp-ultimo'),
			'type'              => __('Type', 'wp-ultimo'),
			'customer'          => __('Customer', 'wp-ultimo'),
			'membership'        => __('Membership', 'wp-ultimo'),
			'domains'           => __('Domains', 'wp-ultimo'),
			'blog_id'           => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

	/**
	 * Renders the customer card for grid mode.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Customer $item The customer being shown.
	 * @return void
	 */
	public function single_row_grid($item) {

		wu_get_template('base/sites/grid-item', array(
			'item'       => $item,
			'list_table' => $this,
		));

	} // end single_row_grid;

	/**
	 * Returns the filters for this page.
	 *
	 * @since 2.0.0
	 * @return boolean|array
	 */
	public function get_filters() {

		return array(
			'filters'      => array(
				'vip' => array(
					'label'   => __('VIP Status', 'wp-ultimo'),
					'options' => array(
						'0' => __('Regular Sites', 'wp-ultimo'),
						'1' => __('VIP Sites', 'wp-ultimo'),
					),
				),
			),
			'date_filters' => array(
				'last_login'      => array(
					'label'   => __('Last Login', 'wp-ultimo'),
					'options' => $this->get_default_date_filter_options(),
				),
				'date_registered' => array(
					'label'   => __('Site Since', 'wp-ultimo'),
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
			'all'            => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'all'),
				'label' => __('All Sites', 'wp-ultimo'),
				'count' => 0,
			),
			'customer_owned' => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'customer_owned'),
				'label' => __('Customer-Owned', 'wp-ultimo'),
				'count' => 0,
			),
			'site_template'  => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'site_template'),
				'label' => __('Templates', 'wp-ultimo'),
				'count' => 0,
			),
			'pending'        => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'pending'),
				'label' => __('Pending', 'wp-ultimo'),
				'count' => 0,
			),
		);

	} // end get_views;

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		$actions = array(
			'screenshot' => __('Take Screenshot', 'wp-ultimo'),
		);

		$actions[wu_request('type', 'all') === 'pending' ? 'delete-pending' : 'delete'] = __('Delete', 'wp-ultimo');

		return $actions;

	} // end get_bulk_actions;

	/**
	 * Handles the bulk processing.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_single_action() {

		$action = $this->current_action();

		if ($action === 'duplicate') {

			$site_id = wu_request('id');

			$site = wu_get_site($site_id);

			if (!$site) {

				WP_Ultimo()->notices->add(__('Site not found.', 'wp-ultimo'), 'error', 'network-admin');

				return;

			} // end if;

			$new_site = $site->duplicate();

			$new_name = sprintf(__('Copy of %s', 'wp-ultimo'), $new_site->get_title());

			$new_path = sprintf('%s%s', trim($new_site->get_path(), '/'), 'copy');

			$new_site->set_template_id($new_site->get_blog_id());

			$new_site->set_blog_id(0);

			$new_site->get_title($new_name);

			$new_site->set_path($new_path);

			$new_site->site_date_registered(wu_get_current_time('mysql', true));

			$result = $new_site->save();

			if (is_wp_error($result)) {

				WP_Ultimo()->notices->add($result->get_error_message(), 'error', 'network-admin');

				return;

			} // end if;

			$redirect_url = wu_network_admin_url('wp-ultimo-edit-site', array(
				'id'      => $new_site->get_id(),
				'updated' => 1,
			));

			wp_redirect($redirect_url);

			exit;

		} // end if;

	} // end process_single_action;

} // end class Site_List_Table;
