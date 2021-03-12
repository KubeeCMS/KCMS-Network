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
class Signup_Field_Billing_Address extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'billing_address';

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
	 * Is this a user-related field?
	 *
	 * If this is set to true, this field will be hidden
	 * when the user is already logged in.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_user_field() {

		return true;

	} // end is_user_field;

	/**
	 * Requires the title of the field/element type.
	 *
	 * This is used on the Field/Element selection screen.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Billing Address', 'wp-ultimo');

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

		return __('BA Description', 'wp-ultimo');

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

		return 'dashicons-wu-address';

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
			'zip_and_country' => true,
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
			'id'       => 'billing_address',
			'required' => true,
		);

	}  // end force_attributes;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return array(
			'zip_and_country' => array(
				'type'  => 'toggle',
				'title' => __('Display only ZIP and Country?', 'wp-ultimo'),
				'desc'  => __('Checking this option will only add the ZIP and country fields, instead of all the normal billing address fields.', 'wp-ultimo'),
				'value' => true,
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

		$zip_only = wu_string_to_bool($attributes['zip_and_country']);

		$customer = wu_get_current_customer();

		/*
		 * Checks for an existing customer
		 */
		if ($customer) {

			$fields = $customer->get_billing_address()->get_fields($zip_only);

		} else {

			$fields = \WP_Ultimo\Objects\Billing_Address::fields($zip_only);

		} // end if;

		$fields['billing_country']['html_attr'] = array(
			'v-model' => 'country',
		);

		$left_classes  = ' wu-box-border md:wu-w-1/2 md:wu-float-left md:wu-pr-2';
		$right_classes = ' wu-box-border md:wu-w-1/2 md:wu-float-left md:wu-pl-2';

		if ($attributes['zip_and_country']) {

			$fields['billing_zip_code']['wrapper_classes'] .= $left_classes;
			$fields['billing_country']['wrapper_classes']  .= $right_classes;

		} else {

			$fields['company_name']['wrapper_classes']     .= $left_classes;
			$fields['billing_email']['wrapper_classes']    .= $right_classes;
			$fields['tax_id']['wrapper_classes']           .= ' wu-clear-both';
			$fields['billing_city']['wrapper_classes']     .= $left_classes;
			$fields['billing_zip_code']['wrapper_classes'] .= $right_classes;
			$fields['billing_state']['wrapper_classes']    .= $left_classes;
			$fields['billing_country']['wrapper_classes']  .= $right_classes;

		} // end if;

		$fields['clear'] = array(
			'type' => 'note',
			'desc' => '<div class="wu-clear-both"></div>',
		);

		return $fields;

	} // end to_fields_array;

} // end class Signup_Field_Billing_Address;
