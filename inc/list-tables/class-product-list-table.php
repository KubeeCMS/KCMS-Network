<?php
/**
 * Product List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Product List Table class.
 *
 * @since 2.0.0
 */
class Product_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Products\\Product_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Product', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Products', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                        // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_network_admin_url('wp-ultimo-edit-product'),
				'classes' => '',
			),
		));

	} // end __construct;

	/**
	 * Displays the content of the product column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Product $item Product object.
	 * @return string
	 */
	public function column_name($item) {

		$url_atts = array(
			'id'    => $item->get_id(),
			'model' => 'product'
		);

		$title = sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-product', $url_atts), $item->get_name());

		// Concatenate the two blocks
		$title = "<strong>$title</strong>";

		$actions = array(
			'edit'      => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-product', $url_atts), __('Edit', 'wp-ultimo')),
			'duplicate' => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-products', array('action' => 'duplicate', 'id' => $item->get_id())), __('Duplicate', 'wp-ultimo')),
			'delete'    => sprintf('<a title="%s" class="wubox" href="%s">%s</a>', __('Delete', 'wp-ultimo'), wu_get_form_url('delete_modal', $url_atts), __('Delete', 'wp-ultimo')),
		);

		return $title . $this->row_actions($actions);

	} // end column_name;

	/**
	 * Displays the type of the product.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Product $item Product object.
	 * @return string
	 */
	public function column_type($item) {

		$label = $item->get_type_label();

		$class = $item->get_type_class();

		return "<span class='wu-bg-gray-200 wu-text-gray-700 wu-leading-none wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono $class'>{$label}</span>";

	} // end column_type;

	/**
	 * Displays the slug of the product.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Product $item Product object.
	 * @return string
	 */
	public function column_slug($item) {

		$slug = $item->get_slug();

		return "<span class='wu-bg-gray-200 wu-text-gray-700 wu-leading-none wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono'>{$slug}</span>";

	} // end column_slug;

	/**
	 * Displays the price of the product.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Product $item Product object.
	 * @return string
	 */
	public function column_amount($item) {

		if ($item->get_pricing_type() === 'contact_us') {

			return __('None', 'wp-ultimo') . sprintf('<br><small>%s</small>', __('Requires contact', 'wp-ultimo'));

		} // end if;

		if (empty($item->get_amount())) {

			return __('Free', 'wp-ultimo');

		} // end if;

		$amount = wu_format_currency($item->get_amount(), $item->get_currency());

		if ($item->is_recurring()) {

			$duration = $item->get_duration();

			$message = sprintf(
				// translators: %1$s is the formatted price, %2$s the duration, and %3$s the duration unit (day, week, month, etc)
				_n('every %2$s', 'every %1$s %2$s', $duration, 'wp-ultimo'), // phpcs:ignore
				$duration,
				$item->get_duration_unit()
			);

			if (!$item->is_forever_recurring()) {

				$billing_cycles_message = sprintf(
					// translators: %s is the number of billing cycles.
					_n('for %s cycle', 'for %s cycles', $item->get_billing_cycles(), 'wp-ultimo'),
					$item->get_billing_cycles()
				);

				$message .= ' ' . $billing_cycles_message;

			} // end if;

		} else {

			$message = __('one time payment', 'wp-ultimo');

		} // end if;

		return sprintf('%s<br><small>%s</small>', $amount, $message);

	} // end column_amount;

	/**
	 * Displays the setup fee of the product.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Product $item Product object.
	 * @return string
	 */
	public function column_setup_fee($item) {

		if ($item->get_pricing_type() === 'contact_us') {

			return __('None', 'wp-ultimo') . sprintf('<br><small>%s</small>', __('Requires contact', 'wp-ultimo'));

		} // end if;

		if (!$item->has_setup_fee()) {

			return __('No Setup Fee', 'wp-ultimo');

		} // end if;

		return wu_format_currency($item->get_setup_fee(), $item->get_currency());

	} // end column_setup_fee;

	/**
	 * Handles the bulk processing adding duplication.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function process_single_action() {

		$bulk_action = $this->current_action();

		if ($bulk_action === 'duplicate') {

			$product = wu_request('id');

			$product = wu_get_product($product);

			if (!$product) {

				WP_Ultimo()->notices->add(__('Product not found.', 'wp-ultimo'), 'error', 'network-admin');

				return;

			} // end if;

			$new_product = $product->duplicate();

			$new_name = sprintf(__('Copy of %s', 'wp-ultimo'), $product->get_name());

			$new_product->set_name($new_name);

			$new_product->set_slug(sanitize_title($new_name . '-' . time()));

			$new_product->set_date_created(wu_get_current_time('mysql', true));

			$result = $new_product->save();

			if (is_wp_error($result)) {

				WP_Ultimo()->notices->add($result->get_error_message(), 'error', 'network-admin');

				return;

			} // end if;

			$redirect_url = wu_network_admin_url('wp-ultimo-edit-product', array(
				'id'      => $new_product->get_id(),
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
			'cb'                => '<input type="checkbox" />',
			'featured_image_id' => '<span class="dashicons-wu-image"></span>',
			'name'              => __('Name', 'wp-ultimo'),
			'type'              => __('Type', 'wp-ultimo'),
			'slug'              => __('Slug', 'wp-ultimo'),
			'amount'            => __('Price', 'wp-ultimo'),
			'setup_fee'         => __('Setup Fee', 'wp-ultimo'),
			'id'                => __('ID', 'wp-ultimo'),
		);

		return $columns;

	} // end get_columns;

	/**
	 * Handles the item display for grid mode.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Product $item The line item being displayed.
	 * @return void
	 */
	public function single_row_grid($item) {

		wu_get_template('base/products/grid-item', array(
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

		return array(
			'filters' => array(),
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
			'all'     => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'all'),
				'label' => __('All Products', 'wp-ultimo'),
				'count' => 0,
			),
			'plan'    => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'plan'),
				'label' => __('Plans', 'wp-ultimo'),
				'count' => 0,
			),
			'package' => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'package'),
				'label' => __('Packages', 'wp-ultimo'),
				'count' => 0,
			),
			'service' => array(
				'field' => 'type',
				'url'   => add_query_arg('type', 'service'),
				'label' => __('Services', 'wp-ultimo'),
				'count' => 0,
			),
		);

	} // end get_views;

} // end class Product_List_Table;
