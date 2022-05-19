<?php
/**
 * Adds a validation rules that allows us to check if a given parameter is unique.
 *
 * @package WP_Ultimo
 * @subpackage Helpers/Validation_Rules
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers\Validation_Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a validation rules that allows us to check if a given parameter is unique.
 *
 * @since 2.0.0
 */
class Unique extends Rule {

	/**
	 * Error message to be returned when this value has been used.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $message = ':attribute :value has been used';

	/**
	 * Parameters that this rule accepts.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $fillableParams = array('model', 'column', 'self_id'); // phpcs:ignore

	/**
	 * Performs the actual check.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Value being checked.
	 * @return boolean
	 */
	public function check($value) : bool { // phpcs:ignore

		$this->requireParameters(array(
			'model',
			'column',
		));

		$column  = $this->parameter('column');
		$model   = $this->parameter('model');
		$self_id = $this->parameter('self_id');

		// do query
		$existing = $model::get_by($column, $value);

		/*
		 * Customize the error message for the customer.
		 */
		if ('\WP_Ultimo\Models\Customer' === $model) {

			$this->message = __('A customer with the same email address or username already exists.', 'wp-ultimo');

		} // end if;

		return $existing ? $existing->get_id() === absint($self_id) : true; // phpcs:ignore

	} // end check;

} // end class Unique;
