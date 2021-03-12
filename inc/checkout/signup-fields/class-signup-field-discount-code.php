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
class Signup_Field_Discount_Code extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'discount_code';

	} // end get_type;

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_required() {

		return false;

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

		return __('Discount Code', 'wp-ultimo');

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

		return __('Discount Code Description', 'wp-ultimo');

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

		return 'dashicons-wu-ticket';

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
			'placeholder' => '',
			'default'     => '',
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
			'placeholder',
			'tooltip',
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
			'id' => 'discount_code',
		);

	} // end force_attributes;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return array(
			'display_checkbox' => array(
				'type'    => 'toggle',
				'title'   => __('Display a "Have a coupon code checkbox"?', 'wp-ultimo'),
				'tooltip' => __('This will display a checkbox that the customer has to check before we show the discount code input.', 'wp-ultimo'),
				'value'   => 0,
			),
		);

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

		$checkout_fields = array();

		if ($attributes['display_checkbox']) {

			$checkout_fields['discount_code_checkbox'] = array(
				'id'        => 'discount_code',
				'type'      => 'toggle',
				'name'      => __('Have a coupon code?', 'wp-ultimo'),
				'class'     => 'wu-w-auto',
				'html_attr' => array(
					'v-model' => 'toggle_discount_code',
				),
			);

		} // end if;

		$checkout_fields['discount_code'] = array(
			'type'              => 'text',
			'id'                => 'discount_code',
			'name'              => $attributes['name'],
			'placeholder'       => $attributes['placeholder'],
			'tooltip'           => $attributes['tooltip'],
			'default'           => $attributes['default'],
			'value'             => wu_request('discount_code'),
			'wrapper_html_attr' => array(
				'v-show' => 'toggle_discount_code',
			),
			'html_attr'         => array(
				'v-model.lazy' => 'discount_code',
			),
		);

		return $checkout_fields;

	} // end to_fields_array;

} // end class Signup_Field_Discount_Code;
