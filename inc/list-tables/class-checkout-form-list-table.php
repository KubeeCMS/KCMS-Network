<?php
/**
 * Checkout_Form List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Checkout_Form List Table class.
 *
 * @since 2.0.0
 */
class Checkout_Form_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Checkout_Forms\\Checkout_Form_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Checkout Form', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Checkout Forms', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                              // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_get_form_url('add_new_checkout_form'),
				'classes' => 'wubox',
			),
		));

	} // end __construct;

	/**
	 * Displays the content of the product column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Checkout_Form $item Checkout Form object.
	 * @return string
	 */
	public function column_name($item) {

		$url_atts = array(
			'id'    => $item->get_id(),
			'slug'  => $item->get_slug(),
			'model' => 'checkout_form',
		);

		$title = sprintf('<strong><a href="%s">%s</a></strong>', wu_network_admin_url('wp-ultimo-edit-checkout-form', $url_atts), $item->get_name());

		$actions = array(
			'edit'      => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-checkout-form', $url_atts), __('Edit', 'wp-ultimo')),
			'duplicate' => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-checkout-forms', array('action' => 'duplicate', 'id' => $item->get_id())), __('Duplicate', 'wp-ultimo')),
			'get_shortcode'    => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Shortcode', 'wp-ultimo'), wu_get_form_url('shortcode_checkout', $url_atts), __('Shortcode', 'wp-ultimo')),
			'delete'    => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete', 'wp-ultimo'), wu_get_form_url('delete_modal', $url_atts), __('Delete', 'wp-ultimo')),
		);

		return $title . $this->row_actions($actions);

	} // end column_name;

	/**
	 * Displays the slug of the form.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Checkout_Form $item Checkout Form object.
	 * @return string
	 */
	public function column_slug($item) {

		$slug = $item->get_slug();

		return "<span class='wu-bg-gray-200 wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono'>{$slug}</span>";

	} // end column_slug;

	/**
	 * Displays the number pof steps and fields.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Checkout_Form $item Checkout Form object.
	 * @return string
	 */
	public function column_steps($item) {

		return sprintf(__('%1$d Step(s) and %2$d Field(s)', 'wp-ultimo'), $item->get_step_count(), $item->get_field_count());

	} // end column_steps;

	/**
	 * Displays the form shortcode.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Checkout_Form $item Checkout Form object.
	 * @return string
	 */
	public function column_shortcode($item) {

		$button = sprintf('
		<button type="button" data-clipboard-action="copy" data-clipboard-target="#hidden_textarea" class="btn-clipboard" title="%s">
      <span class="dashicons-wu-copy"></span>
    </button>', __('Copy to the Clipboard', 'wp-ultimo'));

		return sprintf('<input class="wu-bg-gray-200 wu-border-none wu-text-gray-700 wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono" value="%s">', esc_attr($item->get_shortcode()), '');

	} // end column_shortcode;

	/**
	 * Handles the bulk processing adding duplication
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_single_action() {

		$bulk_action = $this->current_action();

		if ($bulk_action === 'duplicate') {

			$checkout_form_id = wu_request('id');

			$checkout_form = wu_get_checkout_form($checkout_form_id);

			if (!$checkout_form) {

				WP_Ultimo()->notices->add(__('Checkout form not found.', 'wp-ultimo'), 'error', 'network-admin');

				return;

			} // end if;

			$new_checkout_form = $checkout_form->duplicate();

			$new_name = sprintf(__('Copy of %s', 'wp-ultimo'), $checkout_form->get_name());

			$new_checkout_form->set_name($new_name);

			$new_checkout_form->set_slug(sanitize_title($new_name));

			$new_checkout_form->set_date_created(wu_get_current_time('mysql', true));

			$result = $new_checkout_form->save();

			if (is_wp_error($result)) {

				WP_Ultimo()->notices->add($result->get_error_message(), 'error', 'network-admin');

				return;

			} // end if;

			$redirect_url = wu_network_admin_url('wp-ultimo-edit-checkout-form', array(
				'id'      => $new_checkout_form->get_id(),
				'updated' => 1,
			));

			wp_redirect($redirect_url);

			exit;

		} // end if;

	} // end process_single_action;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'    => '<input type="checkbox" />',
			'name'  => __('Form Name', 'wp-ultimo'),
			'slug'  => __('Form Slug', 'wp-ultimo'),
			'steps' => __('Steps', 'wp-ultimo'),
			'id'    => __('ID', 'wp-ultimo'),
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

} // end class Checkout_Form_List_Table;
