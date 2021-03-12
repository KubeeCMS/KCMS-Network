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
	 * Type of registration: new, renewal, upgrade, display.
	 *
	 * The display type is used to create the tables that show the products purchased on a membership
	 * and payment screens.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $cart_type = 'new';

	/**
	 * The id of the plan being hired.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $plan_id;

	/**
	 * The membership object, if that exists.
	 *
	 * This is used to pre-populate fields such as products
	 * and more.
	 *
	 * @since 2.0.0
	 * @var null|\WP_Ultimo\Models\Memberships
	 */
	protected $membership;

	/**
	 * The country of the customer.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $country;

	/**
	 * The discount code object, if any.
	 *
	 * @since 2.0.0
	 * @var null|\WP_Ultimo\Models\Discount_Code
	 */
	protected $discount_code;

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
	protected $duration = 1;

	/**
	 * The billing cycle duration unit.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $duration_unit = 'month';

	/**
	 * The number of billign cycles.
	 *
	 * 0 means unlimited cycles (a.k.a until canceled).
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $billing_cycles = 0;

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
	 * Creates the cart object using the products, discount code.
	 *
	 * Memberships are optional and used when dealing with
	 * upgrades and downgrades. Payment ID is used when trying to recover a pending payment.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Check the wp_parse_args below for the accepted parameters.
	 */
	public function __construct($atts) {

		$atts = wp_parse_args($atts, array(
			'products'      => array(),
			'memberships'   => array(),
			'payment_id'    => false,
			'discount_code' => false,
			'country'       => '',
			'cart_type'     => '',
		));

		/*
		 * Try to figure out based on the parameters if this cart is a
		 * new order or a upgrade/downgrade.
		 */
		$this->set_cart_type($atts['cart_type']);

		if (wu_get_isset($atts, 'discount_code')) {

			$this->add_discount_code(wu_get_isset($atts, 'discount_code'));

		} // end if;

		$this->country = $atts['country'];

		// Set the country based on the customer, if empty
		if (!$this->country && !empty($this->memberships)) {

			$membership = current($this->memberships);

			$customer = $membership->get_customer();

			$this->country = $customer->get_meta('ip_country');

		} // end if;

		foreach ($atts['products'] as $product_id) {

			$this->add_product($product_id);

		} // end foreach;

		foreach ($atts['memberships'] as $membership_id) {

			$this->add_membership($membership_id);

		} // end foreach;

		/*
		 * Checks if this is a payment recovery.
		 */
		$this->maybe_recover_payment();

	} // end __construct;

	/**
	 * Gets a membership and adds it to the membership list associated with the order.
	 *
	 * We need the product_id here as well to be able to tell which membership related to which
	 * product when dealing with multi-products checkouts.
	 *
	 * @since 2.0.0
	 *
	 * @param int $membership_id ID of a given membership.
	 * @return void
	 */
	public function add_membership($membership_id) {

		$membership = wu_get_membership($membership_id);

		if ($membership) {

			$this->memberships[] = $membership;

			$products = $membership->get_all_products();

			foreach ($products as $line_item) {

				$this->add_product($line_item['product']->get_id());

			} // end foreach;

		} // end if;

	} // end add_membership;

	/**
	 * Returns the list of memberships associated with this cart.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_memberships() {

		return $this->memberships;

	} // end get_memberships;

	/**
	 * For an order to be valid, all the recurring products must have the same
	 * billing intervals and cycle.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_valid() {

		$is_valid = true;

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

		if ($product->get_type() === 'plan') {

			$this->plan_id = $product->get_id();

			$this->currency      = $product->get_currency();
			$this->duration      = $product->get_duration();
			$this->duration_unit = $product->get_duration_unit();

		} // end if;

		$this->products[] = $product;

		$line_item = new Line_Item(array(
			'product'  => $product,
			'quantity' => $quantity,
		));

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
			'taxable'     => true,
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
	 * Set registration type
	 *
	 * This is based on the following query strings:
	 *
	 *        - $_REQUEST['cart_type'] - Will either be "renewal" or "upgrade". If empty, we assume "new".
	 *        - $_REQUEST['membership_id'] - This must be provided for renewals and upgrades so we know which membership
	 *                                       to work with.
	 *
	 * @todo Not working at the moment. Needs implementation.
	 *
	 * @since 2.0.0
	 * @param string $cart_type The cart type.
	 * @return void
	 */
	public function set_cart_type($cart_type = '') {

		if ($cart_type !== '') {

			$this->cart_type = $cart_type;

			return;

		} // end if;

		$cart_type = wu_request('cart-type', 'new');

		$membership_hash = wu_request('membership');

		if ($cart_type !== 'new' && $membership_hash) {

			/**
			* The `cart_type` query arg is set, it's NOT `new`, and we have a membership ID.
			*/
			$membership = wu_get_membership_by_hash($membership_hash);

			if (!empty($membership) && $membership->get_user_id() == get_current_user_id()) {

				$this->membership = $membership;

				$this->cart_type = sanitize_text_field($cart_type);

			} // end if;

		} elseif (!wu_multiple_memberships_enabled() && $this->get_plan_id()) {

			/**
			* Multiple memberships not enabled, and we have a selected membership level ID on the form.
			* We determine if it's a renewal or upgrade based on the user's current membership level and
			* the one they've selected on the registration form.
			*/
			$customer = wu_get_current_customer();

			$previous_membership = !empty($customer) ? rcp_get_customer_single_membership($customer->get_id()) : false;

			if (!empty($previous_membership)) {

				$this->membership = $previous_membership;

				if ($this->membership->get_object_id() === $this->get_product_id()) {

					$this->cart_type = 'renewal';

				} else {

					$this->cart_type = 'upgrade';

				} // end if;

			} // end if;

		} // end if;

		if ('upgrade' === $this->cart_type && is_object($this->membership) && $this->get_plan_id()) {

			/**
			* If we have the type listed as an "upgrade", we'll run a few extra checks to determine
			* if we should change this to "downgrade".
			*/
			// Figure out if this is a downgrade instead .
			$plan = wu_get_product($this->get_plan_id());

			$previous_plan = wu_get_product($this->membership->get_plan_id());

			// if the previous membership level is invalid (maybe it's been deleted), then treat this as a new registration.
			if (empty($previous_plan)) {

				wu_log(sprintf('Previous membership level (// %d) is invalid. Treating this as a new registration.', $this->membership->get_plan_id()));

				$this->cart_type = 'new';

				return;

			} // end if;

			$days_in_old_cycle = wu_get_days_in_cycle($previous_plan->get_duration_unit(), $previous_plan->get_duration());

			$days_in_new_cycle = wu_get_days_in_cycle($plan->get_duration_unit(), $plan->get_duration());

			$old_price_per_day = $days_in_old_cycle > 0 ? $previous_plan->get_amount() / $days_in_old_cycle : $previous_plan->get_amount();

			$new_price_per_day = $days_in_new_cycle > 0 ? $this->get_recurring_total(true, false) / $days_in_new_cycle : $this->get_recurring_total(true, false);

			wu_log(sprintf('Old price per day: %s (ID #%d); New price per day: %s (ID #%d)', $old_price_per_day, $previous_plan->get_id(), $new_price_per_day, $membership_level->get_id()));

			if ($old_price_per_day > $new_price_per_day) {

				$this->cart_type = 'downgrade';

			} // end if;

		}  // end if;

	} // end set_cart_type;

	/**
	 * Get the registration type.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_cart_type() {

		return $this->cart_type;

	} // end get_cart_type;

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
	 * Attempt to recover an existing pending / abandoned / failed payment.
	 *
	 * First we look in the request for `rcp_registration_payment_id`. If that doesn't exist then we try to recover
	 * automatically. This will only work if the current membership (located via `set_cart_type()`)
	 * has a pending payment ID. This will be the case if someone was attempting to sign up or manually renew,
	 * didn't complete payment, then immediately reattempted.
	 *
	 * If a payment is located, then this payment is used for the registration instead of creating a new one.
	 * This will also used the existing membership record associated with this payment.
	 *
	 * Requirements:
	 *      - The payment status is `pending`, `abandoned`, or `failed`.
	 *      - Transaction ID is empty.
	 *      - There is an associated membership record (it's okay if it's disabled, it just needs to exist).
	 *
	 * @link  https://github.com/restrictcontentpro/restrict-content-pro/issues/2230
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function maybe_recover_payment() {

		return;

	} // end maybe_recover_payment;

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

			$total += $line_item->get_total();

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
	 * @return string|false
	 */
	public function get_billing_start_date() {
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

			$trial_period = strtotime("+$duration $duration_unit");

			if ($trial_period < $smallest_trial) {

				$smallest_trial = $trial_period;

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

		/**
		 * Should apply to fees?
		 */
		if ($line_item->get_type() === 'fee') {

			if ($this->discount_code->get_setup_fee_value() <= 0) {

				return $line_item;

			} // end if;

			$line_item->attributes(array(
				'discount_rate' => $this->discount_code->get_setup_fee_value(),
				'discount_type' => $this->discount_code->get_setup_fee_type(),
			));

		} else {

			$line_item->attributes(array(
				'discount_rate' => $this->discount_code->get_value(),
				'discount_type' => $this->discount_code->get_type(),
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

		$tax_rates = wu_get_applicable_tax_rates($this->country, $tax_category);

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
			'tax_rate'  => $tax_rate,
			'tax_type'  => $tax_type,
			'tax_label' => $tax_label,
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
			// 'proration_credits' => $this->get_proration_credits(),
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
	 * Implements our on json_decode version of this object. Useful for use in vue.js.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function done() {

		$totals = $this->calculate_totals();

		return (object) array(

			'type'          => $this->get_cart_type(),
			'valid'         => $this->is_valid(),
			'is_free'       => $this->is_free(),

			'has_plan'      => $this->has_plan(),
			'has_recurring' => $this->has_recurring(),
			'has_discount'  => $this->has_discount(),
			'has_trial'     => $this->has_trial(),

			'line_items'    => $this->get_line_items(),
			'discount_code' => $this->get_discount_code(),
			'totals'        => $this->calculate_totals(),

			'dates'         => (object) array(
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
	 * Set the value of discount_code
	 *
	 * @since 2.0.0
	 * @param \WP_Ultimo\Model\Discount_Code $discount_code The discount code object.
	 * @return void
	 */
	public function set_discount_code($discount_code) {

		$this->discount_code = $discount_code;

	} // end set_discount_code;

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
	 * Set the value of plan_id.
	 *
	 * @since 2.0.0
	 * @param mixed $plan_id The plan id.
	 * @return void
	 */
	public function set_plan_id($plan_id) {

		$this->plan_id = $plan_id;

	} // end set_plan_id;

	/**
	 * Get the currency code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_currency() {

		// return $this->currency; For now, multi-currency is not yet supported.
		return wu_get_setting('currency_symbol', 'USD');

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

} // end class Cart;
