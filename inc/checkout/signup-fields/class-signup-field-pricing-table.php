<?php
/**
 * Creates a cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Order
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields;

use \WP_Ultimo\Checkout\Signup_Fields\Base_Signup_Field;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
class Signup_Field_Pricing_Table extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'pricing_table';

	} // end get_type;

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_required() {

		return true;

	} // end is_required;

	/**
	 * Requires the title of the field/element type.
	 *
	 * This is used on the Field/Element selection screen.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Pricing Table', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the description of the field/element.
	 *
	 * This is used as the title attribute of the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('PT Description', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_icon() {

		return 'dashicons-before dashicons-schedule';

	} // end get_icon;

	/**
	 * Returns the default values for the field-elements.
	 *
	 * This is passed through a wp_parse_args before we send the values
	 * to the method that returns the actual fields for the checkout form.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function defaults() {

		return array(
			'pricing_table_products' => array(),
			'pricing_table_template' => 'checkout/partials/pricing-table-list',
		);

	} // end defaults;

	/**
	 * List of keys of the default fields we want to display on the builder.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function default_fields() {

		return array(
			'name',
		);

	} // end default_fields;

	/**
	 * If you want to force a particular attribute to a value, declare it here.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function force_attributes() {

		return array(
			'id'       => 'pricing_table',
			'required' => true,
		);

	} // end force_attributes;

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_pricing_table_templates() {

		$templates = array(
			'checkout/partials/pricing-table-list'        => __('Simple List', 'wp-ultimo'),
			'checkout/partials/legacy-pricing-table-list' => __('Legacy Pricing Table', 'wp-ultimo'),
		);

		return apply_filters('wu_get_pricing_table_templates', $templates);

	} // end get_pricing_table_templates;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = array();

		$editor_fields['pricing_table_products'] = array(
			'type'        => 'model',
			'title'       => __('Products', 'wp-ultimo'),
			'placeholder' => __('Products', 'wp-ultimo'),
			'tooltip'     => '',
			'html_attr'   => array(
				'data-model'        => 'product',
				'data-value-field'  => 'id',
				'data-label-field'  => 'name',
				'data-search-field' => 'name',
				'data-max-items'    => 10,
			),
		);

		$editor_fields['pricing_table_template'] = array(
			'type'        => 'select',
			'title'       => __('Pricing Table Template', 'wp-ultimo'),
			'placeholder' => __('Select your Template', 'wp-ultimo'),
			'options'     => array($this, 'get_pricing_table_templates'),
		);

		$editor_fields['_dev_note_develop_your_own'] = array(
			'type' => 'note',
			'desc' => sprintf('<div class="wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded wu-w-full">%s</div>', __('Want to add customized pricing table templates? See how you can do that here.', 'wp-ultimo')),
		);

		return $editor_fields;

	} // end get_fields;

	/**
	 * Returns the field/element actual field array to be used on the checkout form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes Attributes saved on the editor form.
	 * @return array An array of fields, not the field itself.
	 */
	public function to_fields_array($attributes) {

		$product_list = explode(',', $attributes['pricing_table_products']);

		$products = array_map('wu_get_product', $product_list);

		$products = array_filter($products);

		$content = wu_get_template_contents($attributes['pricing_table_template'], array(
			'products' => $products,
			'name'     => $attributes['name'],
		));

		$checkout_fields[$attributes['id']] = array(
			'id'   => $attributes['id'],
			'type' => 'note',
			'desc' => $content,
		);

		return $checkout_fields;

	} // end to_fields_array;

} // end class Signup_Field_Pricing_Table;
