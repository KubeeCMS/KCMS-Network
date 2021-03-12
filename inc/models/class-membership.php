<?php
/**
 * The Membership model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use \WP_Ultimo\Models\Base_Model;
use \WP_Ultimo\Models\Customer;
use \WP_Ultimo\Models\Product;
use \WP_Ultimo\Models\Site;
use \WP_Ultimo\Database\Memberships\Membership_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Membership model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Membership extends Base_Model {

	use Traits\Limitable, Traits\Billable;

	/**
	 * ID of the customer attached to this membership.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $customer_id;

	/**
	 * User ID attached to this membership.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $user_id;

	/**
	 * Plan associated with the membership.
	 *
	 * @since 2.0.0
	 * @var mixed
	 */
	protected $plan_id;

	/**
	 * Additional products. Services and Packages.
	 *
	 * @since 2.0.0
	 * @var mixed
	 */
	protected $addon_products = array();

	/**
	 * Currency for this membership. 3-letter currency code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $currency;

	/**
	 * Initial amount for the subscription. Includes the setup fee.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $initial_amount = 0;

	/**
	 * Is this product recurring?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $recurring = 1;

	/**
	 * Should auto-renew?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $auto_renew = 1;

	/**
	 * Time interval between charges.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $duration = 1;

	/**
	 * Time interval unit between charges.
	 *
	 * - day
	 * - week
	 * - month
	 * - year
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $duration_unit = 'month';

	/**
	 * Amount to charge recurrently.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $amount = 0;

	/**
	 * Date of creation of this membership.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * Date of activation of this membership.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_activated;

	/**
	 * Date of the end of the trial period.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_trial_end;

	/**
	 * Date of the next renewal.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_renewed;

	/**
	 * Date of the cancellation.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_cancellation;

	/**
	 * Date of expiration.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_expiration;

	/**
	 * Change of the payment completion for the plan value.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_payment_plan_completed;

	/**
	 * Amount of times this membership got billed.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $times_billed;

	/**
	 * Maximum times we should charge this membership.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $billing_cycles;

	/**
	 * Status of the membership.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $status;

	/**
	 * ID of the customer on the payment gateway database.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $gateway_customer_id;

	/**
	 * ID of the subscription on the payment gateway database.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $gateway_subscription_id;

	/**
	 * ID of the gateway being used on this subscription.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $gateway;

	/**
	 * Signup method used to create this membership.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $signup_method;

	/**
	 * Not sure what this does.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $subscription_key;

	/**
	 * Plan that this membership upgraded from.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $upgraded_from;

	/**
	 * Date this membership was last modified.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_modified;

	/**
	 * If this membership is disabled.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $disabled;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Memberships\\Membership_Query';

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
			'amount'         => 'numeric|default:0',
			'duration'       => 'default:1',
			'billing_cycles' => 'default:0',
			'active'         => 'default:1',
			'plan_id'        => 'required|integer|exists:\WP_Ultimo\Models\Product,id',
			'customer_id'    => 'required|integer|exists:\WP_Ultimo\Models\Customer,id',
		);

	} // end validation_rules;

	/**
	 * Gets the customer object associated with this membership.
	 *
	 * @todo Implement this.
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Customer;
	 */
	public function get_customer() {

		return wu_get_customer($this->get_customer_id());

	} // end get_customer;

	/**
	 * Get the value of customer_id.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_customer_id() {

		return $this->customer_id;

	} // end get_customer_id;

	/**
	 * Set the value of customer_id.
	 *
	 * @since 2.0.0
	 * @param mixed $customer_id ID of the customer attached to this membership.
	 * @return void
	 */
	public function set_customer_id($customer_id) {

		$this->customer_id = $customer_id;

	} // end set_customer_id;

	/**
	 * Checks if a given customer should have access to this site options.
	 *
	 * @since 2.0.0
	 *
	 * @param int $customer_id The customer id to check.
	 * @return boolean
	 */
	public function is_customer_allowed($customer_id = false) {

		if (current_user_can('manage_network')) {

			return true;

		} // end if;

		if (!$customer_id) {

			$customer = WP_Ultimo()->currents->get_customer();

			$customer_id = $customer ? $customer->get_id() : 0;

		} // end if;

		$allowed = abs($customer_id) === abs($this->get_customer_id());

		return apply_filters('wu_membership_is_customer_allowed', $allowed, $customer_id, $this);

	} // end is_customer_allowed;

	/**
	 * Get the value of user_id.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_user_id() {

		return $this->user_id;

	} // end get_user_id;

	/**
	 * Set the value of user_id.
	 *
	 * @since 2.0.0
	 * @param mixed $user_id User ID attached to this membership.
	 * @return void
	 */
	public function set_user_id($user_id) {

		$this->user_id = $user_id;

	} // end set_user_id;

	/**
	 * Get the value of plan_id.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_plan_id() {

		return $this->plan_id;

	} // end get_plan_id;

	/**
	 * Returns the plan that created this membership.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Product
	 */
	public function get_plan() {

		return wu_get_product($this->get_plan_id());

	} // end get_plan;

	/**
	 * Set plan associated with the membership.
	 *
	 * @since 2.0.0
	 * @param int $plan_id Plan associated with the membership.
	 * @return void
	 */
	public function set_plan_id($plan_id) {

		$this->plan_id = $plan_id;

	} // end set_plan_id;

	/**
	 * Checks if this membership has a plan.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_plan() {

		return !empty($this->get_plan());

	} // end has_plan;

	/**
	 * Get additional product objects.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Product[] A list of the addon projects.
	 */
	public function get_addons() {

		$addons = array_map('wu_get_product', $this->get_addon_ids());

		return array_filter($addons);

	} // end get_addons;

	/**
	 * Checks if the given membership has addon products.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_addons() {

		return !empty($this->get_addons());

	} // end has_addons;

	/**
	 * Gets a list of product ids for addons.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_addon_ids() {

		return array_map('abs', array_keys((array) $this->addon_products));

	} // end get_addon_ids;


	/**
	 * Adds an an addon product from this membership.
	 *
	 * @since 2.0.0
	 *
	 * @param int     $product_id The product id.
	 * @param integer $quantity The quantity.
	 * @return void
	 */
	public function add_product($product_id, $quantity = 1) {

		$has_product = wu_get_isset($this->addon_products, $product_id);

		if ($has_product && $this->addon_products[$product_id] >= 0) {

			$this->addon_products[$product_id] = $this->addon_products[$product_id] + $quantity;

		} else {

			$this->addon_products[$product_id] = $quantity;

		} // end if;

		if ($this->addon_products[$product_id] <= 0) {

			unset($this->addon_products[$product_id]);

		} // end if;

	} // end add_product;

	/**
	 * Removes a product from the membership.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $product_id The product id.
	 * @param integer $quantity The quantity to remove.
	 * @return void
	 */
	public function remove_product($product_id, $quantity = 1) {

		$has_product = wu_get_isset($this->addon_products, $product_id);

		if ($has_product && $this->addon_products[$product_id] >= 0) {

			$this->addon_products[$product_id] = $this->addon_products[$product_id] - $quantity;

		} // end if;

		if ($this->addon_products[$product_id] <= 0) {

			unset($this->addon_products[$product_id]);

		} // end if;

	} // end remove_product;

	/**
	 * Get additional products. Services and Packages.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_addon_products() {

		$products = array();

		foreach ($this->addon_products as $product_id => $quantity) {

			$product = wu_get_product($product_id);

			if (!$product) {

				continue;

			} // end if;

			$products[] = array(
				'quantity' => $quantity,
				'product'  => $product,
			);

		} // end foreach;

		return $products;

	} // end get_addon_products;

	/**
	 * Returns a list with all products, including the plan.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_all_products() {

		$products = array(
			array(
				'quantity' => 1,
				'product'  => $this->get_plan(),
			),
		);

		return array_merge($products, $this->get_addon_products());

	} // end get_all_products;

	/**
	 * Set additional products. Services and Packages.
	 *
	 * @since 2.0.0
	 * @param mixed $addon_products Additional products. Services and Packages.
	 * @return void
	 */
	public function set_addon_products($addon_products) {

		$this->addon_products = maybe_unserialize($addon_products);

	} // end set_addon_products;

	/**
	 * Returns the amount recurring in a human-friendly way.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_recurring_description() {

		$description = sprintf(
			// translators: %1$s the duration, and %2$s the duration unit (day, week, month, etc)
			_n('every %2$s', 'every %1$s %2$ss', $this->get_duration(), 'wp-ultimo'), // phpcs:ignore
			$this->get_duration(),
			$this->get_duration_unit()
		);

		return $description;

	} // end get_recurring_description;

	/**
	 * Returns the times billed in a human-friendly way.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_times_billed_description() {

		// translators: times billed / subscription duration in cycles. e.g. 1/12 cycles
		$description = __('%1$s / %2$s cycles', 'wp-ultimo');

		if ($this->is_forever_recurring()) {

			// translators: the place holder is the number of times the membership was billed.
			$description = __('%1$s / until canceled', 'wp-ultimo');

		} // end if;

		return sprintf($description, $this->get_times_billed(), $this->get_billing_cycles());

	} // end get_times_billed_description;

	/**
	 * Returns the membership price structure in a way human can understand it.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_price_description() {

		$pricing = array();

		if ($this->is_recurring()) {

			$duration = $this->get_duration();

			$message = sprintf(
				// translators: %1$s is the formatted price, %2$s the duration, and %3$s the duration unit (day, week, month, etc)
				_n('%1$s every %3$s', '%1$s every %2$s %3$ss', $duration, 'wp-ultimo'), // phpcs:ignore
				wu_format_currency($this->get_amount(), $this->get_currency()),
				$duration,
				$this->get_duration_unit()
			);

			$pricing['subscription'] = $message;

			if (!$this->is_forever_recurring()) {

				$billing_cycles_message = sprintf(
					// translators: %s is the number of billing cycles.
					_n('for %s cycle', 'for %s cycles', $this->get_billing_cycles(), 'wp-ultimo'),
					$this->get_billing_cycles()
				);

				$pricing['subscription'] .= ' ' . $billing_cycles_message;

			} // end if;

		} else {

			$pricing['subscription'] = sprintf(
				// translators: %1$s is the formatted price of the product
				__('%1$s one time payment', 'wp-ultimo'),
				wu_format_currency($this->get_amount(), $this->get_currency())
			);

		} // end if;

		if ($this->is_free()) {

			$pricing['subscription'] = __('Free!', 'wp-ultimo');

		} // end if;

		return implode(' + ', $pricing);

	} // end get_price_description;

	/**
	 * Get the value of currency.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_currency() {

		// return $this->currency; For now, multi-currency is not yet supported.
		return wu_get_setting('currency_symbol', 'USD');

	} // end get_currency;

	/**
	 * Set the value of currency.
	 *
	 * @since 2.0.0
	 * @param mixed $currency Currency for this membership. 3-letter currency code.
	 * @return void
	 */
	public function set_currency($currency) {

		$this->currency = $currency;

	} // end set_currency;

	/**
	 * Get time interval between charges.
	 *
	 * @return int
	 */
	public function get_duration() {

		return abs($this->duration);

	} // end get_duration;

	/**
	 * Set time interval between charges.
	 *
	 * @param int $duration Time interval between charges.
	 */
	public function set_duration($duration) {

		$this->duration = abs($duration);

	} // end set_duration;

	/**
	 * Get time interval unit between charges.
	 *
	 * @return string
	 */
	public function get_duration_unit() {

		return $this->duration_unit;

	} // end get_duration_unit;

	/**
	 * Set time interval unit between charges.
	 *
	 * @param string $duration_unit Time interval unit between charges.
	 */
	public function set_duration_unit($duration_unit) {

		$this->duration_unit = $duration_unit;

	} // end set_duration_unit;

	/**
	 * Get the product amount.
	 *
	 * @return int
	 */
	public function get_amount() {

		return $this->amount;

	} // end get_amount;

	/**
	 * Get normalized amount. This is used to calculate MRR>
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_normalized_amount() {

		$amount = $this->get_amount();

		if ($this->is_recurring()) {

			return $amount;

		} // end if;

		$duration = $this->get_duration();

		$normalized_duration_unit = wu_convert_duration_unit_to_month($this->get_duration_unit());

		return $amount / $duration * $normalized_duration_unit;

	} // end get_normalized_amount;

	/**
	 * Set the product amount.
	 *
	 * @param int $amount The product amount.
	 */
	public function set_amount($amount) {

		$this->amount = wu_to_float($amount);

	} // end set_amount;

	/**
	 * Get the product setup fee.
	 *
	 * @return int
	 */
	public function get_initial_amount() {

		return $this->initial_amount;

	} // end get_initial_amount;

	/**
	 * Set the product setup fee.
	 *
	 * @param int $initial_amount The product setup fee.
	 */
	public function set_initial_amount($initial_amount) {

		$this->initial_amount = wu_to_float($initial_amount);

	} // end set_initial_amount;

	/**
	 * Get the value of date_created.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_date_created() {

		return $this->date_created;

	} // end get_date_created;

	/**
	 * Set the value of date_created.
	 *
	 * @since 2.0.0
	 * @param mixed $date_created Date of creation of this membership.
	 * @return void
	 */
	public function set_date_created($date_created) {

		$this->date_created = $date_created;

	} // end set_date_created;

	/**
	 * Get the value of date_activated.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_date_activated() {

		return $this->date_activated;

	} // end get_date_activated;

	/**
	 * Set the value of date_activated.
	 *
	 * @since 2.0.0
	 * @param mixed $date_activated Date of activation of this membership.
	 * @return void
	 */
	public function set_date_activated($date_activated) {

		$this->date_activated = $date_activated;

	} // end set_date_activated;

	/**
	 * Get the value of date_trial_end.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_date_trial_end() {

		return $this->date_trial_end;

	} // end get_date_trial_end;

	/**
	 * Set the value of date_trial_end.
	 *
	 * @since 2.0.0
	 * @param mixed $date_trial_end Date of the end of the trial period.
	 * @return void
	 */
	public function set_date_trial_end($date_trial_end) {

		$this->date_trial_end = $date_trial_end;

	} // end set_date_trial_end;

	/**
	 * Get the value of date_renewed.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_date_renewed() {

		return $this->date_renewed;

	} // end get_date_renewed;

	/**
	 * Set the value of date_renewed.
	 *
	 * @since 2.0.0
	 * @param mixed $date_renewed Date of the next renewal.
	 * @return void
	 */
	public function set_date_renewed($date_renewed) {

		$this->date_renewed = $date_renewed;

	} // end set_date_renewed;

	/**
	 * Get the value of date_cancellation.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_date_cancellation() {

		return $this->date_cancellation;

	} // end get_date_cancellation;

	/**
	 * Set the value of date_cancellation.
	 *
	 * @since 2.0.0
	 * @param mixed $date_cancellation Date of the cancellation.
	 * @return void
	 */
	public function set_date_cancellation($date_cancellation) {

		$this->date_cancellation = $date_cancellation;

	} // end set_date_cancellation;

	/**
	 * Get the value of date_expiration.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_date_expiration() {

		return $this->date_expiration;

	} // end get_date_expiration;

	/**
	 * Set the value of date_expiration.
	 *
	 * @since 2.0.0
	 * @param mixed $date_expiration Date of expiration.
	 * @return void
	 */
	public function set_date_expiration($date_expiration) {

		$this->date_expiration = $date_expiration;

	} // end set_date_expiration;

	/**
	 * Get the expiration date as a timestamp.
	 *
	 * @access public
	 * @since  3.0
	 * @return int|false
	 */
	public function get_expiration_time() {

		$expiration = $this->get_date_expiration();

		$timestamp = ($expiration && 'none' !== $expiration) ? strtotime($expiration, current_time('timestamp')) : false;

		/**
		 * Filters the expiration time.
		 *
		 * @param int|false      $timestamp     Expiration timestamp.
		 * @param int            $membership_id ID of the membership.
		 * @param \WP_Ultimo\Models\Membership $this          Membership object.
		 *
		 * @since 2.0
		 */
		$timestamp = apply_filters('wu_membership_get_expiration_time', $timestamp, $this->get_id(), $this);

		return $timestamp;

	} // end get_expiration_time;

	/**
	 * Calculate a new expiration date.
	 *
	 * @param bool $from_today Whether to calculate from today (`true`), or extend the existing expiration date
	 *                         (`false`).
	 * @param bool $trial      Whether or not this is for a free trial.
	 *
	 * @access public
	 * @since  3.0
	 * @return String Date in Y-m-d H:i:s format or "none" if is a lifetime membership.
	 */
	public function calculate_expiration($from_today = false, $trial = false) {

		// Get the member's current expiration date
		$expiration = $this->get_date_expiration();

		// Determine what date to use as the start for the new expiration calculation
		if (!$from_today && $expiration > current_time('timestamp') && $this->is_active()) {

			$base_timestamp = $expiration;

		} else {

			$base_timestamp = current_time('timestamp');

		} // end if;

		// @todo use membership level class
		if ($this->get_duration() > 0) {

			if ( false && $this->trial_duration > 0 && $trial ) {

				$expire_timestamp = strtotime('+' . $this->trial_duration . ' ' . $this->trial_duration_unit . ' 23:59:59', $base_timestamp);

			} else {

				$expire_timestamp = strtotime('+' . $this->get_duration() . ' ' . $this->get_duration_unit() . ' 23:59:59', $base_timestamp);

			} // end if;

			$extension_days = array('29', '30', '31');

			if (in_array(gmdate('j', $expire_timestamp), $extension_days) && 'month' === $this->get_duration_unit()) {
				/*
				 * Here we extend the expiration date by 1-3 days in order to account for "walking" payment dates in PayPal.
				 *
				 * See https://github.com/pippinsplugins/restrict-content-pro/issues/239
				 */

				$month = gmdate('n', $expire_timestamp);

				if ($month < 12) {

					$month += 1;

					$year = gmdate('Y', $expire_timestamp);

				} else {

					$month = 1;

					$year = gmdate('Y', $expire_timestamp ) + 1;

				} // end if;

				$expire_timestamp = mktime(0, 0, 0, $month, 1, $year);

			} // end if;

			$expiration = gmdate('Y-m-d 23:59:59', $expire_timestamp);

		} else {

			$expiration = 'none';

		} // end if;

		/**
		 * Filters the calculated expiration date.
		 *
		 * @param string         $expiration    Calculated expiration date in MySQL format.
		 * @param int            $membership_id ID of the membership.
		 * @param \WP_Ultimo\Models\Membership $this          Membership object.
		 *
		 * @since 2.0
		 */
		$expiration = apply_filters( 'wu_membership_calculated_date_expiration', $expiration, $this->get_id(), $this );

		return $expiration;

	} // end calculate_expiration;

	/**
	 * Get the value of date_payment_plan_completed.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_date_payment_plan_completed() {

		return $this->date_payment_plan_completed;

	} // end get_date_payment_plan_completed;

	/**
	 * Set the value of date_payment_plan_completed.
	 *
	 * @since 2.0.0
	 * @param mixed $date_payment_plan_completed Change of the payment completion for the plan value.
	 * @return void
	 */
	public function set_date_payment_plan_completed($date_payment_plan_completed) {

		$this->date_payment_plan_completed = $date_payment_plan_completed;

	} // end set_date_payment_plan_completed;

	/**
	 * Get the value of auto_renew.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_auto_renew() {

		return $this->auto_renew;

	} // end get_auto_renew;

	/**
	 * Set the value of auto_renew.
	 *
	 * @since 2.0.0
	 * @param mixed $auto_renew If this membership should auto-renewal.
	 * @return void
	 */
	public function set_auto_renew($auto_renew) {

		$this->auto_renew = $auto_renew;

	} // end set_auto_renew;

	/**
	 * Get the value of times_billed.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_times_billed() {

		return (int) $this->times_billed;

	} // end get_times_billed;

	/**
	 * Set the value of times_billed.
	 *
	 * @since 2.0.0
	 * @param mixed $times_billed Amount of times this membership got billed.
	 * @return void
	 */
	public function set_times_billed($times_billed) {

		$this->times_billed = $times_billed;

	} // end set_times_billed;

	/**
	 * Get the value of billing_cycles.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_billing_cycles() {

		return $this->billing_cycles;

	} // end get_billing_cycles;

	/**
	 * Checks if this product recurs forever.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_forever_recurring() {

		return empty($this->get_billing_cycles());

	} // end is_forever_recurring;

	/**
	 * Checks if we are on the max renewals.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function at_maximum_renewals() {

		if (!$this->is_forever_recurring()) {

			return false;

		} // end if;

		$times_billed = $this->get_times_billed() - 1; // Subtract 1 to exclude initial payment.
		$renew_times  = $this->get_billing_cycles();

		return $times_billed >= $renew_times;

	} // end at_maximum_renewals;

	/**
	 * Set the value of billing_cycles.
	 *
	 * @since 2.0.0
	 * @param mixed $billing_cycles Maximum times we should charge this membership.
	 * @return void
	 */
	public function set_billing_cycles($billing_cycles) {

		$this->billing_cycles = $billing_cycles;

	} // end set_billing_cycles;

	/**
	 * Returns the default billing address.
	 *
	 * Classes that implement this trait need to implement
	 * this method.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Objects\Billing_Address
	 */
	public function get_default_billing_address() {

		$billing_address = new \WP_Ultimo\Objects\Billing_Address();

		$customer = $this->get_customer();

		if ($customer) {

			return $customer->get_billing_address();

		} // end if;

		return $billing_address;

	} // end get_default_billing_address;

	/**
	 * Get the value of status.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_status() {

		return $this->status;

	} // end get_status;

	/**
	 * Set the value of status.
	 *
	 * @since 2.0.0
	 * @param mixed $status Status of the membership.
	 * @return void
	 */
	public function set_status($status) {

		$this->status = $status;

	} // end set_status;

	/**
	 * Returns the Label for a given severity level.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_status_label() {

		$status = new Membership_Status($this->get_status());

		return $status->get_label();

	} // end get_status_label;

	/**
	 * Gets the classes for a given class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_status_class() {

		$status = new Membership_Status($this->get_status());

		return $status->get_classes();

	} // end get_status_class;

	/**
	 * Get the value of gateway_customer_id.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_gateway_customer_id() {

		return $this->gateway_customer_id;

	} // end get_gateway_customer_id;

	/**
	 * Set the value of gateway_customer_id.
	 *
	 * @since 2.0.0
	 * @param mixed $gateway_customer_id ID of the customer on the payment gateway database.
	 * @return void
	 */
	public function set_gateway_customer_id($gateway_customer_id) {

		$this->gateway_customer_id = $gateway_customer_id;

	} // end set_gateway_customer_id;

	/**
	 * Get the value of gateway_subscription_id.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_gateway_subscription_id() {

		return $this->gateway_subscription_id;

	} // end get_gateway_subscription_id;

	/**
	 * Set the value of gateway_subscription_id.
	 *
	 * @since 2.0.0
	 * @param mixed $gateway_subscription_id ID of the subscription on the payment gateway database.
	 * @return void
	 */
	public function set_gateway_subscription_id($gateway_subscription_id) {

		$this->gateway_subscription_id = $gateway_subscription_id;

	} // end set_gateway_subscription_id;

	/**
	 * Get the value of gateway.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_gateway() {

		return $this->gateway;

	} // end get_gateway;

	/**
	 * Set the value of gateway.
	 *
	 * @since 2.0.0
	 * @param mixed $gateway ID of the gateway being used on this subscription.
	 * @return void
	 */
	public function set_gateway($gateway) {

		$this->gateway = $gateway;

	} // end set_gateway;

	/**
	 * Get the value of signup_method.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_signup_method() {

		return $this->signup_method;

	} // end get_signup_method;

	/**
	 * Set the value of signup_method.
	 *
	 * @since 2.0.0
	 * @param mixed $signup_method Signup method used to create this membership.
	 * @return void
	 */
	public function set_signup_method($signup_method) {

		$this->signup_method = $signup_method;

	} // end set_signup_method;

	/**
	 * Get the value of subscription_key.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_subscription_key() {

		return $this->subscription_key;

	} // end get_subscription_key;

	/**
	 * Set the value of subscription_key.
	 *
	 * @since 2.0.0
	 * @param mixed $subscription_key Not sure what this does.
	 * @return void
	 */
	public function set_subscription_key($subscription_key) {

		$this->subscription_key = $subscription_key;

	} // end set_subscription_key;

	/**
	 * Get the value of upgraded_from.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_upgraded_from() {

		return $this->upgraded_from;

	} // end get_upgraded_from;

	/**
	 * Set the value of upgraded_from.
	 *
	 * @since 2.0.0
	 * @param mixed $upgraded_from Plan that this membership upgraded from.
	 * @return void
	 */
	public function set_upgraded_from($upgraded_from) {

		$this->upgraded_from = $upgraded_from;

	} // end set_upgraded_from;

	/**
	 * Get the value of date_modified.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_date_modified() {

		return $this->date_modified;

	} // end get_date_modified;

	/**
	 * Set the value of date_modified.
	 *
	 * @since 2.0.0
	 * @param mixed $date_modified Date this membership was last modified.
	 * @return void
	 */
	public function set_date_modified($date_modified) {

		$this->date_modified = $date_modified;

	} // end set_date_modified;

	/**
	 * Get the value of disabled.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function is_disabled() {

		return (bool) $this->disabled;

	} // end is_disabled;

	/**
	 * Set the value of disabled.
	 *
	 * @since 2.0.0
	 * @param mixed $disabled If this membership is disabled.
	 * @return void
	 */
	public function set_disabled($disabled) {

		$this->disabled = (bool) $disabled;

	} // end set_disabled;

	/**
	 * Returns a list of payments associated with this membership.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_payments() {

		return wu_get_payments(array(
			'membership_id' => $this->get_id(),
		));

	} // end get_payments;

	/**
	 * Returns the sites attached to this membership.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sites() {

		$sites = Site::query(array(
			'meta_query' => array(
				'customer_id' => array(
					'key'   => 'wu_membership_id',
					'value' => $this->get_id(),
				),
			),
		));

		$pending_site = $this->get_pending_site();

		if ($pending_site) {

			$pending_site->set_type('pending');

			$sites[] = $pending_site;

		} // end if;

		return $sites;

	} // end get_sites;

	/**
	 * Adds a pending site to the membership meta data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $site_info Site info.
	 * @return bool
	 */
	public function create_pending_site($site_info) {

		global $current_site;

		$site_info = wp_parse_args($site_info, array(
			'title'     => '',
			'domain'    => $current_site->domain,
			'path'      => '',
			'transient' => array(),
		));

		$site = new \WP_Ultimo\Models\Site($site_info);

		$this->meta['pending_site'] = $site;

		return $this->save();

	} // end create_pending_site;

	/**
	 * Returns the pending site, if any.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Site|false
	 */
	public function get_pending_site() {

		return $this->get_meta('pending_site');

	} // end get_pending_site;

	/**
	 * Removes a pending site of a membership.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function delete_pending_site() {

		return $this->delete_meta('pending_site');

	} // end delete_pending_site;

	/**
	 * Get is this product recurring?
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_recurring() {

		return (bool) $this->recurring && (float) $this->get_amount() > 0;

	} // end is_recurring;

	/**
	 * Checks if this plan is free or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_free() {

		return $this->is_recurring() === false && $this->get_initial_amount() == 0;

	} // end is_free;

	/**
	 * Set is this product recurring?
	 *
	 * @since 2.0.0
	 * @param boolean $recurring Is this product recurring.
	 * @return void
	 */
	public function set_recurring($recurring) {

		$this->recurring = (bool) $recurring;

	} // end set_recurring;

	/**
	 * Gets the total grossed by the membership so far.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_total_grossed() {

		global $wpdb;

		static $sum;

		if ($sum === null) {

			$sum = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT SUM(total) FROM {$wpdb->base_prefix}wu_payments WHERE parent_id = 0 AND membership_id = %d",
					$this->get_id()
				)
			);

		} // end if;

		return $sum;

	} // end get_total_grossed;

	/**
	 * By default, we just use the to_array method, but you can rewrite this.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_search_results() {

		$search_result = $this->to_array();

		$search_result['customer'] = null;

		$search_result['display_name'] = '';

		if ($this->get_customer()) {

			$search_result['customer'] = $this->get_customer()->to_search_results();

			$search_result['display_name'] = $search_result['customer']['display_name'];

		} // end if;

		$search_result['formatted_price'] = $this->get_price_description();

		$search_result['reference_code'] = $this->get_hash();

		return $search_result;

	} // end to_search_results;

	/**
	 * Renews the membership by updating status and expiration date.
	 *
	 * Does NOT handle payment processing for the renewal. This should be called after receiving a renewal payment.
	 *
	 * @since  2.0.0
	 *
	 * @param bool   $recurring  Whether or not the membership is recurring.
	 * @param string $status     Membership status.
	 * @param string $expiration Membership expiration date in MySQL format.
	 * @return true|false Whether or not the renewal was successful.
	 */
	public function renew($recurring = false, $status = 'active', $expiration = '') {

		$id = $this->get_id();

		$plan_id = $this->get_plan_id();

		wu_log_add("membership-{$id}", sprintf('Starting membership renewal for membership #%d. Membership Level ID: %d; Current Expiration Date: %s', $id, $plan_id, $this->get_date_expiration()));

		if (empty($plan_id)) {

			return false;

		} // end if;

		// Bail if this has a payment plan and it's completed - prevents renewals from running after the fact.
		if (!$this->is_forever_recurring() && $this->at_maximum_renewals()) {

			return false;

		} // end if;

		$plan = wu_get_product($plan_id);

		if (!$expiration) {

			$expiration = $this->calculate_expiration($this->is_recurring());

			/**
			 * Filters the calculated expiration date to be set after the renewal.
			 *
			 * @param string     $expiration       Calculated expiration date.
			 * @param Product    $plan Membership level object.
			 * @param int        $membership_id    ID of the membership.
			 * @param Membership $this             Membership object.
			 *
			 * @since 2.0.0
			 */
			$expiration = apply_filters('wu_membership_renewal_expiration_date', $expiration, $plan, $this->get_id(), $this);

		} // end if;

		/**
		 * Triggers before the membership renewal.
		 *
		 * @param string     $expiration    New expiration date to be set.
		 * @param int        $membership_id ID of the membership.
		 * @param Membership $this          Membership object.
		 *
		 * @since 2.0
		 */
		do_action('wu_membership_pre_renew', $expiration, $this->get_id(), $this);

		$this->set_date_expiration($expiration);

		if (!empty($status)) {

			$this->set_status($status);

		} // end if;

		$this->set_recurring($recurring);

		$this->set_date_renewed(current_time('mysql')); // Current time.

		if ($this->get_user_id()) {

			delete_user_meta($this->get_user_id(), 'wu_expired_email_sent');

		} // end if;

		$status = $this->save();

		if (is_wp_error($status)) {

			return $status;

		} // end if;

		/**
		 * Triggers after the membership renewal.
		 *
		 * @param string     $expiration    New expiration date to be set.
		 * @param int        $membership_id ID of the membership.
		 * @param Membership $this          Membership object.
		 *
		 * @since 2.0
		 */
		do_action('wu_membership_post_renew', $expiration, $this->get_id(), $this);

		wu_log_add("membership-{$id}", sprintf('Completed membership renewal for membership #%d. Membership Level ID: %d; New Expiration Date: %s; New Status: %s', $id, $plan_id, $expiration, $this->get_status()));

		return true;

	} // end renew;

	/**
	 * Changes the membership status to "cancelled".
	 *
	 * Does NOT handle actual cancellation of subscription payments, that is done in rcp_process_member_cancellation().
	 * This should be called after a member is successfully cancelled.
	 *
	 * @since  2.0.0
	 * @return void
	 */
	public function cancel() {

		if ($this->get_status() === Membership_Status::CANCELLED) {

			return; // Already cancelled

		} // end if;

		/**
		 * Triggers before the membership is cancelled.
		 *
		 * @param int            $membership_id ID of the membership.
		 * @param \WP_Ultimo\Models\Membership $this          Membership object.
		 *
		 * @since 2.0
		 */
		do_action('wu_membership_pre_cancel', $this->get_id(), $this);

		// Change status.
		$this->set_status(Membership_Status::CANCELLED);

		$this->set_date_cancellation(current_time('mysql'));

		$this->save();

		/**
		 * Triggers after the membership is cancelled.
		 *
		 * This triggers the cancellation email.
		 *
		 * @param int            $membership_id ID of the membership.
		 * @param \WP_Ultimo\Models\Membership $this          Membership object.
		 *
		 * @since 2.0
		 */
		do_action('wu_membership_post_cancel', $this->get_id(), $this);

	} // end cancel;

} // end class Membership;
