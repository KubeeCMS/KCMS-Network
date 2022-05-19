<?php
/**
 * Adds a validation rule for products.
 *
 * @package WP_Ultimo
 * @subpackage Helpers/Validation_Rules
 * @since 2.0.4
 */

namespace WP_Ultimo\Helpers\Validation_Rules;

use WP_Ultimo\Dependencies\Rakit\Validation\Rule;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Validates products.
 *
 * @since 2.0.4
 */
class Products extends Rule {

	/**
	 * Error message to be returned when this value has been used.
	 *
	 * @since 2.0.4
	 * @var string
	 */
	protected $message = '';

	/**
	 * Parameters that this rule accepts.
	 *
	 * @since 2.0.4
	 * @var array
	 */
	protected $fillableParams = array(); // phpcs:ignore

	/**
	 * Performs the actual check.
	 *
	 * @since 2.0.4
	 *
	 * @param mixed $products Value being checked.
	 * @return boolean
	 */
	public function check($products) : bool { // phpcs:ignore

		$products = (array) $products;

		$product_objects = array_map('wu_get_product', $products);

		list($plan, $additional_products) = wu_segregate_products($product_objects);

		if ($plan) {

			return true;

		} // end if;

		$this->message = __('A plan is required.', 'wp-ultimo');

		return false;

	} // end check;

} // end class Products;
