<?php
/**
 * Domain List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

use \WP_Ultimo\Models\Domain;
use WP_Ultimo\Database\Domains\Domain_Stage;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Domain List Table class.
 *
 * @since 2.0.0
 */
class Domain_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Domains\\Domain_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Domain', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Domains', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                       // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_get_form_url('add_new_domain'),
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

		if (wu_request('blog_id')) {

			$_filter_fields['blog_id'] = wu_request('blog_id');

		} // end if;

		return $_filter_fields;

	} // end get_extra_query_fields;

	/**
	 * Displays the content of the domain column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Domain $item Domain object.
	 * @return string
	 */
	public function column_domain($item) {

		$url_atts = array(
			'id'    => $item->get_id(),
			'model' => 'domain',
		);

		$domain = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-domain', $url_atts), $item->get_domain());

		$html = "<span class='wu-font-mono'><strong>{$domain}</strong></span>";

		$actions = array(
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-domain', $url_atts), __('Edit', 'wp-ultimo')),
			'delete' => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete', 'wp-ultimo'), wu_get_form_url('delete_modal', $url_atts), __('Delete', 'wp-ultimo')),
		);

		return $html . $this->row_actions($actions);

	} // end column_domain;

	/**
	 * Displays the content of the active column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Domain $item Domain object.
	 * @return string
	 */
	public function column_active($item) {

		return $item->is_active() ? __('Yes', 'wp-ultimo') : __('No', 'wp-ultimo');

	} // end column_active;

	/**
	 * Displays the content of the primary domain column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Domain $item Domain object.
	 * @return string
	 */
	public function column_primary_domain($item) {

		return $item->is_primary_domain() ? __('Yes', 'wp-ultimo') : __('No', 'wp-ultimo');

	} // end column_primary_domain;

	/**
	 * Displays the content of the secure column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Domain $item Domain object.
	 * @return string
	 */
	public function column_secure($item) {

		return $item->is_secure() ? __('Yes', 'wp-ultimo') : __('No', 'wp-ultimo');

	} // end column_secure;

	/**
	 * Returns the markup for the stage column.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Domain $item The domain being displayed.
	 * @return string
	 */
	public function column_stage($item) {

		$label = $item->get_stage_label();

		$class = $item->get_stage_class();

		return "<span class='wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-leading-none wu-font-mono $class'>{$label}</span>";

	} // end column_stage;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'domain'         => __('Domain', 'wp-ultimo'),
			'stage'          => __('Stage', 'wp-ultimo'),
			'blog_id'        => __('Site', 'wp-ultimo'),
			'active'         => __('Active', 'wp-ultimo'),
			'primary_domain' => __('Primary', 'wp-ultimo'),
			'secure'         => __('HTTPS', 'wp-ultimo'),
			'id'             => __('ID', 'wp-ultimo'),
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

				/**
				 * Active
				 */
				'active'         => array(
					'label'   => __('Active', 'wp-ultimo'),
					'options' => array(
						0 => __('Inactive', 'wp-ultimo'),
						1 => __('Active', 'wp-ultimo'),
					),
				),

				/**
				 * Primay
				 */
				'primary_domain' => array(
					'label'   => __('Is Primary', 'wp-ultimo'),
					'options' => array(
						0 => __('Not Primary Domain', 'wp-ultimo'),
						1 => __('Primary Domain', 'wp-ultimo'),
					),
				),

				/**
				 * Secure (HTTPS)
				 */
				'secure'         => array(
					'label'   => __('HTTPS', 'wp-ultimo'),
					'options' => array(
						0 => __('Non-HTTPS', 'wp-ultimo'),
						1 => __('HTTPS', 'wp-ultimo'),
					),
				),

				/**
				 * Stage
				 */
				'stage'          => array(
					'label'   => __('Verification Stage', 'wp-ultimo'),
					'options' => Domain_Stage::to_array(),
				),

			),
			'date_filters' => array(

			),
		);

	} // end get_filters;

} // end class Domain_List_Table;
