<?php
/**
 * Billing Address class
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Objects;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Billing Address class
 *
 * @since 2.0.0
 */
class Billing_Address {

	/**
	 * The Billing Address content.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Initializes the object.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Array of key => values billing address fields.
	 */
	public function __construct($data = array()) {

		$this->attributes($data);

	} // end __construct;

	/**
	 * Loops through allowed fields and loads them.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Array of key => values billing address fields.
	 * @return void
	 */
	public function attributes($data) {

		$allowed_attributes = array_keys(self::fields());

		foreach ($data as $key => $value) {

			if (in_array($key, $allowed_attributes, true)) {

				$this->attributes[$key] = $value;

			} // end if;

		} // end foreach;

	} // end attributes;

	/**
	 * Checks if this billing address has any content at all.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function exists() {

		return !empty(array_filter($this->attributes));

	} // end exists;

	/**
	 * Checks if a parameter exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The parameter to check.
	 * @return boolean
	 */
	public function __isset($name) {

		return wu_get_isset($this->attributes, $name, '');

	} // end __isset;

	/**
	 * Gets a billing address field.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The parameter to return.
	 * @return string
	 */
	public function __get($name) {

		$value = wu_get_isset($this->attributes, $name, '');

		return apply_filters("wu_billing_address_get_{$name}", $value, $this);

	} // end __get;

	/**
	 * Sets a billing address field.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Field name.
	 * @param string $value The field value.
	 */
	public function __set($name, $value) {

		$value = apply_filters("wu_billing_address_set_{$name}", $value, $this);

		$this->attributes[$name] = $value;

	} // end __set;

	/**
	 * Returns the validation rules for the billing address fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function validation_rules() {

		$fields = self::fields();

		$keys = array_keys(array_filter($fields, function($item) {

			return wu_get_isset($item, 'validation_rules');

		}));

		return array_combine($keys, array_column($fields, 'validation_rules'));

	} // end validation_rules;

	/**
	 * Validates the fields following the validation rules.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error
	 */
	public function validate() {

		$validator = new \WP_Ultimo\Helpers\Validator;

		$validator->validate($this->to_array(), $this->validation_rules());

		if ($validator->fails()) {

			return $validator->get_errors();

		} // end if;

		return true;

	} // end validate;

	/**
	 * Returns a key => value representation of the billing address.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $labels Wether or not to return labels as keys or the actual keys.
	 * @return array
	 */
	public function to_array($labels = false) {

		$address_array = array();

		$fields = self::fields();

		foreach ($fields as $field_key => $field) {

			if (!empty($this->{$field_key})) {

				$key = $labels ? $field['title'] : $field_key;

				$address_array[$key] = $this->{$field_key};

			} // end if;

		} // end foreach;

		return $address_array;

	}  // end to_array;

	/**
	 * Returns a string representation of the billing address.
	 *
	 * Example:
	 *
	 * Company Name
	 * Tax ID
	 * Address 1
	 * ...
	 *
	 * @since 2.0.0
	 *
	 * @param string $delimiter Delimiter to glue address pieces together.
	 * @return string
	 */
	public function to_string($delimiter = PHP_EOL) {

		return implode($delimiter, $this->to_array());

	} // end to_string;

	/**
	 * Returns the field array with values added.
	 *
	 * @since 2.0.0
	 * @param bool $zip_only If we only need zip and country.
	 * @return array
	 */
	public function get_fields($zip_only = false) {

		$fields = self::fields($zip_only);

		foreach ($fields as $field_key => &$field) {

			$field['value'] = $this->{$field_key};

		} // end foreach;

		return $fields;

	} // end get_fields;

	/**
	 * Billing Address field definitions.
	 *
	 * This is used to determine fields allowed on the billing address.
	 *
	 * @since 2.0.0
	 * @param bool $zip_only If we only need zip and country.
	 * @return array
	 */
	public static function fields($zip_only = false) {

		$fields = array();

		$fields['company_name'] = array(
			'type'                => 'text',
			'title'               => __('Company Name', 'wp-ultimo'),
			'default_placeholder' => __('E.g. Google (optional)', 'wp-ultimo'),
			'wrapper_classes'     => 'sm:wu-col-span-1',
		);

		$fields['billing_email'] = array(
			'type'                => 'text',
			'title'               => __('Billing Email', 'wp-ultimo'),
			'default_placeholder' => __('E.g. john@company.com', 'wp-ultimo'),
			'wrapper_classes'     => 'sm:wu-col-span-1',
			'required'            => true,
		);

		$fields['billing_address_line_1'] = array(
			'type'                => 'text',
			'title'               => __('Address Line 1', 'wp-ultimo'),
			'default_placeholder' => __('E.g. 555 1st Avenue', 'wp-ultimo'),
			'wrapper_classes'     => 'wu-col-span-2',
			'required'            => true,
		);

		$fields['billing_address_line_2'] = array(
			'type'                => 'text',
			'title'               => __('Address Line 2', 'wp-ultimo'),
			'default_placeholder' => __('E.g. Apartment 10a', 'wp-ultimo'),
			'wrapper_classes'     => 'wu-col-span-2',
		);

		$fields['billing_country'] = array(
			'type'                => 'select',
			'title'               => __('Country', 'wp-ultimo'),
			'default_placeholder' => __('E.g. US', 'wp-ultimo'),
			'wrapper_classes'     => 'sm:wu-col-span-1',
			'value'               => ' ',
			'options'             => 'wu_get_countries_as_options',
			'required'            => true,
		);

		$fields['billing_state'] = array(
			'type'                => 'text',
			'title'               => __('State / Province', 'wp-ultimo'),
			'default_placeholder' => __('E.g. NY', 'wp-ultimo'),
			'wrapper_classes'     => 'sm:wu-col-span-1',
		);

		$fields['billing_city'] = array(
			'type'                => 'text',
			'title'               => __('City / Town', 'wp-ultimo'),
			'default_placeholder' => __('E.g. New York City', 'wp-ultimo'),
			'wrapper_classes'     => 'sm:wu-col-span-1',
		);

		$fields['billing_zip_code'] = array(
			'type'                => 'text',
			'title'               => __('ZIP / Postal Code', 'wp-ultimo'),
			'default_placeholder' => __('E.g. 10009', 'wp-ultimo'),
			'wrapper_classes'     => 'sm:wu-col-span-1',
			'required'            => true,
		);

		$fields = wu_set_order_from_index($fields); // Adds missing order attributes

		if ($zip_only) {

			$fields = array(
				'billing_zip_code' => $fields['billing_zip_code'],
				'billing_country'  => $fields['billing_country'],
			);

		} // end if;

		/**
		 * Allow plugin developers to filter the billing address fields.
		 *
		 * @since 2.0.0
		 *
		 * @param array $fields Billing Address array.
	   * @param bool  $zip_only If we only need zip and country.
		 * @return array
		 */
		$fields = apply_filters('wu_billing_address_fields', $fields, $zip_only);

		uasort($fields, 'wu_sort_by_order');

		return $fields;

	} // end fields;

	/**
	 * Billing Address fields array for REST API.
	 *
	 * @since 2.0.0
	 * @param bool $zip_only If we only need zip and country.
	 * @return array
	 */
	public static function fields_for_rest($zip_only = false) {

		$fields_for_rest = array();

		foreach (self::fields($zip_only) as $field_key => $field) {

			$options = wu_get_isset($field, 'options', false);

			$enum = is_callable($options) ? call_user_func($options) : false;

			$fields_for_rest[$field_key] = array(
				'description' => wu_get_isset($field, 'title', false) . '. ' . wu_get_isset($field, 'default_placeholder', false),
				'type'        => 'string',
				'required'    => wu_get_isset($field, 'required', false),
			);

			if ($enum) {

				$fields_for_rest[$field_key]['enum'] = array_keys($enum);

			} // end if;

		} // end foreach;

		return $fields_for_rest;

	} // end fields_for_rest;

} // end class Billing_Address;
