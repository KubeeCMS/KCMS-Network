<?php // phpcs:disable
/**
 * Adds a validation rules that allows us to check if a given parameter is unique.
 *
 * @package WP_Ultimo
 * @subpackage Helpers/Validation_Rules
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers\Validation_Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;
use WP_Ultimo\Managers\Signup_Fields_Manager;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a validation rules that allows us to check if a given parameter is unique.
 *
 * @since 2.0.0
 */
class Checkout_Steps extends Rule {

	/**
	 * Error message to be returned when this value has been used.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $message = ':attribute is wrongly setup.';

	/**
	 * Parameters that this rule accepts.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $fillableParams = array(); // phpcs:ignore

	/**
	 * Performs the actual check.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Value being checked.
	 * @return boolean
	 */
  public function check($value) : bool {

    if (is_string($value)) {

      $value = maybe_unserialize($value);

    } // end if;

    $required_fields = Signup_Fields_Manager::get_instance()->get_required_fields();

    $required_fields_list = array_keys($required_fields);

    if (!$value || is_string($value)) {

      return true;

    } // end if;

		$fields = array_column($value, 'fields');

		if (empty($fields)) {

			return true;

		} // end if;

    $all_fields = call_user_func_array('array_merge', $fields);
    
    $all_fields_list = array_column($all_fields, 'type');

		/**
		 * First, we validated that all of our required fields are present.
		 */
    $all_present = true;

    foreach ($required_fields_list as $field_slug) {

      if (!in_array($field_slug, $all_fields_list, true)) {

        $this->message = sprintf(__('The %s field must be present in at least one of the checkout form steps.', 'wp-ultimo'), wu_slug_to_name($field_slug));

        return false;

      } // end if;

    } // end if;

    /**
     * Allow developers to bypass the check if a field is auto-submittable.
     * 
     * @since 2.0.0
     * @param array $submittable_field_types The list of field types.
     * @return array
     */
    $submittable_field_types = apply_filters(
      'wu_checkout_step_validation_submittable_field_types', 
      array(
        'submit_button',
        'pricing_table',
        'template_selection',
      )
    );

    /**
     * Second, we must validate if every step has a submit button.
     */
    foreach ($value as $step) {

      $found_submittable_field_types = \WP_Ultimo\Dependencies\Arrch\Arrch::find($step['fields'], array(
        'where'    => array(
          array('type', $submittable_field_types),
        ),
      ));

      if (empty($found_submittable_field_types)) {

        $this->message = sprintf(__('The %s step is missing a submit field', 'wp-ultimo'), $step['name']);

        return false;

      } // end if;

    } // end foreach;

    /*
     * @todo: Plan, product selection fields must come before the order summary and payment fields.
     */

    return true;

	} // end check;

} // end class Checkout_Steps;
