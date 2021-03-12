<?php
/**
 * The Discount_Code model for the Discount Codes.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Base_Model;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Discount_Code model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Discount_Code extends Base_Model {

	use \WP_Ultimo\Traits\WP_Ultimo_Coupon_Deprecated;

	/**
	 * Name of the discount code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name;

	/**
	 * Code to redeem the discount code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $code;

	/**
	 * Text describing the coupon code. Useful for identifying it.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $description;

	/**
	 * Number of times this discount was applied.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $uses = 0;

	/**
	 * The number of times this discount can be used before becoming inactive.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $max_uses;

	/**
	 * If we should apply the discount to renewals as well.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $apply_to_renewals = false;

	/**
	 * Type of the discount. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type = 'percentage';

	/**
	 * Amount discounted in cents.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $value = 0;

	/**
	 * Type of the discount for the setup fee value. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $setup_fee_type = 'percentage';

	/**
	 * Amount discounted fpr setup fees in cents.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $setup_fee_value = 0;

	/**
	 * If this coupon code is active or not.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $active = 1;

	/**
	 * Start date for the coupon code to be considered valid.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_start;

	/**
	 * Expiration date for the coupon code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_expiration;

	/**
	 * Date when this discount code was created.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Discount_Codes\\Discount_Code_Query';

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		return array(
			'name'     => 'required|min:2',
			'code'     => 'required|min:4|max:20',
			'uses'     => 'integer|default:0',
			'max_uses' => 'integer|min:0|default:0',
			'active'   => 'default:1',
		);

	} // end validation_rules;

	/**
	 * Get name of the discount code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_name() {

		return $this->name;

	} // end get_name;

	/**
	 * Set name of the discount code.
	 *
	 * @since 2.0.0
	 * @param string $name Name of the discount code.
	 * @return void
	 */
	public function set_name($name) {

		$this->name = $name;

	} // end set_name;

	/**
	 * Get code to redeem the discount code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_code() {

		return $this->code;

	} // end get_code;

	/**
	 * Set code to redeem the discount code.
	 *
	 * @since 2.0.0
	 * @param string $code Code to redeem the discount code.
	 * @return void
	 */
	public function set_code($code) {

		$this->code = $code;

	} // end set_code;

	/**
	 * Get text describing the coupon code. Useful for identifying it.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return $this->description;

	} // end get_description;

	/**
	 * Set text describing the coupon code. Useful for identifying it.
	 *
	 * @since 2.0.0
	 * @param string $description Text describing the coupon code. Useful for identifying it.
	 * @return void
	 */
	public function set_description($description) {

		$this->description = $description;

	} // end set_description;

	/**
	 * Get number of times this discount was applied.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_uses() {

		return $this->uses;

	} // end get_uses;

	/**
	 * Set number of times this discount was applied.
	 *
	 * @since 2.0.0
	 * @param int $uses Number of times this discount was applied.
	 * @return void
	 */
	public function set_uses($uses) {

		$this->uses = $uses;

	} // end set_uses;

	/**
	 * Get the number of times this discount can be used before becoming inactive.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_max_uses() {

		return (int) $this->max_uses;

	} // end get_max_uses;

	/**
	 * Set the number of times this discount can be used before becoming inactive.
	 *
	 * @since 2.0.0
	 * @param int $max_uses The number of times this discount can be used before becoming inactive.
	 * @return void
	 */
	public function set_max_uses($max_uses) {

		$this->max_uses = $max_uses;

	} // end set_max_uses;

	/**
	 * Checks if the given discount code has a number of max uses.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_max_uses() {

		return $this->get_max_uses() > 0;

	} // end has_max_uses;

	/**
	 * Get if we should apply this coupon to renewals as well.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_apply_to_renewals() {

		return (bool) $this->apply_to_renewals;

	} // end get_apply_to_renewals;

	/**
	 * Set if we should apply this coupon to renewals as well.
	 *
	 * @since 2.0.0
	 * @param bool $apply_to_renewals Wether or not we should apply the discount to renewals.
	 * @return void
	 */
	public function set_apply_to_renewals($apply_to_renewals) {

		$this->apply_to_renewals = (bool) $apply_to_renewals;

	} // end set_apply_to_renewals;

	/**
	 * Get type of the discount. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return $this->type;

	} // end get_type;

	/**
	 * Set type of the discount. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @param string $type Type of the discount. Can be a percentage or absolute.
	 * @return void
	 */
	public function set_type($type) {

		$this->type = $type;

	} // end set_type;

	/**
	 * Get amount discounted in cents.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_value() {

		return $this->value;

	} // end get_value;

	/**
	 * Set amount discounted in cents.
	 *
	 * @since 2.0.0
	 * @param int $value Amount discounted in cents.
	 * @return void
	 */
	public function set_value($value) {

		$this->value = $value;

	} // end set_value;

	/**
	 * Get type of the discount for the setup fee value. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_setup_fee_type() {

		return $this->setup_fee_type;

	} // end get_setup_fee_type;

	/**
	 * Set type of the discount for the setup fee value. Can be a percentage or absolute.
	 *
	 * @since 2.0.0
	 * @param string $setup_fee_type Type of the discount for the setup fee value. Can be a percentage or absolute.
	 * @return void
	 */
	public function set_setup_fee_type($setup_fee_type) {

		$this->setup_fee_type = $setup_fee_type;

	} // end set_setup_fee_type;

	/**
	 * Get amount discounted fpr setup fees in cents.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_setup_fee_value() {

		return $this->setup_fee_value;

	} // end get_setup_fee_value;

	/**
	 * Set amount discounted fpr setup fees in cents.
	 *
	 * @since 2.0.0
	 * @param int $setup_fee_value Amount discounted fpr setup fees in cents.
	 * @return void
	 */
	public function set_setup_fee_value($setup_fee_value) {

		$this->setup_fee_value = $setup_fee_value;

	} // end set_setup_fee_value;

	/**
	 * Get if this coupon code is active or not.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_active() {

		return (bool) $this->active;

	} // end is_active;

	/**
	 * Checks if a given coupon code is valid and can be applied.
	 *
	 * @since 2.0.0
	 * @param \WP_Ultimo\Models\Product $product Product to check against.
	 * @return boolean
	 */
	public function is_valid($product = false) {

		return $this->is_active();

	} // end is_valid;

	/**
	 * Checks if this discount applies just for the first payment.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_one_time() {

		return (bool) $this->get_apply_to_renewals() === 1;

	} // end is_one_time;

	/**
	 * Set if this coupon code is active or not.
	 *
	 * @since 2.0.0
	 * @param bool $active If this coupon code is active or not.
	 * @return void
	 */
	public function set_active($active) {

		$this->active = (bool) $active;

	} // end set_active;

	/**
	 * Get start date for the coupon code to be considered valid.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_start() {

		return $this->date_start;

	} // end get_date_start;

	/**
	 * Set start date for the coupon code to be considered valid.
	 *
	 * @since 2.0.0
	 * @param string $date_start Start date for the coupon code to be considered valid.
	 * @return void
	 */
	public function set_date_start($date_start) {

		$this->date_start = $date_start;

	} // end set_date_start;

	/**
	 * Get expiration date for the coupon code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_expiration() {

		return $this->date_expiration;

	} // end get_date_expiration;

	/**
	 * Set expiration date for the coupon code.
	 *
	 * @since 2.0.0
	 * @param string $date_expiration Expiration date for the coupon code.
	 * @return void
	 */
	public function set_date_expiration($date_expiration) {

		$this->date_expiration = $date_expiration;

	} // end set_date_expiration;

	/**
	 * Get date when this discount code was created.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_created() {

		return $this->date_created;

	} // end get_date_created;

	/**
	 * Set date when this discount code was created.
	 *
	 * @since 2.0.0
	 * @param string $date_created Date when this discount code was created.
	 * @return void
	 */
	public function set_date_created($date_created) {

		$this->date_created = $date_created;

	} // end set_date_created;

	/**
	 * Returns a text describing the discount code values.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_discount_description() {

		$description = array();

		if ($this->get_value() > 0) {

			$value = wu_format_currency($this->get_value());

			if ($this->get_type() === 'percentage') {

				$value = $this->get_value() . '%';

			} // end if;

			$description[] = sprintf(
				// translators: placeholder is the value off. Can be wither $X.XX or X%
				__('%1$s OFF on Subscriptions', 'wp-ultimo'),
				$value
			);

		} // end if;

		if ($this->get_setup_fee_value() > 0) {

			$setup_fee_value = wu_format_currency($this->get_setup_fee_value());

			if ($this->get_setup_fee_type() === 'percentage') {

				$value = $this->get_setup_fee_value() . '%';

			} // end if;

			$description[] = sprintf(
				// translators: placeholder is the value off. Can be wither $X.XX or X%
				__('%1$s OFF on Setup Fees', 'wp-ultimo'),
				$value
			);

		} // end if;

		return implode(' ' . __('and', 'wp-ultimo') . ' ', $description);

	} // end get_discount_description;

	/**
	 * Transform the object into an assoc array.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_array() {

		$array = parent::to_array();

		$array['discount_description'] = $this->get_discount_description();

		return $array;

	} // end to_array;

	/**
	 * Save (create or update) the model on the database.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function save() {

		$results = parent::save();

		if (!is_wp_error($results) && has_action('wp_ultimo_coupon_after_save')) {

			if (did_action('wp_ultimo_coupon_after_save')) {

				return $results;

			} // end if;

			$compat_coupon = $this;

			do_action_deprecated('wp_ultimo_coupon_after_save', array($compat_coupon), '2.0.0', 'wu_discount_code_post_save');

		} // end if;

		return $results;

	} // end save;

} // end class Discount_Code;
