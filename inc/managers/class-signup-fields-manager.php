<?php
/**
 * Signup Fields Manager
 *
 * Keeps track of the registered signup field types.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Signup_Fields
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Keeps track of the registered signup field types.
 *
 * @since 2.0.0
 */
class Signup_Fields_Manager extends Base_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the instantiated fields.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $instantiated_field_types;

	/**
	 * Returns the list of registered signup field types.
	 *
	 * Developers looking for add new types of fields to the signup
	 * should use the filter wu_checkout_forms_field_types to do so.
	 *
	 * @see wu_checkout_forms_field_types
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_field_types() {

		$field_types = array(
			'pricing_table'      => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Pricing_Table',
			'period_selection'   => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Period_Selection',
			'products'           => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Products',
			'template_selection' => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Template_Selection',
			'username'           => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Username',
			'email'              => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Email',
			'password'           => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Password',
			'site_title'         => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Site_Title',
			'site_url'           => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Site_Url',
			'discount_code'      => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Discount_Code',
			'order_summary'      => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Order_Summary',
			'payment'            => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Payment',
			'order_bump'         => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Order_Bump',
			'billing_address'    => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Billing_Address',
			'steps'              => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Steps',
			'text'               => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Text',
			'checkbox'           => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Checkbox',
			'color_picker'       => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Color',
			'select'             => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Select',
			'hidden'             => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Hidden',
			'shortcode'          => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Shortcode',
			'terms_of_use'       => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Terms_Of_Use',
			'submit_button'      => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Signup_Field_Submit_Button',
		);

		/*
		 * Allow developers to add new field types
		 */
		do_action('wu_register_field_types');

		/**
		 * Our APIs to add new field types hook into here.
		 * Do not use this filter directly. Use the wu_register_field_type()
		 * function instead.
		 *
		 * @see wu_register_field_type()
		 *
		 * @since 2.0.0
		 * @param array $field_types
		 * @return array
		 */
		return apply_filters('wu_checkout_field_types', $field_types);

	} // end get_field_types;

	/**
	 * Instantiate a field type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class_name The class name.
	 * @return \WP_Ultimo\Checkout\Signup_Fields\Base_Signup_Field
	 */
	public function instantiate_field_type($class_name) {

		return new $class_name;

	} // end instantiate_field_type;

	/**
	 * Returns an array with all fields, instantiated.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_instantiated_field_types() {

		if ($this->instantiated_field_types === null) {

			$this->instantiated_field_types = array_map(array($this, 'instantiate_field_type'), $this->get_field_types());

		} // end if;

		return $this->instantiated_field_types;

	} // end get_instantiated_field_types;

	/**
	 * Returns a list of all the required fields that must be present on a CF.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_required_fields() {

		$fields = $this->get_instantiated_field_types();

		$fields = array_filter($fields, function($item) {

			return $item->is_required();

		});

		return $fields;

	} // end get_required_fields;

	/**
	 * Returns a list of all the user fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_user_fields() {

		$fields = $this->get_instantiated_field_types();

		$fields = array_filter($fields, function($item) {

			return $item->is_user_field();

		});

		return $fields;

	} // end get_user_fields;

	/**
	 * Returns a list of all the site fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_site_fields() {

		$fields = $this->get_instantiated_field_types();

		$fields = array_filter($fields, function($item) {

			return $item->is_site_field();

		});

		return $fields;

	} // end get_site_fields;

	/**
	 * Returns a list of all editor fields registered.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_all_editor_fields() {

		$all_editor_fields = array();

		$field_types = $this->get_instantiated_field_types();

		foreach ($field_types as $field_type) {

			$all_editor_fields = array_merge($all_editor_fields, $field_class->get_fields());

		} // end foreach;

		return $all_editor_fields;

	} // end get_all_editor_fields;

} // end class Signup_Fields_Manager;
