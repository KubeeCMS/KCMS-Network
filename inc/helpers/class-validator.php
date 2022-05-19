<?php
/**
 * Wraps the validation library being used by WP Ultimo.
 *
 * @package WP_Ultimo
 * @subpackage Helper
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers;

use \WP_Ultimo\Dependencies\Rakit\Validation\Validator as Validator_Helper;
use \WP_Ultimo\Helpers\Validation_Rules\Unique;
use \WP_Ultimo\Helpers\Validation_Rules\Unique_Site;
use \WP_Ultimo\Helpers\Validation_Rules\Exists;
use \WP_Ultimo\Helpers\Validation_Rules\Checkout_Steps;
use \WP_Ultimo\Helpers\Validation_Rules\Price_Variations;
use \WP_Ultimo\Helpers\Validation_Rules\Domain;
use \WP_Ultimo\Helpers\Validation_Rules\Site_Template;
use \WP_Ultimo\Helpers\Validation_Rules\Products;
use \WP_Ultimo\Helpers\Validation_Rules\Country;
use \WP_Ultimo\Helpers\Validation_Rules\State;
use \WP_Ultimo\Helpers\Validation_Rules\City;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Wraps the validation library being used by WP Ultimo.
 *
 * @since 2.0.0
 */
class Validator {

	/**
	 * Holds an instance of the validator object.
	 *
	 * @since 2.0.0
	 * @var Rakit\Validation\Validator
	 */
	protected $validator;

	/**
	 * Holds an instance of the validation being performed.
	 *
	 * @since 2.0.0
	 * @var Rakit\Validation\Validation
	 */
	protected $validation;

	/**
	 * Holds the errors returned from validation.
	 *
	 * @since 2.0.0
	 * @var \WP_Error
	 */
	protected $errors;

	/**
	 * Sets up the validation library and makes the error messages translatable.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$validation_error_messages = apply_filters('wu_validator_error_messages', array(
			'required' => __('The :attribute field is required', 'wp-ultimo'),
		), $this);

		$this->validator = new Validator_Helper($validation_error_messages);

		$this->validator->setTranslations(array(
			'and' => __('and', 'wp-ultimo'),
			'or'  => __('or', 'wp-ultimo'),
		));

		$this->validator->addValidator('unique', new Unique);
		$this->validator->addValidator('unique_site', new Unique_Site);
		$this->validator->addValidator('exists', new Exists);
		$this->validator->addValidator('checkout_steps', new Checkout_Steps);
		$this->validator->addValidator('price_variations', new Price_Variations);
		$this->validator->addValidator('domain', new Domain);
		$this->validator->addValidator('site_template', new Site_Template);
		$this->validator->addValidator('products', new Products);
		$this->validator->addValidator('country', new Country);
		$this->validator->addValidator('state', new State);
		$this->validator->addValidator('city', new City);

	} // end __construct;

	/**
	 * Validates the data passed according to the rules passed.
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation#available-rules
	 *
	 * @param array $data Data to be validated.
	 * @param array $rules List of rules to validate against.
	 * @return \WP_Ultimo\Helpers\Validator
	 */
	public function validate($data, $rules = array()) {

		$this->errors = new \WP_Error;

		$this->validation = $this->validator->make($data, $rules);

		$this->validation->validate();

		return $this;

	} // end validate;

	/**
	 * Returns true when the validation fails.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function fails() {

		return $this->validation->fails();

	} // end fails;

	/**
	 * Returns a WP_Error object containing all validation errors.
	 *
	 * @since 2.0.0
	 * @return WP_Error
	 */
	public function get_errors() {

		$errors = $this->validation->errors()->toArray();

		$this->cast_to_wp_error($errors);

		return $this->errors;

	} // end get_errors;

	/**
	 * Converts the native error structure to a WP_Error object.
	 *
	 * @since 2.0.0
	 *
	 * @param array $errors Array containing the errors returned.
	 * @return void
	 */
	protected function cast_to_wp_error($errors) {

		foreach ($errors as $key => $error_messages) {

			foreach ($error_messages as $error_message) {

				$this->errors->add($key, $error_message);

			} // end foreach;

		} // end foreach;

	} // end cast_to_wp_error;

	/**
	 * Get holds an instance of the validation being performed.
	 *
	 * @since 2.0.0
	 * @return Rakit\Validation\Validation
	 */
	public function get_validation() {

		return $this->validation;

	} // end get_validation;

} // end class Validator;
