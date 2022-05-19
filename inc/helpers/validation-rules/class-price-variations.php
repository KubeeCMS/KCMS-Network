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

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a validation rules that allows us to check if a given parameter is unique.
 *
 * @since 2.0.0
 */
class Price_Variations extends Rule {

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
	protected $fillableParams = array('duration', 'duration_unit'); // phpcs:ignore

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

    if (!is_array($value)) {

      return false;

    } // end if;

    foreach ($value as $price_variation) {

      /**
       * Validation Duration
       */
      $duration = wu_get_isset($price_variation, 'duration', false);

      if (!is_numeric($duration) || (int) $duration <= 0) {

        return false;

      } // end if;

      /**
       * Validation Unit
       */
      $unit = wu_get_isset($price_variation, 'duration_unit', false);

      $allowed_units = array(
        'day',
        'week',
        'month',
        'year',
      );

      if (!in_array($unit, $allowed_units, true)) {

        return false;

      } // end if;

      /**
       * Check if it is the same as the main duration
       */
      if ($this->parameter('duration') == $duration && $this->parameter('duration_unit') === $unit) {

        $this->message = 'This product cannot have a price variation for the same duration and duration unit values as the product itself.';

        return false;

      } // end if;

      /**
       * Validation Amount
       */
      $amount = wu_get_isset($price_variation, 'amount', false);

      if ($amount) {

        $amount = wu_to_float($amount);

      } // end if;

      if (!is_numeric($amount)) {

        return false;

      } // end if;

    } // end foreach;

    return true;

	} // end check;

} // end class Price_Variations;
