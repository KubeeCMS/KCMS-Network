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

	use Traits\Limitable, Traits\Billable, Traits\Notable, \WP_Ultimo\Traits\WP_Ultimo_Subscription_Deprecated;

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
	 * @var int
	 */
	protected $plan_id;

	/**
	 * Additional products. Services and Packages.
	 *
	 * @since 2.0.0
	 * @var array
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
	protected $auto_renew = 0;

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
	protected $times_billed = 0;

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
	 * Keep original list of products.
	 *
	 * If the products are changed for some reason,
	 * we need to run additional code to handle updates
	 * in other parts of the code.
	 *
	 * For example, when products change, we
	 * might need to change the user role in every
	 * sub-site belonging to that membership.
	 *
	 * @since 2.0.10
	 * @var array
	 */
	protected $_compiled_product_list = array();

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Memberships\\Membership_Query';

	/**
	 * Constructs the object via the constructor arguments
	 *
	 * @since 2.0.0
	 *
	 * @param object $object Std object with model parameters.
	 */
	public function __construct($object = null) {

		parent::__construct($object);

		if (did_action('plugins_loaded')) {

			$this->_compiled_product_list = $this->get_all_products();

		} // end if;

	} // end __construct;

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

		$currency = wu_get_setting('currency_symbol', 'USD');

		$membership_status = new \WP_Ultimo\Database\Memberships\Membership_Status();

		$membership_status = $membership_status->get_allowed_list(true);

		return array(
			'customer_id'         => 'required|integer|exists:\WP_Ultimo\Models\Customer,id',
			'user_id'             => 'integer',
			'plan_id'             => 'required|integer|exists:\WP_Ultimo\Models\Product,id',
			'currency'            => "default:{$currency}",
			'duration'            => 'numeric|default:1',
			'duration_unit'       => 'in:day,week,month,year',
			'initial_amount'      => 'numeric',
			'auto_renew'          => 'boolean|default:1',
			'status'              => "in:{$membership_status}|default:pending",
			'gateway_customer_id' => 'default:',
			'upgraded_from'       => 'default:',
			'amount'              => 'numeric|default:0',
			'billing_cycles'      => 'numeric|default:0',
			'times_billed'        => 'integer|default:0',
			'active'              => 'default:1',
			'gateway'             => 'default:',
			'signup_method'       => 'default:',
			'disabled'            => 'default:0',
			'recurring'           => 'default:0'
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
	 * @return int
	 */
	public function get_customer_id() {

		return absint($this->customer_id);

	} // end get_customer_id;

	/**
	 * Set the value of customer_id.
	 *
	 * @since 2.0.0
	 * @param int $customer_id The ID of the customer attached to this membership.
	 * @return void
	 */
	public function set_customer_id($customer_id) {

		$this->customer_id = absint($customer_id);

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

		$allowed = absint($customer_id) === absint($this->get_customer_id());

		return apply_filters('wu_membership_is_customer_allowed', $allowed, $customer_id, $this);

	} // end is_customer_allowed;

	/**
	 * Get the value of user_id.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_user_id() {

		return $this->user_id;

	} // end get_user_id;

	/**
	 * Set the value of user_id.
	 *
	 * @since 2.0.0
	 * @param int $user_id The user ID attached to this membership.
	 * @return void
	 */
	public function set_user_id($user_id) {

		$this->user_id = absint($user_id);

	} // end set_user_id;

	/**
	 * Get the value of plan_id.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_plan_id() {

		return (int) $this->plan_id;

	} // end get_plan_id;

	/**
	 * Returns the plan that created this membership.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Product
	 */
	public function get_plan() {

		$plan = wu_get_product($this->get_plan_id());

		// Get the correct ariation if exists
		if ($plan && ($plan->get_duration() !== $this->get_duration() || $plan->get_duration_unit() !== $this->get_duration_unit())) {

			$variation = $plan->get_as_variation($this->get_duration(), $this->get_duration_unit());

			$plan = $variation ?? $plan;

		} // end if;

		return $plan;

	} // end get_plan;

	/**
	 * Set plan associated with the membership.
	 *
	 * @since 2.0.0
	 * @param int $plan_id The plan ID associated with the membership.
	 * @return void
	 */
	public function set_plan_id($plan_id) {

		$this->plan_id = absint($plan_id);

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

		return array_map('absint', array_keys((array) $this->addon_products));

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
	 * @return array
	 */
	public function get_addon_products() {

		$products = array();

		$this->addon_products = is_array($this->addon_products) ? $this->addon_products : array();

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
	 * @param mixed $addon_products Additional products related to this membership. Services, Packages or other types of products.
	 * @return void
	 */
	public function set_addon_products($addon_products) {

		$this->addon_products = maybe_unserialize($addon_products);

	} // end set_addon_products;

	/**
	 * Changes the membership products and totals.
	 *
	 * This is used when a upgrade, downgrade or addon
	 * checkout is processed.
	 *
	 * It takes a Cart object and uses that to construct
	 * the new membership parameters.
	 *
	 * Important: this method does not SAVE the changes
	 * you need to explicitly call save() after a swap
	 * to persist the changes.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Cart $order The cart object.
	 * @return \WP_Ultimo\Models\Membership
	 */
	public function swap($order) {

		if (!is_a($order, '\WP_Ultimo\Checkout\Cart')) {

			return new \WP_Error('invalid-date', __('Swap Cart is invalid.', 'wp-ultimo'));

		} // end if;

		/*
		 * We'll do that based on the line items,
		 * we do it that way because it is the only
		 * place where we have quantity info, as well
		 * as product ID.
		 */
		foreach ($order->get_line_items() as $line_item) {

			$product = $line_item->get_product();

			/**
			 * We only care about products.
			 */
			if (empty($product)) {

				continue;

			} // end if;

			/*
			 * Checks if this is a plan.
			 *
			 * If that's the case, we need to replace the current
			 * plan id.
			 */
			if ($product->get_type() === 'plan') {

				$this->set_plan_id($product->get_id());

				continue;

			} // end if;

			/*
			 * For other products,
			 * we add them as addons.
			 */
			$this->add_product($product->get_id(), $line_item->get_quantity());

		} // end foreach;

		/*
		 * Finally, we have a couple of other parameters to set.
		 */
		$this->set_amount($order->get_recurring_total());
		$this->set_initial_amount($order->get_total());
		$this->set_recurring($order->has_recurring());

		$this->set_duration($order->get_duration());
		$this->set_duration_unit($order->get_duration_unit());

		/*
		 * Returns self for chaining.
		 */
		return $this;

	} // end swap;

	/**
	 * Schedule a swap for the membership.
	 *
	 * @since 2.0.0
	 *
	 * @param Cart           $order The cart representing the change.
	 * @param string|boolean $schedule_date The date to schedule the change for.
	 * @return int|\WP_Error
	 */
	public function schedule_swap($order, $schedule_date = false) {

		if (empty($schedule_date)) {

			$schedule_date = $this->get_date_expiration();

		} // end if;

		if (!wu_validate_date($schedule_date)) {

			return new \WP_Error('invalid-date', __('Schedule date is invalid.', 'wp-ultimo'));

		} // end if;

		if (!is_a($order, '\WP_Ultimo\Checkout\Cart')) {

			return new \WP_Error('invalid-date', __('Swap Cart is invalid.', 'wp-ultimo'));

		} // end if;

		$date_instance = wu_date($schedule_date);

		/*
		 * Saves the order.
		 */
		$this->update_meta('wu_swap_order', $order);
		$this->update_meta('wu_swap_scheduled_date', $schedule_date);

		/*
		 * Remove the previous swaps.
		 */
		wu_unschedule_action('wu_async_membership_swap', array(
			'membership_id' => $this->get_id(),
		), 'membership');

		/*
		 * Schedule the swap.
		 */
		return wu_schedule_single_action($date_instance->format('U'), 'wu_async_membership_swap', array(
			'membership_id' => $this->get_id(),
		), 'membership');

	} // end schedule_swap;

	/**
	 * Returns the scheduled swap, if any.
	 *
	 * @since 2.0.0
	 * @return object
	 */
	public function get_scheduled_swap() {

		$order          = $this->get_meta('wu_swap_order');
		$scheduled_date = $this->get_meta('wu_swap_scheduled_date');

		if (!$scheduled_date || !$order) {

			$this->delete_meta('wu_swap_order');
			$this->delete_meta('wu_swap_scheduled_date');

			return false;

		} // end if;

		return (object) array(
			'order'          => $order,
			'scheduled_date' => $scheduled_date,
		);

	} // end get_scheduled_swap;

	/**
	 * Removes a schedule swap.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function delete_scheduled_swap() {

		$this->delete_meta('wu_swap_order');

		$this->delete_meta('wu_swap_scheduled_date');

		do_action('wu_membership_delete_scheduled_swap', $this);

	} // end delete_scheduled_swap;

	/**
	 * Returns the amount recurring in a human-friendly way.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_recurring_description() {

		$description = sprintf(
			// translators: %1$s the duration, and %2$s the duration unit (day, week, month, etc)
			_n('every %2$s', 'every %1$s %2$s', $this->get_duration(), 'wp-ultimo'), // phpcs:ignore
			$this->get_duration(),
			wu_get_translatable_string(($this->get_duration() <= 1 ? $this->get_duration_unit() : $this->get_duration_unit() . 's'))
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
			$description = __('%1$s / until cancelled', 'wp-ultimo');

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
				_n('%1$s every %3$s', '%1$s every %2$s %3$s', $duration, 'wp-ultimo'), // phpcs:ignore
				wu_format_currency($this->get_amount(), $this->get_currency()),
				$duration,
				wu_get_translatable_string($duration <= 1 ? $this->get_duration_unit() : $this->get_duration_unit() . 's')
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
				wu_format_currency($this->get_initial_amount(), $this->get_currency())
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
	 * @return string
	 */
	public function get_currency() {

		// return $this->currency; For now, multi-currency is not yet supported.
		return wu_get_setting('currency_symbol', 'USD');

	} // end get_currency;

	/**
	 * Set the value of currency.
	 *
	 * @since 2.0.0
	 * @param string $currency The currency that this membership. It's a 3-letter code. E.g. 'USD'.
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

		return absint($this->duration);

	} // end get_duration;

	/**
	 * Set time interval between charges.
	 *
	 * @param int $duration The interval period between a charge. Only the interval amount, the unit will be defined in another property.
	 */
	public function set_duration($duration) {

		$this->duration = absint($duration);

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
	 * @param string $duration_unit The duration amount type. Can be 'day', 'week', 'month' or 'year'.
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
	 * @param int $initial_amount The initial amount charged for this membership, including the setup fee.
	 */
	public function set_initial_amount($initial_amount) {

		$this->initial_amount = wu_to_float($initial_amount);

	} // end set_initial_amount;

	/**
	 * Get the value of date_created.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_created() {

		return $this->date_created;

	} // end get_date_created;

	/**
	 * Set the value of date_created.
	 *
	 * @since 2.0.0
	 * @param string $date_created Date of creation of this membership.
	 * @return void
	 */
	public function set_date_created($date_created) {

		$this->date_created = $date_created;

	} // end set_date_created;

	/**
	 * Get the value of date_activated.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_activated() {

		return $this->date_activated;

	} // end get_date_activated;

	/**
	 * Set the value of date_activated.
	 *
	 * @since 2.0.0
	 * @param string $date_activated Date when this membership was activated.
	 * @return void
	 */
	public function set_date_activated($date_activated) {

		$this->date_activated = $date_activated;

	} // end set_date_activated;

	/**
	 * Get the value of date_trial_end.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_trial_end() {

		return $this->date_trial_end;

	} // end get_date_trial_end;

	/**
	 * Set the value of date_trial_end.
	 *
	 * @since 2.0.0
	 * @param string $date_trial_end Date when the trial period ends, if this membership has or had a trial period.
	 * @return void
	 */
	public function set_date_trial_end($date_trial_end) {

		$this->date_trial_end = $date_trial_end;

	} // end set_date_trial_end;

	/**
	 * Get the value of date_renewed.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_renewed() {

		return $this->date_renewed;

	} // end get_date_renewed;

	/**
	 * Set the value of date_renewed.
	 *
	 * @since 2.0.0
	 * @param string $date_renewed Date when the membership was cancelled.
	 * @return void
	 */
	public function set_date_renewed($date_renewed) {

		$this->date_renewed = $date_renewed;

	} // end set_date_renewed;

	/**
	 * Get the value of date_cancellation.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_cancellation() {

		return $this->date_cancellation;

	} // end get_date_cancellation;

	/**
	 * Set the value of date_cancellation.
	 *
	 * @since 2.0.0
	 * @param string $date_cancellation Date when the membership was cancelled.
	 * @return void
	 */
	public function set_date_cancellation($date_cancellation) {

		$this->date_cancellation = $date_cancellation;

	} // end set_date_cancellation;

	/**
	 * Get the value of date_expiration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_expiration() {

		return $this->date_expiration;

	} // end get_date_expiration;

	/**
	 * Set the value of date_expiration.
	 *
	 * @since 2.0.0
	 * @param string $date_expiration Date when the membership will expiry.
	 * @return void
	 */
	public function set_date_expiration($date_expiration) {

		$this->date_expiration = $date_expiration;

	} // end set_date_expiration;

	/**
	 * Calculate a new expiration date.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $from_today Whether to calculate from today (`true`), or extend the existing expiration date (`false`).
	 * @param bool $trial      Whether or not this is for a free trial.
	 * @return String Date in Y-m-d H:i:s format or null if is a lifetime membership.
	 */
	public function calculate_expiration($from_today = false, $trial = false) {

		// Get the member's current expiration date
		$expiration           = $this->get_date_expiration();
		$expiration_timestamp = 0;

		if (!$this->is_recurring()) {

			return null;

		} // end if;

		if (wu_validate_date($expiration)) {

			$expiration_timestamp = wu_date($expiration)->format('U');

		} // end if;

		// Determine what date to use as the start for the new expiration calculation
		if (!$from_today && $expiration_timestamp > wu_get_current_time('timestamp', true)) { // phpcs:ignore

			$base_timestamp = $expiration_timestamp;

		} else {

			$base_timestamp = wu_get_current_time('timestamp', true); // phpcs:ignore

		} // end if;

		if ($this->get_duration() > 0) {

			if (false && $this->trial_duration > 0 && $trial) {

				$expire_timestamp = strtotime('+' . $this->get_trial_duration() . ' ' . $this->trial_duration_unit . ' 23:59:59', $base_timestamp);

			} else {

				$expire_timestamp = strtotime('+' . $this->get_duration() . ' ' . $this->get_duration_unit() . ' 23:59:59', $base_timestamp);

			} // end if;

			$extension_days = array('29', '30', '31');

			if (in_array(gmdate('j', $expire_timestamp), $extension_days, true) && 'month' === $this->get_duration_unit()) {

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

			$expiration = null; // tag as lifetime.

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
		$expiration = apply_filters('wu_membership_calculated_date_expiration', $expiration, $this->get_id(), $this);

		return $expiration;

	} // end calculate_expiration;

	/**
	 * Get the value of date_payment_plan_completed.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_payment_plan_completed() {

		return $this->date_payment_plan_completed;

	} // end get_date_payment_plan_completed;

	/**
	 * Set the value of date_payment_plan_completed.
	 *
	 * @since 2.0.0
	 * @param string $date_payment_plan_completed Change of the payment completion for the plan value.
	 * @return void
	 */
	public function set_date_payment_plan_completed($date_payment_plan_completed) {

		$this->date_payment_plan_completed = $date_payment_plan_completed;

	} // end set_date_payment_plan_completed;

	/**
	 * Get the value of auto_renew.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function should_auto_renew() {

		return (bool) $this->auto_renew;

	} // end should_auto_renew;

	/**
	 * Deprecated: get_auto_renew
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function get_auto_renew() {

		_deprecated_function(__METHOD__, '2.0.0', 'should_auto_renew()');

		return $this->should_auto_renew();

	} // end get_auto_renew;

	/**
	 * Set the value of auto_renew.
	 *
	 * @since 2.0.0
	 * @param bool $auto_renew If this membership should auto-renewal.
	 * @return void
	 */
	public function set_auto_renew($auto_renew) {

		$this->auto_renew = (bool) $auto_renew;

	} // end set_auto_renew;

	/**
	 * Get the value of times_billed.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_times_billed() {

		return (int) $this->times_billed;

	} // end get_times_billed;

	/**
	 * Set the value of times_billed.
	 *
	 * @since 2.0.0
	 * @param int $times_billed Amount of times this membership got billed.
	 * @return void
	 */
	public function set_times_billed($times_billed) {

		$this->times_billed = $times_billed;

	} // end set_times_billed;

	/**
	 * Increments times billed.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $number Amount to increment by.
	 * @return \WP_Ultimo\Models\Membership
	 */
	public function add_to_times_billed($number = 1) {

		$times_billed = absint($this->get_times_billed());

		$this->set_times_billed(($times_billed + $number));

		return $this;

	} // end add_to_times_billed;

	/**
	 * Get the value of billing_cycles.
	 *
	 * @since 2.0.0
	 * @return int
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
	 * @param int $billing_cycles Maximum times we should charge this membership.
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
	 * Checks if the current membership has a active status.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_active() {

		$active_statuses = array(
			Membership_Status::ACTIVE,
			Membership_Status::ON_HOLD,
		);

		$active = in_array($this->status, $active_statuses, true);

		return apply_filters('wu_membership_is_active', $active, $this);

	} // end is_active;

	/**
	 * Get the value of status.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_status() {

		return $this->status;

	} // end get_status;

	/**
	 * Set the value of status.
	 *
	 * @since 2.0.0
	 * @param string $status The membership status. Can be 'pending', 'active', 'on-hold', 'expired', 'cancelled' or other values added by third-party add-ons.
	 * @options \WP_Ultimo\Database\Payments\Payment_Status
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
	 * @return string
	 */
	public function get_gateway_customer_id() {

		return $this->gateway_customer_id;

	} // end get_gateway_customer_id;

	/**
	 * Set the value of gateway_customer_id.
	 *
	 * @since 2.0.0
	 * @param int $gateway_customer_id The ID of the customer on the payment gateway database.
	 * @return void
	 */
	public function set_gateway_customer_id($gateway_customer_id) {

		$this->gateway_customer_id = $gateway_customer_id;

	} // end set_gateway_customer_id;

	/**
	 * Get the value of gateway_subscription_id.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_gateway_subscription_id() {

		return $this->gateway_subscription_id;

	} // end get_gateway_subscription_id;

	/**
	 * Set the value of gateway_subscription_id.
	 *
	 * @since 2.0.0
	 * @param string $gateway_subscription_id The ID of the subscription on the payment gateway database.
	 * @return void
	 */
	public function set_gateway_subscription_id($gateway_subscription_id) {

		$this->gateway_subscription_id = $gateway_subscription_id;

	} // end set_gateway_subscription_id;

	/**
	 * Get the value of gateway.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_gateway() {

		return $this->gateway;

	} // end get_gateway;

	/**
	 * Set the value of gateway.
	 *
	 * @since 2.0.0
	 * @param string $gateway ID of the gateway being used on this subscription.
	 * @return void
	 */
	public function set_gateway($gateway) {

		$this->gateway = $gateway;

	} // end set_gateway;

	/**
	 * Get the value of signup_method.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_signup_method() {

		return $this->signup_method;

	} // end get_signup_method;

	/**
	 * Set the value of signup_method.
	 *
	 * @since 2.0.0
	 * @param string $signup_method Signup method used to create this membership.
	 * @return void
	 */
	public function set_signup_method($signup_method) {

		$this->signup_method = $signup_method;

	} // end set_signup_method;

	/**
	 * Get the value of upgraded_from.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_upgraded_from() {

		return $this->upgraded_from;

	} // end get_upgraded_from;

	/**
	 * Set the value of upgraded_from.
	 *
	 * @since 2.0.0
	 * @param int $upgraded_from Plan that this membership upgraded from.
	 * @return void
	 */
	public function set_upgraded_from($upgraded_from) {

		$this->upgraded_from = $upgraded_from;

	} // end set_upgraded_from;

	/**
	 * Get the value of date_modified.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_modified() {

		return $this->date_modified;

	} // end get_date_modified;

	/**
	 * Set the value of date_modified.
	 *
	 * @since 2.0.0
	 * @param string $date_modified Date this membership was last modified.
	 * @return void
	 */
	public function set_date_modified($date_modified) {

		$this->date_modified = $date_modified;

	} // end set_date_modified;

	/**
	 * Get the value of disabled.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_disabled() {

		return (bool) $this->disabled;

	} // end is_disabled;

	/**
	 * Set the value of disabled.
	 *
	 * @since 2.0.0
	 * @param bool $disabled If this membership is a disabled one.
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
	 * Returns the last pending payment for a membership.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Payment
	 */
	public function get_last_pending_payment() {

		$payments = wu_get_payments(array(
			'membership_id'      => $this->get_id(),
			'status'             => 'pending',
			'number'             => 1,
			'orderby'            => 'id',
			'order'              => 'DESC',
			'gateway_payment_id' => '',
		));

		return !empty($payments) ? array_pop($payments) : false;

	} // end get_last_pending_payment;

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
			'title'         => '',
			'domain'        => $current_site->domain,
			'path'          => '',
			'transient'     => array(),
			'is_publishing' => false,
		));

		$site = new \WP_Ultimo\Models\Site($site_info);

		return $this->update_meta('pending_site', $site);

	} // end create_pending_site;

	/**
	 * Updates a pending site to the membership meta data.
	 *
	 * @since 2.0.11
	 *
	 * @param \WP_Ultimo\Models\Site $site Site info.
	 * @return bool
	 */
	public function update_pending_site($site) {

		return $this->update_meta('pending_site', $site);

	} // end update_pending_site;

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
	 * Published a pending site, but via job queue.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function publish_pending_site_async() {
		/*
		 * If the force sync setting is on, fallback to the sync version.
		 */
		if (wu_get_setting('force_publish_sites_sync', false)) {

			$this->publish_pending_site();

			return;

		} // end if;

		// We first try to generate the site through request to start earlier as possible.
		$rest_path = add_query_arg(
			array(
				'action'        => 'wu_publish_pending_site',
				'_ajax_nonce'   => wp_create_nonce('wu_publish_pending_site'),
				'membership_id' => $this->get_id(),
			),
			admin_url( 'admin-ajax.php' )
		);

		if ( function_exists( 'fastcgi_finish_request' ) && version_compare( phpversion(), '7.0.16', '>=' ) ) {
			// The server supports fastcgi, so we use this to guaranty that the function started before abort connection

			wp_remote_request( $rest_path, array(
				'sslverify' => false,
			));

		} elseif (ignore_user_abort(true) !== ignore_user_abort(false)) {
			// We do not have fastcgi but can make the request continue without listening

			wp_remote_request( $rest_path, array(
				'sslverify' => false,
				'blocking'  => false,
			));

		} // end if;

		wu_enqueue_async_action('wu_async_publish_pending_site', array('membership_id' => $this->get_id()), 'membership');

	} // end publish_pending_site_async;

	/**
	 * Publishes a pending site.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error
	 */
	public function publish_pending_site() {
		/*
		 * Trigger event before the publication of a site.
		 */
		do_action('wu_before_pending_site_published', $this);

		$pending_site = $this->get_pending_site();

		if (!$pending_site) {

			return true;

		} // end if;

		$is_publishing = $pending_site->is_publishing();

		if ($is_publishing) {

			return true;

		} // end if;

		$pending_site->set_publishing(true);

		$this->update_pending_site($pending_site);

		$pending_site->set_type('customer_owned');

		$saved = $pending_site->save();

		if (is_wp_error($saved)) {

			return $saved;

		} // end if;

		$this->delete_pending_site();

		/*
		 * Trigger event that marks the publication of a site.
		 */
		do_action('wu_pending_site_published', $pending_site, $this);

		return true;

	} // end publish_pending_site;

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
	 * Checks if this is a lifetime membership.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_lifetime() {

		return empty($this->get_date_expiration()) || $this->get_date_expiration() === '0000-00-00 00:00:00';

	} // end is_lifetime;

	/**
	 * Checks if this plan is free or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_free() {

		return $this->is_recurring() === false && empty($this->get_initial_amount());

	} // end is_free;

	/**
	 * Set is this product recurring?
	 *
	 * @since 2.0.0
	 * @param boolean $recurring If this membership is recurring (true), which means the customer paid a defined amount each period of time, or not recurring (false).
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
	 * @param bool   $auto_renew  Whether or not the membership is recurring.
	 * @param string $status     Membership status.
	 * @param string $expiration Membership expiration date in MySQL format.
	 * @return true|false Whether or not the renewal was successful.
	 */
	public function renew($auto_renew = false, $status = 'active', $expiration = '') {

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

			$expiration = $this->calculate_expiration(!$this->is_recurring());

			/**
			 * Filters the calculated expiration date to be set after the renewal.
			 *
			 * @param string     $expiration       Calculated expiration date.
			 * @param Product    $plan Membership level object.
			 * @param int        $membership_id    The ID of the membership.
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
		 * @param int        $membership_id The ID of the membership.
		 * @param Membership $this          Membership object.
		 *
		 * @since 2.0
		 */
		do_action('wu_membership_pre_renew', $expiration, $this->get_id(), $this);

		$this->set_date_expiration($expiration);

		if (!empty($status)) {

			$this->set_status($status);

		} // end if;

		$this->set_auto_renew($auto_renew);

		$this->set_date_renewed(wu_get_current_time('mysql')); // Current time.

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
		 * @param int        $membership_id The ID of the membership.
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
		 * @param int            $membership_id The ID of the membership.
		 * @param \WP_Ultimo\Models\Membership $this          Membership object.
		 *
		 * @since 2.0
		 */
		do_action('wu_membership_pre_cancel', $this->get_id(), $this);

		// Change status.
		$this->set_status(Membership_Status::CANCELLED);

		$this->set_date_cancellation(wu_get_current_time('mysql'));

		$this->save();

		/**
		 * Triggers after the membership is cancelled.
		 *
		 * This triggers the cancellation email.
		 *
		 * @param int            $membership_id The ID of the membership.
		 * @param \WP_Ultimo\Models\Membership $this          Membership object.
		 *
		 * @since 2.0
		 */
		do_action('wu_membership_post_cancel', $this->get_id(), $this);

	} // end cancel;

	/**
	 * Returns the number of days still left in the cycle.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_remaining_days_in_cycle() {
		/*
		 * If this is a lifetime membership, we have unlimited days. Large number.
		 */
		if (!$this->is_recurring()) {

			return 10000;

		} // end if;

		/*
		 * We need to have a valid expiration date.
		 */
		if (empty($this->get_date_expiration()) || !wu_validate_date($this->get_date_expiration())) {

			return 0;

		} // end if;

		/*
		 * Otherwise, we need to check based on the
		 * expiration date.
		 */
		$expiration_date = wu_date($this->get_date_expiration());
		$today           = wu_date();

		/*
		 * If today is larger than the expiration date,
		 * it means that the customer used all the membership time.
		 * There's nothing to pro-rate in that case.
		 */
		if ($today > $expiration_date) {

			return 0;

		} // end if;

		return floor($today->diffInDays($expiration_date));

	} // end get_remaining_days_in_cycle;

	/**
	 * List of limitations that need to be merged.
	 *
	 * Every model that is limitable (imports this trait)
	 * needs to declare explicitly the limitations that need to be
	 * merged. This allows us to chain the merges, and gives us
	 * a final list of limitations at the end of the process.
	 *
	 * In the case of membership, we need to mash up
	 * all the limitations associated with the membership
	 * plan and additional packages.
	 *
	 * @see \WP_Ultimo\Models\Traits\Trait_Limitable
	 * @since 2.0.0
	 * @return array
	 */
	public function limitations_to_merge() {

		$limitations_to_merge = array();

		$product_ids = array($this->get_plan_id());

		$product_ids = array_merge($this->get_addon_ids(), $product_ids);

		foreach ($product_ids as $product_id) {

			$limitations_to_merge[] = \WP_Ultimo\Objects\Limitations::early_get_limitations('product', $product_id);

		} // end foreach;

		return $limitations_to_merge;

	} // end limitations_to_merge;

	/**
	 * Checks if the membership has product changes.
	 *
	 * @since 2.0.10
	 * @return boolean
	 */
	protected function has_product_changes() {

		if (empty($this->_compiled_product_list)) {

			return false;

		} // end if;

		return $this->_compiled_product_list != $this->get_all_products(); // phpcs:ignore

	} // end has_product_changes;

	/**
	 * Get the number of remaining sites available to this membership.
	 *
	 * This means sites that can be still added.
	 *
	 * @since 2.0.11
	 * @return int
	 */
	public function get_remaining_sites() {

		$limit = $this->get_limitations()->sites->get_limit();

		if (!$this->get_limitations()->sites->is_enabled()) {

			return PHP_INT_MAX;

		} // end if;

		$limit = $limit === '' ? PHP_INT_MAX : $limit;

		return $limit - count($this->get_sites());

	} // end get_remaining_sites;

	/**
	 * Checks if the current membership has remaining sites available.
	 *
	 * @since 2.0.11
	 * @return boolean
	 */
	public function has_remaining_sites() {

		return $this->get_remaining_sites() >= 1;

	} // end has_remaining_sites;

	/**
	 * Save (create or update) the model on the database.
	 *
	 * @since 2.0.0
	 *
	 * @return bool|\WP_Error
	 */
	public function save() {

		$saved = parent::save();

		if ($this->has_product_changes()) {

			wu_enqueue_async_action('wu_async_after_membership_update_products', array(
				'membership_id' => $this->get_id(),
			), 'membership');

		} // end if;

		return $saved;

	} // end save;

} // end class Membership;
