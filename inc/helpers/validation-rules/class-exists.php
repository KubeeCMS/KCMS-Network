<?php
/**
 * Adds a validation rules that allows us to check if a given parameter exists.
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
 * Adds a validation rules that allows us to check if a given parameter exists.
 *
 * @since 2.0.0
 */
class Exists extends Rule {

	/**
	 * Error message to be returned when this value has been used.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $message = ':attribute :value is not valid';

	/**
	 * Parameters that this rule accepts.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $fillableParams = array('model', 'column', 'except'); // phpcs:ignore

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
			'column'
		));

		$column = $this->parameter('column');
		$model  = $this->parameter('model');

		// do query
		return !!$model::get_by($column, $value);

	} // end check;

} // end class Exists;
