<?php
/**
 * Discount_Code List Table class.
 *
 * @package WP_Ultimo
 * @subpackage List_Table
 * @since 2.0.0
 */

namespace WP_Ultimo\List_Tables;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Discount_Code List Table class.
 *
 * @since 2.0.0
 */
class Discount_Code_List_Table extends Base_List_Table {

	/**
	 * Holds the query class for the object being listed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Discount_Codes\\Discount_Code_Query';

	/**
	 * Initializes the table.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(array(
			'singular' => __('Discount Code', 'wp-ultimo'),  // singular name of the listed records
			'plural'   => __('Discount Codes', 'wp-ultimo'), // plural name of the listed records
			'ajax'     => true,                              // does this table support ajax?
			'add_new'  => array(
				'url'     => wu_network_admin_url('wp-ultimo-edit-discount-code'),
				'classes' => '',
			),
		));

	} // end __construct;

	/**
	 * Displays the content of the name column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Discount_Code $item Discount_Code object.
	 * @return string
	 */
	public function column_name($item) {

		$url_atts = array(
			'id'    => $item->get_id(),
			'model' => 'discount_code',
		);

		$title = sprintf('<a href="%s"><strong>%s</strong></a>', wu_network_admin_url('wp-ultimo-edit-discount-code', $url_atts), $item->get_name());

		$actions = array(
			'edit'   => sprintf('<a href="%s">%s</a>', wu_network_admin_url('wp-ultimo-edit-discount-code', $url_atts), __('Edit', 'wp-ultimo')),
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
	 * Displays the content of the value column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Discount_Code $item Discount_Code object.
	 *
	 * @return string
	 */
	public function column_value($item) {

		if (!$item->get_value()) {

			return __('No Discount', 'wp-ultimo');

		} // end if;

		$value = wu_format_currency($item->get_value());

		if ($item->get_type() === 'percentage') {

			$value = number_format($item->get_value(), 0) . '%';

		} // end if;

		// translators: placeholder is the amount of discount. e.g. 10% or $5.
		return sprintf(__('%s OFF', 'wp-ultimo'), $value);

	} // end column_value;

	/**
	 * Displays the content of the setup fee value column.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Discount_Code $item Discount_Code object.
	 *
	 * @return string
	 */
	public function column_setup_fee_value($item) {

		if (!$item->get_setup_fee_value()) {

			return __('No Discount', 'wp-ultimo');

		} // end if;

		$value = wu_format_currency($item->get_setup_fee_value());

		if ($item->get_setup_fee_type() === 'percentage') {

			$value = number_format($item->get_setup_fee_value()) . '%';

		} // end if;

		// translators: placeholder is the amount of discount. e.g. 10% or $5.
		return sprintf(__('%s OFF', 'wp-ultimo'), $value);

	} // end column_setup_fee_value;

	/**
	 * Displays the use limitations.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Discount_Code $item Discount_Code object.
	 * @return string
	 */
	public function column_uses($item) {

		// translators: the placeholder is the number of times this coupon was used.
		$html = sprintf(__('Used %d time(s)', 'wp-ultimo'), $item->get_uses());

		if ($item->get_max_uses() > 0) {

			// translators: the placeholder is the number of times this coupon can be used before becoming inactive.
			$html .= '<small class="wu-block">' . sprintf(__('Allowed uses: %d', 'wp-ultimo'), $item->get_max_uses()) . '</span>';

		} else {

			$html .= '<small class="wu-block">' . __('No Limits', 'wp-ultimo') . '</span>';

		} // end if;

		return $html;

	} // end column_uses;

	/**
	 * Shows the code as a tag.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Ultimo\Models\Discount_Code $item Discount_Code object.
	 * @return string
	 */
	public function column_coupon_code($item) {

		$code = $item->get_code();

		$html = '<span class="wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono wu-bg-gray-200 wu-text-gray-700">' . strtoupper($code) . '</span>';

		$valid = $item->is_valid();

		if (is_wp_error($valid)) {

			$html .= sprintf('<small class="wu-block wu-sans" %s>%s</small>', wu_tooltip_text($valid->get_error_message()), __('Inactive', 'wp-ultimo'));

		} // end if;

		return $html;

	} // end column_coupon_code;

	/**
	 * Returns the list of columns for this particular List Table.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'              => '<input type="checkbox" />',
			'name'            => __('Name', 'wp-ultimo'),
			'coupon_code'     => __('Code', 'wp-ultimo'),
			'uses'            => __('Uses', 'wp-ultimo'),
			'value'           => __('Value', 'wp-ultimo'),
			'setup_fee_value' => __('Setup Fee Value', 'wp-ultimo'),
			'date_expiration' => __('Dates', 'wp-ultimo'),
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

} // end class Discount_Code_List_Table;
