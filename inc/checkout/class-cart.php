<?php
/**
 * Creates a cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Order
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Checkout\Line_Item;
use \WP_Ultimo\Dependencies\Arrch\Arrch as Array_Search;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
class Cart implements \JsonSerializable {

	/**
	 * Holds a list of errors.
	 *
	 * These errors do not include
	 * validation errors, only errors
	 * that happen while we try to setup
	 * the cart object.
	 *
	 * @since 2.0.0
	 * @var \WP_Error
	 */
	public $errors;

	/**
	 * Cart Attributes.
	 *
	 * List of attributes passed to the
	 * constructor.
	 *
	 * @since 2.0.0
	 * @var object
	 */
	private $attributes;

	/**
	 * Type of registration: new, renewal, upgrade, downgrade, retry, and display.
	 *
	 * The display type is used to create the tables that show the products purchased on a membership
	 * and payment screens.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $cart_type;

	/**
	 * The customer object, if that exists.
	 *
	 * @since 2.0.0
	 * @var null|\WP_Ultimo\Models\Customer
	 */
	protected $customer;

	/**
	 * The membership object, if that exists.
	 *
	 * This is used to pre-populate fields such as products
	 * and more.
	 *
	 * @since 2.0.0
	 * @var null|\WP_Ultimo\Models\Membership
	 */
	protected $membership;

	/**
	 * The payment object, if that exists.
	 *
	 * This is used to pre-populate fields such as products
	 * and more.
	 *
	 * @since 2.0.0
	 * @var null|\WP_Ultimo\Models\Payment
	 */
	protected $payment;

	/**
	 * The discount code object, if any.
	 *
	 * @since 2.0.0
	 * @var null|\WP_Ultimo\Models\Discount_Code
	 */
	protected $discount_code;

	/**
	 * The country of the customer.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $country;

	/**
	 * The state of the customer.
	 *
	 * @since 2.0.11
	 * @var string
	 */
	protected $state;

	/**
	 * The city of the customer.
	 *
	 * @since 2.0.11
	 * @var string
	 */
	protected $city;

	/**
	 * The currency of this purchase.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $currency;

	/**
	 * The billing cycle duration.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $duration;

	/**
	 * The billing cycle duration unit.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $duration_unit;

	/**
	 * The number of billing cycles.
	 *
	 * 0 means unlimited cycles (a.k.a until cancelled).
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $billing_cycles = 0;

	/**
	 * The id of the plan being hired.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $plan_id;

	/**
	 * The cart products.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Models\Product[]
	 */
	protected $products = array();

	/**
	 * Line item representation of the products.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Checkout\Line_Item[]
	 */
	protected $line_items = array();

	/**
	 * If this cart should auto-renew.
	 *
	 * This flag tells the gateways that support
	 * subscriptions to go ahead and try to set up
	 * a new one.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $auto_renew = true;

	/**
	 * Extra parameters to send to front-end.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $extra = array();

	/**
	 * Construct our cart/order object.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args An array containing the cart arguments.
	 */
	public function __construct($args) {
		/*
		 * Why are we using shortcode atts, you might ask?
		 *
		 * Well, shortcode atts cleans the array, allowing only
		 * the keys we list on the defaults array.
		 *
		 * Since we're passing over the entire $_POST array
		 * this helps us to keep things cleaner and secure.
		 */
		$args = shortcode_atts(array(

			/*
			 * Cart Type.
			 */
			'cart_type'     => 'new',

			/*
			 * The list of products being bought.
			 */
			'products'      => array(),

			/*
			 * The duration parameters
			 * This will dictate which price variations we are going to use.
			 */
			'duration'      => false,
			'duration_unit' => false,

			/*
			 * The membership ID.
			 * This is passed when we want to handle a upgrade/downgrade/addon.
			 */
			'membership_id' => false,

			/*
			 * Payment ID.
			 * This is passed when we are trying to recovered a abandoned/pending payment.
			 */
			'payment_id'    => false,

			/*
			 * The discount code to be used.
			 */
			'discount_code' => false,

			/*
			 * If we should auto-renew or not.
			 */
			'auto_renew'    => true,

			/*
			 * The country, state, and city of the customer.
			 * Used for taxation purposes.
			 */
			'country'       => '',
			'state'         => '',
			'city'          => '',

			/*
			 * Currency
			 */
			'currency'      => '',

		), $args);

		/*
		 * Checks for errors
		 */
		$this->errors = new \WP_Error;

		/*
		 * Save arguments in memory
		 */
		$this->attributes = (object) $args;

		/**
		 * Allow developers to make additional changes to
		 * the checkout object.
		 *
		 * @since 2.0.0
		 * @param self The cart object.
		 */
		do_action('wu_cart_setup', $this);

		/*
		 * Set the country, duration and duration_unit.
		 */
		$this->cart_type     = $this->attributes->cart_type;
		$this->country       = $this->attributes->country;
		$this->state         = $this->attributes->state;
		$this->city          = $this->attributes->city;
		$this->currency      = $this->attributes->currency;
		$this->duration      = $this->attributes->duration;
		$this->duration_unit = $this->attributes->duration_unit;

		/*
		 * Loads the current customer, if it exists.
		 */
		$this->customer = wu_get_current_customer();

		/*
		 * At this point, we have almost everything we can ready.
		 * It's time to deal with discount codes.
		 */
		$this->set_discount_code($this->attributes->discount_code);

		/*
		 * Delegates the logic to another
		 * method that builds up the cart.
		 */
		$this->build_cart();

		/*
		 * Also set the auto-renew status.
		 *
		 * This setting can be forced if the settings say so,
		 * so we only set it if that is not enabled.
		 */
		if (!wu_get_setting('force_auto_renew', true)) {

			$this->auto_renew = wu_string_to_bool($this->attributes->auto_renew);

		} // end if;

		/*
		 * Calculate-totals.
		 *
		 * This will make sure our cart is ready to be consumed
		 * by other parts of the code.
		 */
		$this->calculate_totals();

		/**
		 * Allow developers to make additional changes to
		 * the checkout object.
		 *
		 * @since 2.0.0
		 * @param self The cart object.
		 */
		do_action('wu_cart_after_setup', $this);

	} // end __construct;

	/**
	 * Get additional parameters set by integrations and add-ons.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $key The parameter key.
	 * @param boolean $default The default value.
	 * @return mixed
	 */
	public function get_param($key, $default = false) {

		return wu_get_isset($this->attributes, $key, $default);

	} // end get_param;

	/**
	 * Set additional parameters set by integrations and add-ons.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The key to set.
	 * @param mixed  $value The value to set.
	 * @return void
	 */
	public function set_param($key, $value) {

		$this->extra[] = $key;

		$this->attributes->{$key} = $value;

	} // end set_param;

	/**
	 * Gets the tax exempt status of the current cart.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_tax_exempt() {

		return apply_filters('wu_cart_is_tax_exempt', false, $this);

	} // end is_tax_exempt;

	/**
	 * Builds the cart.
	 *
	 * Here, we try to determine the type of
	 * cart so we can properly set it up, based
	 * on the payment, membership, and products passed.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function build_cart() {
		/*
		 * Maybe deal with payment recovery first.
		 */
		$is_recovery_cart = $this->build_from_payment($this->attributes->payment_id);

		/*
		 * If we are recovering a payment, we stop right here.
		 * The pending payment object has all the info we need
		 * in order to build the proper cart.
		 */
		if ($is_recovery_cart) {

			return;

		} // end if;

		/*
		 * The next step is to deal with membership changes.
		 * These include downgrades/upgrades and addons.
		 */
		$is_membership_change = $this->build_from_membership($this->attributes->membership_id);

		/*
		 * If this is a membership change,
		 * we can return as our work is done.
		 */
		if ($is_membership_change) {

			return;

		} // end if;

		/*
		 * Otherwise, we add the the products normally,
		 * and set the cart as new.
		 */
		$this->cart_type = 'new';

		if (is_array($this->attributes->products)) {
		  /*
			 * Otherwise, we add the products to build the cart.
			 */
			foreach ($this->attributes->products as $product_id) {

				$this->add_product($product_id);

			} // end foreach;

		} // end if;

	} // end build_cart;

	/**
	 * Creates a string that describes the cart.
	 *
	 * Some gateways require a description that you need
	 * to match after the payment confirmation.
	 *
	 * This method generates such a string based on
	 * the products on the cart.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_cart_descriptor() {

		$desc = wu_get_setting('company_name', __('Subscription', 'wp-ultimo'));

		$products = array();

		foreach ($this->get_line_items() as $line_item) {

			$product = $line_item->get_product();

			if (!$product) {

				continue;

			} // end if;

			$products[] = $line_item->get_title();

		} // end foreach;

		$descriptor = $desc . ' - ' . implode(', ', $products);

		return trim($descriptor);

	} // end get_cart_descriptor;

	/**
	 * Decides if we are trying to recover a payment.
	 *
	 * @since 2.0.0
	 *
	 * @param int $payment_id A valid payment ID.
	 * @return bool
	 */
	protected function build_from_payment($payment_id) {

		if ($this->cart_type === 'downgrade') {

			return false;

		} // end if;

		/*
		 * No valid payment id passed, so we
		 * are not trying to recover a payment.
		 */
		if (empty($payment_id)) {

			return false;

		} // end if;

		/*
		 * We got here, that means
		 * the intend behind this cart was to actually
		 * recover a payment.
		 *
		 * That means we can safely set the cart type to retry.
		 */
		$this->cart_type = 'retry';

		/*
		 * Now, let's try to fetch the payment in question.
		 */
		$payment = wu_get_payment($payment_id);

		if (!$payment) {

			$this->errors->add('payment_not_found', __('The payment in question was not found.', 'wp-ultimo'));

			return true;

		} // end if;

		/*
		 * The payment exists, set it globally.
		 */
		$this->payment = $payment;

		/*
		 * Check for payment status.
		 *
		 * We want to make sure we only allow for repayment of pending,
		 * cancelled, or abandoned payments
		 */
		$allowed_status = apply_filters('wu_cart_set_payment_allowed_status', array(
			'pending',
		));

		if (!in_array($payment->get_status(), $allowed_status, true)) {

			$this->errors->add('invalid_status', __('The payment in question has an invalid status.', 'wp-ultimo'));

			return true;

		} // end if;

		/*
		 * Adds the country to calculate taxes.
		 */
		$this->country = $this->country ? $this->country : ($this->customer ? $this->customer->get_country() : '');

		/*
		 * Check for the correct permissions.
		 *
		 * For obvious reasons, only the customer that owns
		 * a payment can pay it. Let's check for that.
		 */
		if (empty($this->customer) || $this->customer->get_id() !== $payment->get_customer_id()) {

			$this->errors->add('lacks_permission', __('You are not allowed to modify this payment.', 'wp-ultimo'));

			return true;

		} // end if;

		/*
		 * Sets the membership as well, to prevent issues
		 */
		$membership = $payment->get_membership();

		if (!$membership) {

			$this->errors->add('membership_not_found', __('The membership in question was not found.', 'wp-ultimo'));

			return true;

		} // end if;

		/*
		 * If the membership is active
		 * this can't be a retry, so we skip
		 * the rest.
		 */
		if ($membership->get_status() === 'active') {

			return false;

		} // end if;

		/*
		 * Sets membership globally.
		 */
		$this->membership    = $membership;
		$this->duration      = $membership->get_duration();
		$this->duration_unit = $membership->get_duration_unit();

		/*
		 * Finally, copy the line items from the payment.
		 */
		foreach ($payment->get_line_items() as $line_item) {

			$product = $line_item->get_product();

			if ($product) {

				$this->products[] = $product;

			} // end if;

			$this->add_line_item($line_item);

		} // end foreach;

		return true;

	} // end build_from_payment;

	/**
	 * Uses the membership to decide if this is a upgrade/downgrade/addon cart.
	 *
	 * @since 2.0.0
	 *
	 * @param int $membership_id A valid membership ID.
	 * @return bool
	 */
	protected function build_from_membership($membership_id) {
		/*
		 * No valid membership id passed, so we
		 * are not trying to change a membership.
		 */
		if (empty($membership_id)) {

			return false;

		} // end if;

		/*
		 * We got here, that means
		 * the intend behind this cart was to actually
		 * change a membership.
		 *
		 * We can set the cart type provisionally.
		 * This assignment might change in the future, as we make
		 * additional assertions about the contents of the cart.
		 */
		$this->cart_type = 'upgrade';

		/*
		 * Now, let's try to fetch the membership in question.
		 */
		$membership = wu_get_membership($membership_id);

		if (!$membership) {

			$this->errors->add('membership_not_found', __('The membership in question was not found.', 'wp-ultimo'));

			return true;

		} // end if;

		/*
		 * The membership exists, set it globally.
		 */
		$this->membership = $membership;

		/*
		 * In the case of membership changes,
		 * the status is not that relevant, as customers
		 * might want to make changes to memberships that are
		 * active, cancelled, etc.
		 *
		 * We do need to check for permissions, though.
		 * Only the customer that owns a membership can change it.
		 */
		if (empty($this->customer) || $this->customer->get_id() !== $membership->get_customer_id()) {

			$this->errors->add('lacks_permission', __('You are not allowed to modify this membership.', 'wp-ultimo'));

			return true;

		} // end if;

		/*
		 * Adds the country to calculate taxes.
		 */
		$this->country = $this->country ? $this->country : $this->customer->get_country();

		/*
		 * If we get to this point, we now need to assess
		 * what are the changes being made.
		 *
		 * First, we need to see if there are actual products
		 * being added, and process those.
		 */
		if (empty($this->attributes->products)) {

			$this->errors->add('no_changes', __('This cart proposes no changes to the current membership.', 'wp-ultimo'));

			return true;

		} // end if;

		/*
		 * Sets the durations to avoid problems
		 * with addon purchases.
		 */
		$plan_product = $membership->get_plan();

		if ($plan_product) {

			$this->duration      = $plan_product->get_duration();
			$this->duration_unit = $plan_product->get_duration_unit();

		} // end if;

		/*
		 * Otherwise, we add the products to build the cart.
		 */
		foreach ($this->attributes->products as $product_id) {

			$this->add_product($product_id);

		} // end foreach;

		/*
		 * With products added, let's check if this is an addon.
		 *
		 * An addon cart adds a new product or service to the current membership.
		 * If this cart, after adding the products, doesn't have a plan, it means
		 * it should continue to use the membership plan, and the other products
		 * must be added to the membership.
		 */
		if (empty($this->plan_id)) {

			if (count($this->products) === 0) {

				$this->errors->add('no_changes', __('This cart proposes no changes to the current membership.', 'wp-ultimo'));

				return true;

			} // end if;

			/*
			 * Set the type to addon.
			 */
			$this->cart_type = 'addon';

			/*
			 * Checks the membership to see if we need to add back the
			 * setup fee.
			 *
			 * If the membership was already successfully charged once,
			 * it probably means that the setup fee was already paid, so we can skip it.
			 */
			add_filter('wu_apply_signup_fee', function() use ($membership) {

				return $membership->get_times_billed() <= 0;

			});

			/*
			 * Adds the membership plan back in, for completeness.
			 * This is also useful to make sure we present
			 * the totals correctly for the customer.
			 */
			$this->add_product($membership->get_plan_id());

			/*
			 * Adds the credit line, after
			 * calculating pro-rate.
			 */
			$this->calculate_prorate_credits();

			return true;

		} // end if;

		/*
		 * With products added, let's check if the plan is changing.
		 *
		 * A plan change implies a upgrade or a downgrade, which we will determine
		 * below.
		 *
		 * A plan change can take many forms.
		 * - Different plan altogether;
		 * - Same plan with different periodicity;
		 * - upgrade to lifetime;
		 * - downgrade to free;
		 */
		$is_plan_change = false;

		if ($membership->get_plan_id() !== $this->plan_id) {

			$is_plan_change = true;

		} // end if;

		/*
		 * Checks for periodicity changes.
		 */
		$old_periodicity = sprintf('%s-$s', $membership->get_duration(), $membership->get_duration_unit());
		$new_periodicity = sprintf('%s-$s', $this->duration, $this->duration_unit);

		if ($old_periodicity !== $new_periodicity) {

			$is_plan_change = true;

		} // end if;

		/*
		 * If there is no plan change, but the product count is > 1
		 * We know that there is another product in this cart other than the
		 * plan, so this is again an addon cart.
		 */
		if (count($this->products) > 1 && $is_plan_change === false) {
			/*
			 * Set the type to addon.
			 */
			$this->cart_type = 'addon';

			/*
			 * Checks the membership to see if we need to add back the
			 * setup fee.
			 *
			 * If the membership was already successfully charged once,
			 * it probably means that the setup fee was already paid, so we can skip it.
			 */
			add_filter('wu_apply_signup_fee', function() use ($membership) {

				return $membership->get_times_billed() <= 0;

			});

			/*
			 * Adds the credit line, after
			 * calculating pro-rate.
			 */
			$this->calculate_prorate_credits();

			return true;

		} // end if;

		/*
		 * We'll probably never enter in this if, but we
		 * hev it here to prevent bugs.
		 */
		if (!$is_plan_change) {

			$this->products   = array();
			$this->line_items = array();

			$this->errors->add('no_changes', __('This cart proposes no changes to the current membership.', 'wp-ultimo'));

			return true;

		} // end if;

		/*
		 * Upgrade to Lifetime.
		 */
		if (!$this->has_recurring() && !$this->is_free()) {
			/*
			 * Adds the credit line, after
			 * calculating pro-rate.
			 */
			$this->calculate_prorate_credits();

			return true;

		} // end if;

		/*
		 * If we get to this point, we know that this is either
		 * an upgrade or a downgrade, so we need to determine which.
		 *
		 * Since by default we set the value to upgrade,
		 * we just need to check for a downgrade scenario.
		 */
		$days_in_old_cycle = wu_get_days_in_cycle($membership->get_duration_unit(), $membership->get_duration());

		$days_in_new_cycle = wu_get_days_in_cycle($this->duration_unit, $this->duration);

		$old_price_per_day = $days_in_old_cycle > 0 ? $membership->get_amount() / $days_in_old_cycle : $membership->get_amount();

		$new_price_per_day = $days_in_new_cycle > 0 ? $this->get_recurring_total() / $days_in_new_cycle : $this->get_recurring_total();

		/*
		 * If the old price is greater than the new price, this is a downgrade.
		 */
		if ($old_price_per_day > $new_price_per_day) {

			$this->cart_type = 'downgrade';

		} // end if;

		/*
		 * All set!
		 */
		return true;

	} // end build_from_membership;

	/**
	 * Calculate pro-rate credits.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function calculate_prorate_credits() {
		/*
		 * Now we come to the craziest part: pro-rating!
		 *
		 * This is super hard to get right, but we basically need to add
		 * new line items to account for the time using the old plan.
		 */
		if ($this->membership->is_lifetime() || !$this->membership->is_recurring()) {

			$credit = $this->membership->get_initial_amount();

		} else {

			$days_unused = $this->membership->get_remaining_days_in_cycle();

			$days_in_old_cycle = wu_get_days_in_cycle($this->membership->get_duration_unit(), $this->membership->get_duration());

			$old_price_per_day = $days_in_old_cycle > 0 ? $this->membership->get_amount() / $days_in_old_cycle : $this->membership->get_amount();

			$credit = $days_unused * $old_price_per_day;

			if ($credit > $this->membership->get_amount()) {

				$credit = $this->membership->get_amount();

			} // end if;

		} // end if;

		/*
		 * No credits
		 */
		if (empty($credit)) {

			return;

		} // end if;

		/*
		 * Checks if we need to add back the value of the
		 * setup fee
		 */
		$has_setup_fee = $this->get_line_items_by_type('fee');

		if (!empty($has_setup_fee) || $this->get_cart_type() === 'upgrade') {

			$product = $this->membership->get_plan();

			if ($product) {

				$setup_fee_price = $product->get_setup_fee();

				$new_line_item = new Line_Item(array(
					'product'     => $product,
					'type'        => 'fee',
					'description' => '--',
					'title'       => '',
					'taxable'     => $product->is_taxable(),
					'recurring'   => false,
					'unit_price'  => $product->get_setup_fee(),
					'quantity'    => 1,
				));

				$new_line_item = $this->apply_taxes_to_item($new_line_item);

				$new_line_item->recalculate_totals();

				$credit += $new_line_item->get_total();

			} // end if;

		} // end if;

		/**
		 * Allow plugin developers to meddle with the credit value.
		 *
		 * @since 2.0.0
		 *
		 * @param int  $credit The credit amount.
		 * @param self $cart This cart object.
		 */
		$credit = apply_filters('wu_checkout_calculate_prorate_credits', $credit, $this);

		/*
		 * No credits
		 */
		if (empty($credit)) {

			return;

		} // end if;

		$line_item_params = apply_filters('wu_checkout_credit_line_item_params', array(
			'type'         => 'credit',
			'title'        => __('Credit', 'wp-ultimo'),
			'description'  => __('Prorated amount based on the previous membership.', 'wp-ultimo'),
			'discountable' => false,
			'taxable'      => false,
			'quantity'     => 1,
			'unit_price'   => -$credit,
		));

		/*
		 * Finally, we add the credit to the purchase.
		 */
		$credit_line_item = new Line_Item($line_item_params);

		$this->add_line_item($credit_line_item);

	} // end calculate_prorate_credits;

	/**
	 * Adds a discount code to the cart.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $code A valid discount code ID or code.
	 * @return void
	 */
	protected function set_discount_code($code) {

		if (empty($code)) {

			return;

		} // end if;

		$code = strtoupper($code);

		$discount_code = wu_get_discount_code_by_code($code);

		if (empty($discount_code)) {

			// translators: %s is the coupon code being used, all-caps. e.g. PROMO10OFF
			$this->errors->add('discount_code', sprintf(__('The code %s do not exist or is no longer valid.', 'wp-ultimo'), $code));

			return false;

		} // end if;

		$is_valid = $discount_code->is_valid();

		if (is_wp_error($is_valid)) {

			$this->errors->merge_from($is_valid);

			return false;

		} // end if;

		/*
		 * Set the coupon
		 */
		$this->discount_code = $discount_code;

		return true;

	} // end set_discount_code;

	/**
	 * Returns the current errors.
	 *
	 * @since 2.0.0
	 * @return \WP_Error
	 */
	public function get_errors() {

		return $this->errors;

	} // end get_errors;

	/**
	 * For an order to be valid, all the recurring products must have the same
	 * billing intervals and cycle.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_valid() {

		$is_valid = true;

		/*
		 * If we got any errors during
		 * the setup, bail.
		 */
		if ($this->errors->has_errors()) {

			return false;

		} // end if;

		$interval = null;

		foreach ($this->line_items as $line_item) {

			$duration      = $line_item->get_duration();
			$duration_unit = $line_item->get_duration_unit();
			$cycles        = $line_item->get_billing_cycles();

			if (!$line_item->is_recurring()) {

				continue;

			} // end if;

			/*
			 * Create a key that will tell us if something changes.
			 *
			 * If unit, duration or cycles are different, we return false.
			 * This means that this order is not valid.
			 *
			 * Maybe in the future we can try to come of ways of accommodating
			 * different billing periods on the same order, right now, there
			 * isn't a way of doing that with all the different gateways we
			 * plan to support.
			 */
			$line_item_interval = "{$duration}-{$duration_unit}-{$cycles}";

			if (!$interval) {

				$interval = $line_item_interval;

			} // end if;

			if ($line_item_interval !== $interval) {

				$this->errors->add('wrong', sprintf(__('Interval %1$s and %2$s do not match.', 'wp-ultimo'), $line_item_interval, $interval));

				return false;

			} // end if;

		} // end foreach;

		return $is_valid;

	}  // end is_valid;

	/**
	 * Checks if this order is free.
	 *
	 * This is used on the checkout to deal with this separately.
	 *
	 * @todo handle 100% off coupon codes.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_free() {

		return empty($this->get_total());

	} // end is_free;

	/**
	 * Checks if we need to collect a payment method.
	 *
	 * Will return false if the order is free or when
	 * the order contains a trial and no payment method is required.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function should_collect_payment() {

		$should_collect_payment = true;

		if ($this->is_free()) {

			$should_collect_payment = false;

		} elseif ($this->has_trial()) {

			$should_collect_payment = !wu_get_setting('allow_trial_without_payment_method', false);

		} // end if;

		return (bool) apply_filters('wu_cart_should_collect_payment', $should_collect_payment, $this);

	} // end should_collect_payment;

	/**
	 * Checks if the cart has a plan.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_plan() {

		return (bool) $this->get_plan();

	} // end has_plan;

	/**
	 * Returns the cart plan.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Product
	 */
	public function get_plan() {

		return wu_get_product($this->plan_id);

	} // end get_plan;

	/**
	 * Returns the recurring products added to the cart.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_recurring_products() {

		return $this->recurring_products;

	} // end get_recurring_products;

	/**
	 * Returns the non-recurring products added to the cart.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_non_recurring_products() {

		return $this->additional_products;

	} // end get_non_recurring_products;

	/**
	 * Returns an array containing all products added to the cart, recurring or not.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_all_products() {

		return $this->products;

	} // end get_all_products;

	/**
	 * Returns the duration value for this cart.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_duration() {

		return $this->duration;

	} // end get_duration;

	/**
	 * Returns the duration unit for this cart.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_duration_unit() {

		return $this->duration_unit;

	} // end get_duration_unit;

	/**
	 * Add a new line item.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Line_Item $line_item The line item.
	 * @return void
	 */
	public function add_line_item($line_item) {

		if (!is_a($line_item, '\WP_Ultimo\Checkout\Line_Item')) {

			return;

		} // end if;

		if ($line_item->is_discountable()) {

			$line_item = $this->apply_discounts_to_item($line_item);

		} // end if;

		if ($line_item->is_taxable()) {

			$line_item = $this->apply_taxes_to_item($line_item);

		} // end if;

		$this->line_items[$line_item->get_id()] = $line_item;

		krsort($this->line_items);

	} // end add_line_item;

	/**
	 * Adds a new product to the cart.
	 *
	 * @since 2.0.0
	 *
	 * @param integer $product_id The product id to add.
	 * @param integer $quantity The quantity.
	 * @return bool
	 */
	public function add_product($product_id, $quantity = 1) {

		$product = is_numeric($product_id) ? wu_get_product($product_id) : wu_get_product_by_slug($product_id);

		if (!$product) {

			return false;

		} // end if;

		// Here we check if the product is recurring and if so, get the correct variation
		if ($product->is_recurring() && !empty($this->duration) && ($this->duration !== $product->get_duration() || $this->duration_unit !== $product->get_duration_unit())) {

			$product = $product->get_as_variation($this->duration, $this->duration_unit);

			if (!$product) {

				return false;

			} // end if;

		} // end if;

		if ($product->get_type() === 'plan') {
			/*
			 * If we already have a plan, we can't add
			 * another one. Bail.
			 */
			if (!empty($this->plan_id)) {

				return false;

			} // end if;

			$this->plan_id        = $product->get_id();
			$this->billing_cycles = $product->get_billing_cycles();

		} // end if;

		/*
		 * We only try to reset the duration and such if
		 * they are not already set.
		 *
		 * We need to do this because we
		 * want access this to fetch price variations.
		 */
		if (empty($this->duration) || $product->is_recurring() === false) {

			$this->duration      = $product->get_duration();
			$this->duration_unit = $product->get_duration_unit();

		} // end if;

		if (empty($this->currency)) {

			$this->currency = $product->get_currency();

		} // end if;

		/*
		 * Set product amount in here, because
		 * that can change...
		 */
		$amount        = $product->get_amount();
		$duration      = $product->get_duration();
		$duration_unit = $product->get_duration_unit();

		/*
		 * Deal with price variations.
		 *
		 * Here's the general idea:
		 *
		 * If the cart duration or duration unit differs from
		 * the product's, we try to fetch a price variation.
		 *
		 * If a price variation doesn't exist, we add an error to
		 * the cart.
		 */
		if ($product->is_free() === false) {

			if (absint($this->duration) !== $product->get_duration() || $this->duration_unit !== $product->get_duration_unit()) {

				$price_variation = $product->get_price_variation($this->duration, $this->duration_unit);

				if ($price_variation) {

					$price_variation = (object) $price_variation;

					$amount        = $price_variation->amount;
					$duration      = $price_variation->duration;
					$duration_unit = $price_variation->duration_unit;

				} else {
					/*
					 * This product does not have a valid
					 * price variation. We need to add an error.
					 */
					// translators: respectively, product name, duration, and duration unit.
					$message = sprintf(__('%1$s does not have a valid price variation for that billing period (every %2$s %3$s(s)) and was not added to the cart.', 'wp-ultimo'), $product->get_name(), $this->duration, $this->duration_unit);

					$this->errors->add('missing-price-variations', $message);

					return false;

				} // end if;

			} // end if;

		} // end if;

		$line_item_data = apply_filters('wu_add_product_line_item', array(
			'product'       => $product,
			'quantity'      => $quantity,
			'unit_price'    => $amount,
			'duration'      => $duration,
			'duration_unit' => $duration_unit,
		), $product, $duration, $duration_unit, $this);

		$this->products[] = $product;

		if (empty($line_item_data)) {

			return false;

		} // end if;

		$line_item = new Line_Item($line_item_data);

		/*
		 * Allows for product removal on the checkout summary,
		 */
		$line_item->product_slug = $product->get_slug();

		$this->add_line_item($line_item);

		/**
		 * Signup Fees
		 */
		if (empty($product->get_setup_fee())) {

			return true;

		} // end if;

		$add_signup_fee = 'renewal' !== $this->get_cart_type();

		/**
		 * Filters whether or not the signup fee should be applied.
		 *
		 * @param bool             $add_signup_fee Whether or not to add the signup fee.
		 * @param object           $product   Membership level object.
		 * @param Cart $this           Registration object.
		 *
		 * @since 3.1
		 */
		$add_signup_fee = apply_filters('wu_apply_signup_fee', $add_signup_fee, $product, $this);

		if (!$add_signup_fee) {

			return true;

		} // end if;

		// translators: placeholder is the product name.
		$description = ($product->get_setup_fee() > 0) ? __('Signup Fee for %s', 'wp-ultimo') : __('Signup Credit for %s', 'wp-ultimo');

		$description = sprintf($description, $product->get_name());

		$setup_fee_line_item = new Line_Item(array(
			'product'     => $product,
			'type'        => 'fee',
			'description' => '--',
			'title'       => $description,
			'taxable'     => $product->is_taxable(),
			'recurring'   => false,
			'unit_price'  => $product->get_setup_fee(),
			'quantity'    => $quantity,
		));

		$this->add_line_item($setup_fee_line_item);

		return true;

	} // end add_product;

	/**
	 * Returns an array containing the subtotal per tax rate.
	 *
	 * @since 2.0.0
	 * @return array $tax_rate => $tax_total.
	 */
	public function get_tax_breakthrough() {

		$line_items = $this->line_items;

		$tax_brackets = array();

		foreach ($line_items as $line_item) {

			$tax_bracket = $line_item->get_tax_rate();

			if (isset($tax_brackets[$tax_bracket])) {

				$tax_brackets[$tax_bracket] += $line_item->get_tax_total();

				continue;

			} // end if;

			$tax_brackets[$tax_bracket] = $line_item->get_tax_total();

		} // end foreach;

		return $tax_brackets;

	} // end get_tax_breakthrough;

	/**
	 * Determine whether or not the level being registered for has a trial that the current user is eligible
	 * for. This will return false if there is a trial but the user is not eligible for it.
	 *
	 * @access public
	 * @since  2.0.0
	 * @return bool
	 */
	public function has_trial() {

		$products = $this->get_all_products();

		if (empty($products)) {

			return false;

		} // end if;

		$is_trial = $this->get_billing_start_date();

		if (!$is_trial) {

			return false;

		} // end if;

		// There is a trial, but let's check eligibility.
		$customer = wu_get_current_customer();

		// No customer, which means they're brand new, which means they're eligible.
		if (empty($customer)) {

			return true;

		} // end if;

		return !$customer->has_trialed();

	} // end has_trial;

	/**
	 * Get the recovered payment object.
	 *
	 * @since 2.0.0
	 * @return object|false Payment object if set, false if not.
	 */
	public function get_recovered_payment() {

		return $this->recovered_payment;

	} // end get_recovered_payment;

	/**
	 * Add discount to the order.
	 *
	 * @since 2.0.0
	 *
	 * @param string $code Coupon code to add.
	 * @return bool
	 */
	public function add_discount_code($code) {

		$discount_code = wu_get_discount_code_by_code($code);

		if (!$discount_code) {

			return false;

		} // end if;

		$this->discount_code = $discount_code;

		return true;

	} // end add_discount_code;

	/**
	 * Get registration discounts.
	 *
	 * @since 2.5
	 * @return array|bool
	 */
	public function get_discounts() {

		return $this->get_line_items_by_type('discount');

	} // end get_discounts;

	/**
	 * Checks if the cart has any discounts applied.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_discount() {

		return $this->get_total_discounts() > 0;

	} // end has_discount;

	/**
	 * Returns a list of line items based on the line item type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type. Can be 'product', 'fee', 'discount'.
	 * @param array  $where_clauses Additional where clauses for search.
	 * @return \WP_Ultimo\Checkout\Line_Item[]
	 */
	public function get_line_items_by_type($type = 'product', $where_clauses = array()) {

		$where_clauses[] = array('type', $type);

		// Cast to array recursively
		$line_items = json_decode(json_encode($this->line_items), true);

		$line_items = Array_Search::find($line_items, array(
			'where' => $where_clauses,
		));

		$ids = array_keys($line_items);

		return array_filter($this->line_items, function($id) use($ids) {

			return in_array($id, $ids, true);

		}, ARRAY_FILTER_USE_KEY);

	} // end get_line_items_by_type;

	/**
	 * Get registration fees.
	 *
	 * @since 2.0.0
	 * @return array|bool
	 */
	public function get_fees() {

		return $this->get_line_items_by_type('fees');

	} // end get_fees;

	/**
	 * Calculates the total tax amount.
	 *
	 * @todo Refactor this.
	 * @since 2.0.0
	 * @return float
	 */
	public function get_total_taxes() {

		$total_taxes = 0;

		foreach ($this->line_items as $line_item) {

			$total_taxes += $line_item->get_tax_total();

		} // end foreach;

		return $total_taxes;

	} // end get_total_taxes;

	/**
	 * Get the total number of fees.
	 *
	 * @since 2.0.0
	 *
	 * @param null $total The total of fees in the order so far.
	 * @param bool $only_recurring | set to only get fees that are recurring.
	 *
	 * @return float
	 */
	public function get_total_fees($total = null, $only_recurring = false) {

		$line_items = $this->get_fees();

		if (!$line_items) {

			return 0;

		} // end if;

		$fees = 0;

		foreach ($line_items as $fee) {

			if ($only_recurring && !$fee->is_recurring()) {

				continue;

			} // end if;

			$fees += $fee->get_total();

		} // end foreach;

		// if total is present, make sure that any negative fees are not
		// greater than the total.
		if ($total && ($fees + $total) < 0) {

			$fees = -1 * $total;

		} // end if;

		return apply_filters('wu_cart_get_total_fees', (float) $fees, $total, $only_recurring, $this);

	} // end get_total_fees;

	/**
	 * Get the total proration amount.
	 *
	 * @todo Needs to be used and implemented on the checkout flow.
	 * @since 2.0.0
	 *
	 * @return float
	 */
	public function get_proration_credits() {

		if (!$this->get_fees()) {

			return 0;

		} // end if;

		$proration = 0;

		foreach ($this->get_fees() as $fee) {

			if (!$fee['proration']) {

				continue;

			} // end if;

			$proration += $fee['amount'];

		} // end foreach;

		return apply_filters('wu_cart_get_proration_fees', (float) $proration, $this);

	} // end get_proration_credits;

	/**
	 * Get the total discounts.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_total_discounts() {

		$total_discount = 0;

		foreach ($this->line_items as $line_item) {

			$total_discount -= $line_item->get_discount_total();

		} // end foreach;

		$total_discount = round($total_discount, wu_currency_decimal_filter());

		return apply_filters('wu_cart_get_total_discounts', $total_discount, $this);

	} // end get_total_discounts;

	/**
	 * Gets the subtotal value of the cart.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_subtotal() {

		$subtotal = 0;

		$exclude_types = array(
			'discount',
			'credit',
		);

		foreach ($this->line_items as $line_item) {

			if (in_array($line_item->get_type(), $exclude_types, true)) {

				continue;

			} // end if;

			$subtotal += $line_item->get_subtotal();

		} // end foreach;

		if (0 > $subtotal) {

			$subtotal = 0;

		} // end if;

		$subtotal = round($subtotal, wu_currency_decimal_filter());

		/**
		 * Filter the "initial amount" total.
		 *
		 * @param float $subtotal     Total amount due today.
		 * @param \WP_Ultimo\Checkout\Cart Cart object.
		 */
		return apply_filters('wu_cart_get_subtotal', floatval($subtotal), $this);

	} // end get_subtotal;

	/**
	 * Get the registration total due today.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_total() {

		$total = 0;

		foreach ($this->line_items as $line_item) {

			$total += $line_item->get_total();

		} // end foreach;

		if (0 > $total) {

			$total = 0;

		} // end if;

		$total = round($total, wu_currency_decimal_filter());

		/**
		 * Filter the "initial amount" total.
		 *
		 * @param float $total     Total amount due today.
		 * @param \WP_Ultimo\Checkout\Cart Cart object.
		 */
		return apply_filters('wu_cart_get_total', floatval($total), $this);

	} // end get_total;

	/**
	 * Get the registration recurring total.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_recurring_total() {

		$total = 0;

		foreach ($this->line_items as $line_item) {

			if (!$line_item->is_recurring()) {

				continue;

			} // end if;

			/*
			 * Check for coupon codes
			 */
			if ($line_item->get_discount_total() > 0 && !$line_item->should_apply_discount_to_renewals()) {

				$new_line_item = clone $line_item;

				$new_line_item->attributes(array(
					'discount_rate' => 0,
				));

				$new_line_item->recalculate_totals();

				$amount = $new_line_item->get_total();

			} else {

				$amount = $line_item->get_total();

			} // end if;

			$total += $amount;

		} // end foreach;

		if (0 > $total) {

			$total = 0;

		} // end if;

		$total = round($total, wu_currency_decimal_filter());

		/**
		 * Filters the "recurring amount" total.
		 *
		 * @param float $total     Recurring amount.
		 * @param \WP_Ultimo\Checkout\Cart Cart object.
		 */
		return apply_filters('wu_cart_get_recurring_total', floatval($total), $this);

	} // end get_recurring_total;

	/**
	 * Gets the recurring subtotal, before taxes.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_recurring_subtotal() {

		$subtotal = 0;

		foreach ($this->line_items as $line_item) {

			if (!$line_item->is_recurring()) {

				continue;

			} // end if;

			$subtotal += $line_item->get_subtotal();

		} // end foreach;

		if (0 > $subtotal) {

			$subtotal = 0;

		} // end if;

		$subtotal = round($subtotal, wu_currency_decimal_filter());

		/**
		 * Filters the "recurring amount" total.
		 *
		 * @param float $subtotal     Recurring amount.
		 * @param \WP_Ultimo\Checkout\Cart Cart object.
		 */
		return apply_filters('wu_cart_get_recurring_total', floatval($subtotal), $this);

	}  // end get_recurring_subtotal;

	/**
	 * Returns the timestamp of the end of the trial period.
	 *
	 * @since 2.0.0
	 * @return string|null
	 */
	public function get_billing_start_date() {

		if ($this->is_free()) {

			return null;

		} // end if;

		/*
		 * Set extremely high value at first to prevent any change of errors.
		 */
		$smallest_trial = 300 * YEAR_IN_SECONDS;

		foreach ($this->get_all_products() as $product) {

			if (!$product->has_trial()) {

				$smallest_trial = 0;

			} // end if;

			$duration = $product->get_trial_duration();

			$duration_unit = $product->get_trial_duration_unit();

			if ($duration && $duration_unit) {

				$trial_period = strtotime("+$duration $duration_unit");

				if ($trial_period < $smallest_trial) {

					$smallest_trial = $trial_period;

				} // end if;

			} // end if;

		} // end foreach;

		return $smallest_trial;

	} // end get_billing_start_date;

	/**
	 * Returns the timestamp of the next charge, if recurring.
	 *
	 * @since 2.0.0
	 * @return string|false
	 */
	public function get_billing_next_charge_date() {
		/*
		 * Set extremely high value at first to prevent any chance of errors.
		 */
		$smallest_next_charge = 300 * YEAR_IN_SECONDS;

		foreach ($this->get_all_products() as $product) {

			if (!$product->is_recurring() || $product->has_trial()) {

				continue;

			} // end if;

			$duration = $product->get_duration();

			$duration_unit = $product->get_duration_unit();

			$next_charge = strtotime("+$duration $duration_unit");

			if ($next_charge < $smallest_next_charge) {

				$smallest_next_charge = $next_charge;

			} // end if;

		} // end foreach;

		return $smallest_next_charge;

	}  // end get_billing_next_charge_date;

	/**
	 * Checks if the order is recurring or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_recurring() {

		return $this->get_recurring_total() > 0;

	} // end has_recurring;

	/**
	 * Returns an array with all types of line-items of the cart.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_line_items() {

		return $this->line_items;

	} // end get_line_items;

	/**
	 * Apply discounts to a line item.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Line_Item $line_item The line item.
	 * @return \WP_Ultimo\Checkout\Line_Item
	 */
	public function apply_discounts_to_item($line_item) {

		/**
		 * Product is not taxable, bail.
		 */
		if (!$line_item->is_discountable() || !$this->discount_code) {

			return $line_item;

		} // end if;

		if (is_wp_error($this->discount_code->is_valid($line_item->get_product_id()))) {

			return $line_item;

		} // end if;

		/**
		 * Should apply to fees?
		 */
		if ($line_item->get_type() === 'fee') {

			if ($this->discount_code->get_setup_fee_value() <= 0) {

				return $line_item;

			} // end if;

			$line_item->attributes(array(
				'discount_rate'              => $this->discount_code->get_setup_fee_value(),
				'discount_type'              => $this->discount_code->get_setup_fee_type(),
				'apply_discount_to_renewals' => false,
				'discount_label'             => strtoupper($this->discount_code->get_code()),
			));

		} else {

			$line_item->attributes(array(
				'discount_rate'              => $this->discount_code->get_value(),
				'discount_type'              => $this->discount_code->get_type(),
				'apply_discount_to_renewals' => $this->discount_code->should_apply_to_renewals(),
				'discount_label'             => strtoupper($this->discount_code->get_code()),
			));

		} // end if;

		$line_item->recalculate_totals();

		return $line_item;

	} // end apply_discounts_to_item;

	/**
	 * Apply taxes to a line item.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Line_Item $line_item The line item.
	 * @return \WP_Ultimo\Checkout\Line_Item
	 */
	public function apply_taxes_to_item($line_item) {

		/**
		 * Tax collection is not enabled
		 */
		if (!wu_should_collect_taxes()) {

			return $line_item;

		} // end if;

		/**
		 * Product is not taxable, bail.
		 */
		if (!$line_item->is_taxable()) {

			return $line_item;

		} // end if;

		$tax_category = $line_item->get_tax_category();

		/**
		 * No tax category, bail.
		 */
		if (!$tax_category) {

			return $line_item;

		} // end if;

		$tax_rates = apply_filters('wu_cart_applicable_tax_rates', wu_get_applicable_tax_rates($this->country, $tax_category, $this->state, $this->city), $this->country, $tax_category, $this);

		if (empty($tax_rates)) {

			return $line_item;

		} // end if;

		foreach ($tax_rates as $applicable_tax_rate) {

			$tax_type  = 'percentage';
			$tax_rate  = $applicable_tax_rate['tax_rate'];
			$tax_label = $applicable_tax_rate['title'];

			continue;

		} // end foreach;

		$line_item->attributes(array(
			'tax_rate'      => $tax_rate,
			'tax_type'      => $tax_type,
			'tax_label'     => $tax_label,
			'tax_inclusive' => wu_get_setting('inclusive_tax', false),
			'tax_exempt'    => $this->is_tax_exempt(),
		));

		$line_item->recalculate_totals();

		return $line_item;

	} // end apply_taxes_to_item;

	/**
	 * Calculates the totals of the cart and return them.
	 *
	 * @since 2.0.0
	 * @return object
	 */
	public function calculate_totals() {

		return (object) array(
			'recurring'       => (object) array(
				'subtotal' => $this->get_recurring_subtotal(),
				'total'    => $this->get_recurring_total(),
			),
			'subtotal'        => $this->get_subtotal(),
			'total_taxes'     => $this->get_total_taxes(),
			'total_fees'      => $this->get_total_fees(),
			'total_discounts' => $this->get_total_discounts(),
			'total'           => $this->get_total(),
		);

	} // end calculate_totals;

	/**
	 * Used for serialization purposes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function jsonSerialize() {

		return $this->done();

	} // end jsonSerialize;

	/**
	 * Get the list of extra parameters.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_extra_params() {

		$extra_params = array();

		foreach ($this->extra as $key) {

			$extra_params[$key] = $this->get_param($key);

		} // end foreach;

		return apply_filters('wu_cart_get_extra_params', $extra_params, $this);

	} // end get_extra_params;

	/**
	 * Implements our on json_decode version of this object. Useful for use in vue.js.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function done() {

		$totals = $this->calculate_totals();

		$errors = array();

		if ($this->errors->has_errors()) {

			foreach ($this->errors as $code => $messages) {

				foreach ($messages as $message) {

					$errors[] = array(
						'code'    => $code,
						'message' => $message,
					);

				} // end foreach;

			} // end foreach;

		} // end if;

		return (object) array(

			'errors'                 => $errors,
			'url'                    => $this->get_cart_url(),
			'type'                   => $this->get_cart_type(),
			'valid'                  => $this->is_valid(),
			'is_free'                => $this->is_free(),
			'should_collect_payment' => $this->should_collect_payment(),

			'has_plan'               => $this->has_plan(),
			'has_recurring'          => $this->has_recurring(),
			'has_discount'           => $this->has_discount(),
			'has_trial'              => $this->has_trial(),

			'line_items'             => $this->get_line_items(),
			'discount_code'          => $this->get_discount_code(),
			'totals'                 => $this->calculate_totals(),

			'extra'                  => $this->get_extra_params(),

			'dates'                  => (object) array(
				'date_trial_end'   => $this->get_billing_start_date(),
				'date_next_charge' => $this->get_billing_next_charge_date(),
			),

		);

	} // end done;

	/**
	 * Converts the current cart to an array of membership elements.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_membership_data() {

		$membership_data = array();

		$all_additional_products = $this->get_line_items_by_type('product', array(
			array('product_id', '!=', $this->get_plan_id()),
		));

		$addon_list = array();

		foreach ($all_additional_products as $line_item) {

			$addon_list[$line_item->get_product_id()] = $line_item->get_quantity();

		} // end foreach;

		$membership_data = array_merge(array(
			'recurring'      => $this->has_recurring(),
			'plan_id'        => $this->get_plan() ? $this->get_plan()->get_id() : 0,
			'initial_amount' => $this->get_total(),
			'addon_products' => $addon_list,
			'currency'       => $this->get_currency(),
			'duration'       => $this->get_duration(),
			'duration_unit'  => $this->get_duration_unit(),
			'amount'         => $this->get_recurring_total(),
			'times_billed'   => 0,
			'billing_cycles' => $this->get_plan() ? $this->get_plan()->get_billing_cycles() : 0,
			'auto_renew'     => false, // @todo: revisit
			'upgraded_from'  => false, // @todo: revisit
		));

		return $membership_data;

	} // end to_membership_data;

	/**
	 * Converts the current cart to a payment data array.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_payment_data() {

		$payment_data = array();

		// Creates the pending payment
		$payment_data = array(
			'status'        => 'pending',
			'tax_total'     => $this->get_total_taxes(),
			'fees'          => $this->get_total_fees(),
			'discounts'     => $this->get_total_discounts(),
			'line_items'    => $this->get_line_items(),
			'discount_code' => $this->get_discount_code() ? $this->get_discount_code()->get_code() : '',
			'subtotal'      => $this->get_subtotal(),
			'total'         => $this->get_total(),
		);

		return $payment_data;

	} // end to_payment_data;

	/**
	 * Get the value of discount_code
	 *
	 * @since 2.0.0
	 * @return null|\WP_Ultimo\Model\Discount_Code
	 */
	public function get_discount_code() {

		return $this->discount_code;

	} // end get_discount_code;

	/**
	 * Get the value of plan_id
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_plan_id() {

		return $this->plan_id;

	} // end get_plan_id;

	/**
	 * Get the currency code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_currency() {

		return $this->currency;

	} // end get_currency;

	/**
	 * Set the currency.
	 *
	 * @since 2.0.0
	 * @param mixed $currency The currency code.
	 * @return void
	 */
	public function set_currency($currency) {

		$this->currency = $currency;

	} // end set_currency;

	/**
	 * Get the cart membership.
	 *
	 * @since 2.0.0
	 * @return null|\WP_Ultimo\Models\Membership
	 */
	public function get_membership() {

		return $this->membership;

	} // end get_membership;

	/**
	 * Get the cart payment.
	 *
	 * @since 2.0.0
	 * @return null|\WP_Ultimo\Models\Payment
	 */
	public function get_payment() {

		return $this->payment;

	} // end get_payment;

	/**
	 * Get the cart customer.
	 *
	 * @since 2.0.0
	 * @return null|\WP_Ultimo\Models\Customer
	 */
	public function get_customer() {

		return $this->customer;

	} // end get_customer;

	/**
	 * Set the cart membership.
	 *
	 * @since 2.0.0
	 * @param \WP_Ultimo\Models\Membership $membership A valid membership object.
	 * @return void
	 */
	public function set_membership($membership) {

		$this->membership = $membership;

	} // end set_membership;

	/**
	 * Set the cart customer.
	 *
	 * @since 2.0.0
	 * @param \WP_Ultimo\Models\Customer $customer A valid customer object.
	 * @return void
	 */
	public function set_customer($customer) {

		$this->customer = $customer;

	} // end set_customer;

	/**
	 * Set the cart payment.
	 *
	 * @since 2.0.0
	 * @param \WP_Ultimo\Models\Payment $payment A valid payment object.
	 * @return void
	 */
	public function set_payment($payment) {

		$this->payment = $payment;

	} // end set_payment;

	/**
	 * Get the value of auto_renew.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function should_auto_renew() {

		return $this->auto_renew === 'yes' || $this->auto_renew === true;

	} // end should_auto_renew;

	/**
	 * Get the cart type.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_cart_type() {

		return $this->cart_type;

	} // end get_cart_type;

	/**
	 * Get the country of the customer.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_country() {

		return $this->country;

	} // end get_country;

	/**
	 * Set the country of the customer.
	 *
	 * @since 2.0.0
	 * @param string $country The country of the customer.
	 * @return void
	 */
	public function set_country($country) {

		$this->country = $country;

	} // end set_country;

	/**
	 * Builds a cart URL that we can use with the browser history APIs.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_cart_url() {

		$base_url = '';

		$plan = wu_get_product($this->plan_id);

		if ($plan) {

			$base_url .= $plan->get_slug();

		} // end if;

		if ($this->duration && absint($this->duration) !== 1) {

			$base_url .= "/{$this->duration}";

		} // end if;

		if ($this->duration_unit && $this->duration_unit !== 'month') {

			$base_url .= "/{$this->duration_unit}";

		} // end if;

		$all_products = $this->products;

		$products_list = array();

		foreach ($all_products as $product) {

			if ($product->get_id() !== $this->plan_id) {

				$products_list[] = $product->get_slug();

			} // end if;

		} // end foreach;

		return add_query_arg(array(
			'products' => $products_list,
		), $base_url);

	} // end get_cart_url;

} // end class Cart;
