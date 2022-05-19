<?php
/**
 * The Payment model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use \WP_Ultimo\Models\Base_Model;
use \WP_Ultimo\Database\Payments\Payment_Status;
use \WP_Ultimo\Checkout\Line_Item;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Payment model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Payment extends Base_Model {

	use Traits\Notable;

	/**
	 * ID of the product of this payment.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $product_id;

	/**
	 * ID of the customer attached to this payment.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $customer_id;

	/**
	 * Membership ID.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $membership_id;

	/**
	 * Parent payment.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $parent_id;

	/**
	 * Currency for this payment. 3-letter currency code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $currency;

	/**
	 * Value before taxes, discounts, fees and etc.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $subtotal = 0;

	/**
	 * Refund total in this payment.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $refund_total = 0;

	/**
	 * The total value in discounts.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $discount_total = 0;

	/**
	 * The amount, in currency, of the tax.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $tax_total = 0;

	/**
	 * Discount code used.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $discount_code;

	/**
	 * Total value of the payment.
	 *
	 * This takes into account fees, discounts, credits, etc.
	 *
	 * @since 2.0.0
	 * @var float
	 */
	protected $total = 0;

	/**
	 * Status of the status.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $status;

	/**
	 * Gateway used to process this payment.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $gateway;

	/**
	 * ID of the payment on the gateway, if it exists.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $gateway_payment_id;

	/**
	 * Array containing representations of the line items on this payment.
	 *
	 * @since 2.0.0
	 * @var \WP_Ultimo\Checkout\Line_Item[]
	 */
	protected $line_items;

	/**
	 * Sequential invoice number assigned to this payment.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $invoice_number;

	/**
	 * Holds if we need to cancel the membership on refund.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $cancel_membership_on_refund;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Payments\\Payment_Query';

	/**
	 * Adds magic methods to return formatted values automatically.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Method name.
	 * @param array  $args List of arguments.
	 * @throws \BadMethodCallException Throws exception when method is not found.
	 * @return mixed
	 */
	public function __call($name, $args) {

		$method_key = str_replace('_formatted', '', $name);

		if (strpos($name, '_formatted') !== false && method_exists($this, $method_key)) {

			return wu_format_currency($this->{"$method_key"}(), $this->get_currency());

		} // end if;

		throw new \BadMethodCallException($name);

	} // end __call;

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

		$payment_types = new \WP_Ultimo\Database\Payments\Payment_Status();

		$payment_types = $payment_types->get_allowed_list(true);

		return array(
			'customer_id'                 => 'required|integer|exists:\WP_Ultimo\Models\Customer,id',
			'membership_id'               => 'required|integer|exists:\WP_Ultimo\Models\Membership,id',
			'parent_id'                   => 'integer|default:',
			'currency'                    => "default:{$currency}",
			'subtotal'                    => 'required|numeric',
			'refund_total'                => 'numeric',
			'tax_total'                   => 'numeric',
			'discount_code'               => 'alpha_dash',
			'total'                       => 'required|numeric',
			'status'                      => "required|in:{$payment_types}",
			'gateway'                     => 'default:',
			'gateway_payment_id'          => 'default:',
			'discount_total'              => 'integer',
			'invoice_number'              => 'default:',
			'cancel_membership_on_refund' => 'boolean|default:0',
		);

	} // end validation_rules;

	/**
	 * Gets the customer object associated with this payment.
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
	 * @param int $customer_id The ID of the customer attached to this payment.
	 * @return void
	 */
	public function set_customer_id($customer_id) {

		$this->customer_id = absint($customer_id);

	} // end set_customer_id;

	/**
	 * Gets the membership object associated with this payment.
	 *
	 * @todo Implement this.
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Membership;
	 */
	public function get_membership() {

		return wu_get_membership($this->get_membership_id());

	} // end get_membership;

	/**
	 * Get membership ID.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_membership_id() {

		return $this->membership_id;

	} // end get_membership_id;

	/**
	 * Set membership ID.
	 *
	 * @since 2.0.0
	 * @param int $membership_id The ID of the membership attached to this payment.
	 * @return void
	 */
	public function set_membership_id($membership_id) {

		$this->membership_id = $membership_id;

	} // end set_membership_id;

	/**
	 * Get parent payment ID.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_parent_id() {

		return $this->parent_id;

	} // end get_parent_id;

	/**
	 * Set parent payment ID.
	 *
	 * @since 2.0.0
	 * @param int $parent_id The ID from another payment that this payment is related to.
	 * @return void
	 */
	public function set_parent_id($parent_id) {

		$this->parent_id = $parent_id;

	} // end set_parent_id;

	/**
	 * Get currency for this payment. 3-letter currency code.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_currency() {

		// return $this->currency; For now, multi-currency is not yet supported.
		return wu_get_setting('currency_symbol', 'USD');

	} // end get_currency;

	/**
	 * Set currency for this payment. 3-letter currency code.
	 *
	 * @since 2.0.0
	 * @param string $currency The currency of this payment. It's a 3-letter code. E.g. 'USD'.
	 * @return void
	 */
	public function set_currency($currency) {

		$this->currency = $currency;

	} // end set_currency;

	/**
	 * Get value before taxes, discounts, fees and etc.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_subtotal() {

		return $this->subtotal;

	} // end get_subtotal;

	/**
	 * Set value before taxes, discounts, fees and etc.
	 *
	 * @since 2.0.0
	 * @param float $subtotal Value before taxes, discounts, fees and other changes.
	 * @return void
	 */
	public function set_subtotal($subtotal) {

		$this->subtotal = $subtotal;

	} // end set_subtotal;

	/**
	 * Get refund total in this payment.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_refund_total() {

		return $this->refund_total;

	} // end get_refund_total;

	/**
	 * Set refund total in this payment.
	 *
	 * @since 2.0.0
	 * @param float $refund_total Total amount refunded.
	 * @return void
	 */
	public function set_refund_total($refund_total) {

		$this->refund_total = $refund_total;

	} // end set_refund_total;

	/**
	 * Get the amount, in currency, of the tax.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_tax_total() {

		return (float) $this->tax_total;

	} // end get_tax_total;

	/**
	 * Set the amount, in currency, of the tax.
	 *
	 * @since 2.0.0
	 * @param float $tax_total The amount, in currency, of the tax.
	 * @return void
	 */
	public function set_tax_total($tax_total) {

		$this->tax_total = $tax_total;

	} // end set_tax_total;

	/**
	 * Get discount code used.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_discount_code() {

		return $this->discount_code;

	} // end get_discount_code;

	/**
	 * Set discount code used.
	 *
	 * @since 2.0.0
	 * @param string $discount_code Discount code used.
	 * @return void
	 */
	public function set_discount_code($discount_code) {

		$this->discount_code = $discount_code;

	} // end set_discount_code;

	/**
	 * Get this takes into account fees, discounts, credits, etc.
	 *
	 * @since 2.0.0
	 * @return float
	 */
	public function get_total() {

		return (float) $this->total;

	} // end get_total;

	/**
	 * Set this takes into account fees, discounts, credits, etc.
	 *
	 * @since 2.0.0
	 * @param float $total This takes into account fees, discounts and credits.
	 * @return void
	 */
	public function set_total($total) {

		$this->total = $total;

	} // end set_total;

	/**
	 * Returns the Label for a given severity level.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_status_label() {

		$status = new Payment_Status($this->get_status());

		return $status->get_label();

	} // end get_status_label;

	/**
	 * Gets the classes for a given class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_status_class() {

		$status = new Payment_Status($this->get_status());

		return $status->get_classes();

	} // end get_status_class;

	/**
	 * Get status of the status.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_status() {

		return $this->status;

	} // end get_status;

	/**
	 * Set status of the status.
	 *
	 * @since 2.0.0
	 * @param string $status The payment status: Can be 'pending', 'completed', 'refunded', 'partially-refunded', 'partially-paid', 'failed', 'cancelled' or other values added by third-party add-ons.
	 * @options \WP_Ultimo\Database\Payments\Payment_Status
	 * @return void
	 */
	public function set_status($status) {

		$this->status = $status;

	} // end set_status;

	/**
	 * Get gateway used to process this payment.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_gateway() {

		return $this->gateway;

	} // end get_gateway;

	/**
	 * Set gateway used to process this payment.
	 *
	 * @since 2.0.0
	 * @param string $gateway ID of the gateway being used on this payment.
	 * @return void
	 */
	public function set_gateway($gateway) {

		$this->gateway = $gateway;

	} // end set_gateway;

	/**
	 * Returns the payment method used. Usually it is the public name of the gateway.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_payment_method() {

		$gateway = $this->get_gateway();

		if (!$gateway) {

			return __('None', 'wp-ultimo');

		} // end if;

		$gateway_class = wu_get_gateway($gateway);

		if (!$gateway_class) {

			return __('None', 'wp-ultimo');

		} // end if;

		$title = $gateway_class->get_public_title() ? $gateway_class->get_public_title() : $gateway_class->title;

		return apply_filters("wu_gateway_{$gateway}_as_option_title", $title, $gateway_class);

	} // end get_payment_method;

	/**
	 * Returns the product associated to this payment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Product|false
	 */
	public function get_product() {

		return wu_get_product($this->product_id);

	} // end get_product;

	/**
	 * Checks if this payment has line items.
	 *
	 * This is used to decide if we need to add the payment as a line-item of itself.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_line_items() {

		return !empty($this->get_line_items());

	} // end has_line_items;

	/**
	 * Returns the line items for this payment.
	 *
	 * Line items are also \WP_Ultimo\Models\Payment objects, with the
	 * type 'line-item'.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_line_items() {

		if ($this->line_items === null) {

			$this->line_items = $this->get_meta('wu_line_items');

		} // end if;

		return (array) $this->line_items;

	} // end get_line_items;

	/**
	 * Set the line items of this payment.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Line_Item[] $line_items THe line items.
	 * @return void
	 */
	public function set_line_items($line_items) {

		$this->meta['wu_line_items'] = $line_items;

		$this->line_items = $line_items;

	} // end set_line_items;

	/**
	 * Add a new line item.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Checkout\Line_Item $line_item The line item.
	 * @return void
	 */
	public function add_line_item($line_item) {

		$line_items = $this->get_line_items();

		if (!is_a($line_item, '\WP_Ultimo\Checkout\Line_Item')) {

			return;

		} // end if;

		$line_items[$line_item->get_id()] = $line_item;

		$this->set_line_items($line_items);

		krsort($this->line_items);

	} // end add_line_item;

	/**
	 * Returns an array containing the subtotal per tax rate.
	 *
	 * @since 2.0.0
	 * @return array $tax_rate => $tax_total.
	 */
	public function get_tax_breakthrough() {

		$line_items = $this->get_line_items();

		$tax_brackets = array();

		foreach ($line_items as $line_item) {

			$tax_bracket = $line_item->get_tax_rate();

			if (!$tax_bracket) {

				continue;

			} // end if;

			if (isset($tax_brackets[$tax_bracket])) {

				$tax_brackets[$tax_bracket] += $line_item->get_tax_total();

				continue;

			} // end if;

			$tax_brackets[$tax_bracket] = $line_item->get_tax_total();

		} // end foreach;

		return $tax_brackets;

	} // end get_tax_breakthrough;

	/**
	 * Recalculate payment totals.
	 *
	 * @todo needs refactoring to use line_items.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Payment
	 */
	public function recalculate_totals() {

		$line_items = $this->get_line_items();

		$tax_total = 0;

		$sub_total = 0;

		$refund_total = 0;

		$total = 0;

		foreach ($line_items as $line_item) {

			$line_item->recalculate_totals();

			$tax_total += $line_item->get_tax_total();

			$sub_total += $line_item->get_subtotal();

			$total += $line_item->get_total();

			if ($line_item->get_type() === 'refund') {

				$refund_total += $line_item->get_subtotal();

			} // end if;

		} // end foreach;

		$this->attributes(array(
			'tax_total'    => $tax_total,
			'subtotal'     => $sub_total,
			'refund_total' => $refund_total,
			'total'        => $total,
		));

		return $this;

	} // end recalculate_totals;

	/**
	 * Checks if this payment is payable still.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_payable() {

		$payable_statuses = apply_filters('wu_payment_payable_statuses', array(
			Payment_Status::PENDING,
			Payment_Status::FAILED,
		));

		return $this->get_total() > 0 && in_array($this->get_status(), $payable_statuses, true);

	} // end is_payable;

	/**
	 * Returns the link to pay for this payment.
	 *
	 * @since 2.0.0
	 * @return false|string Returns false if the payment is not in a payable status.
	 */
	public function get_payment_url() {

		if (!$this->is_payable()) {

			return false;

		} // end if;

		return '#pay';

	} // end get_payment_url;

	/**
	 * Get iD of the product of this payment.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_product_id() {

		return $this->product_id;

	} // end get_product_id;

	/**
	 * Set iD of the product of this payment.
	 *
	 * @since 2.0.0
	 * @param int $product_id The ID of the product of this payment.
	 * @return void
	 */
	public function set_product_id($product_id) {

		$this->product_id = $product_id;

	} // end set_product_id;

	/**
	 * Generates the Invoice URL.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_invoice_url() {

		$url_atts = array(
			'action'    => 'invoice',
			'reference' => $this->get_hash(),
			'key'       => wp_create_nonce('see_invoice'),
		);

		return add_query_arg($url_atts, get_site_url(wu_get_main_site_id()));

	} // end get_invoice_url;

	/**
	 * Get iD of the payment on the gateway, if it exists.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_gateway_payment_id() {

		return $this->gateway_payment_id;

	} // end get_gateway_payment_id;

	/**
	 * Set iD of the payment on the gateway, if it exists.
	 *
	 * @since 2.0.0
	 * @param string $gateway_payment_id The ID of the payment on the gateway, if it exists.
	 * @return void
	 */
	public function set_gateway_payment_id($gateway_payment_id) {

		$this->gateway_payment_id = $gateway_payment_id;

	} // end set_gateway_payment_id;

	/**
	 * By default, we just use the to_array method, but you can rewrite this.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_search_results() {

		$search_result = $this->to_array();

		$search_result['reference_code'] = $this->get_hash();

		$line_items = array_map(function($line_item) {

			return $line_item->to_array();

		}, $this->get_line_items());

		$search_result['product_names'] = implode(', ', array_column($line_items, 'title'));

		return $search_result;

	} // end to_search_results;

	/**
	 * Get the total value in discounts.
	 *
	 * @since 2.0.0
	 * @return integer
	 */
	public function get_discount_total() {

		return (float) $this->discount_total;

	} // end get_discount_total;

	/**
	 * Set the total value in discounts.
	 *
	 * @since 2.0.0
	 * @param integer $discount_total The total value of the discounts applied to this payment.
	 * @return void
	 */
	public function set_discount_total($discount_total) {

		$this->discount_total = (float) $discount_total;

	} // end set_discount_total;

	/**
	 * Get the invoice number actually saved on the payment.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_saved_invoice_number() {

		if ($this->invoice_number === null) {

			$this->invoice_number = $this->get_meta('wu_invoice_number', '');

		} // end if;

		return $this->invoice_number;

	} // end get_saved_invoice_number;

	/**
	 * Get sequential invoice number assigned to this payment.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_invoice_number() {

		if (wu_get_setting('invoice_numbering_scheme', 'reference_code') === 'reference_code') {

			return $this->get_hash();

		} // end if;

		$provisional = false;

		if ($this->invoice_number === null) {

			$this->invoice_number = $this->get_meta('wu_invoice_number');

		} // end if;

		if ($this->invoice_number === false) {

			$provisional = true;

			$this->invoice_number = wu_get_setting('next_invoice_number');

		} // end if;

		$prefix = wu_get_setting('invoice_prefix', '');

		$search = array(
			'%%YEAR%%',
			'%%MONTH%%',
			'%%DAY%%',
		);

		$replace = array(
			gmdate('Y'),
			gmdate('m'),
			gmdate('d'),
		);

		$prefix = str_replace($search, $replace, $prefix);

		return sprintf('%s%s %s', $prefix, $this->invoice_number, $provisional ? __('(provisional)', 'wp-ultimo') : '');

	} // end get_invoice_number;

	/**
	 * Set sequential invoice number assigned to this payment.
	 *
	 * @since 2.0.0
	 * @param int $invoice_number Sequential invoice number assigned to this payment.
	 * @return void
	 */
	public function set_invoice_number($invoice_number) {

		$this->meta['wu_invoice_number'] = $invoice_number;

		$this->invoice_number = $invoice_number;

	} // end set_invoice_number;

	/**
	 * Remove all non-recurring items from the payment.
	 *
	 * This is usually used when creating a new pending payment for
	 * a membership that needs to be manually renewed.
	 *
	 * @since 2.0.0
	 * @return self
	 */
	public function remove_non_recurring_items() {

		$line_items = $this->get_line_items();

		foreach ($line_items as $line_item_id => $line_item) {

			if (!$line_item->is_recurring()) {

				unset($line_items[$line_item_id]);

			} // end if;

		} // end foreach;

		$this->set_line_items($line_items);

		$this->recalculate_totals();

		return $this;

	} // end remove_non_recurring_items;

	/**
	 * Get holds if we need to cancel the membership on refund..
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function should_cancel_membership_on_refund() {

		if ($this->cancel_membership_on_refund === null) {

			$this->cancel_membership_on_refund = $this->get_meta('wu_cancel_membership_on_refund', false);

		} // end if;

		return $this->cancel_membership_on_refund;

	} // end should_cancel_membership_on_refund;

	/**
	 * Set holds if we need to cancel the membership on refund..
	 *
	 * @since 2.0.0
	 * @param bool $cancel_membership_on_refund Holds if we need to cancel the membership on refund.
	 * @return void
	 */
	public function set_cancel_membership_on_refund($cancel_membership_on_refund) {

		$this->meta['wu_cancel_membership_on_refund'] = $cancel_membership_on_refund;

		$this->cancel_membership_on_refund = $cancel_membership_on_refund;

	} // end set_cancel_membership_on_refund;

	/**
	 * Handles a payment refund.
	 *
	 * This DOES NOT contact the gateway to refund a payment.
	 * It only updates the payment status to respond to a refund
	 * confirmation that originated from the gateway.
	 *
	 * An example of how that would work:
	 * 1. Admin issues a refund on the admin panel;
	 * 2. PayPal (for example), process the refund request
	 *    and sends back a IPN (webhook call) telling WP Ultimo
	 *    that the refund was issued successfully;
	 * 3. The IPN handler listens for that event and calls this
	 *    to reflect the refund in the original WU payment.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean      $amount The amount to refund.
	 * @param null|boolean $should_cancel_membership_on_refund If we should cancel a membership as well.
	 * @return void|bool
	 */
	public function refund($amount = false, $should_cancel_membership_on_refund = null) {
		/*
		 * If no amount was passed,
		 * refund the full amount.
		 */
		if (empty($amount)) {

			$amount = $this->get_total();

		} // end if;

		$amount = wu_to_float($amount);

		/*
		 * Do the same for the behavior regarding memberships.
		 */
		if (is_null($should_cancel_membership_on_refund)) {

			$should_cancel_membership_on_refund = $this->should_cancel_membership_on_refund();

		} // end if;

		/*
		 * First, deal with the status.
		 * The new status depends on the refund amount.
		 *
		 * If the amount is >= the total
		 * this is a total refund, otherwise,
		 * it is a partial refund.
		 */
		if ($amount >= $this->get_total()) {

			$title = __('Full Refund', 'wp-ultimo');

			$new_status = Payment_Status::REFUND;

		} else {

			$title = __('Partial Refund', 'wp-ultimo');

			$new_status = Payment_Status::PARTIAL_REFUND;

		} // end if;

		$time = current_time('timestamp'); // phpcs:ignore

		$formatted_value = date_i18n(get_option('date_format'), $time);

		// translators: %s is the date of processing.
		$description = sprintf(__('Processed on %s', 'wp-ultimo'), $formatted_value);

		$line_item_data = array(
			'type'         => 'refund',
			'hash'         => uniqid(),
			'title'        => $title,
			'description'  => $description,
			'discountable' => false,
			'taxable'      => false,
			'unit_price'   => -$amount,
			'quantity'     => 1,
		);

		$refund_line_item = new Line_Item($line_item_data);

		$this->add_line_item($refund_line_item);

		$this->set_status($new_status);

		$this->recalculate_totals();

		$status = $this->save();

		if (is_wp_error($status)) {

			return $status;

		} // end if;

		/**
		 * Updating the payment went well.
		 * Let's deal with the membership, if needed.
		 */
		if ($should_cancel_membership_on_refund) {

			$membership = $this->get_membership();

			if ($membership) {

				$membership->cancel();

			} // end if;

		} // end if;

		return true;

	} // end refund;

	/**
	 * Creates a copy of the given model adn resets it's id to a 'new' state.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Model\Base_Model
	 */
	public function duplicate() {

		$line_items = $this->get_line_items();

		$new_payment = parent::duplicate();

		$new_payment->set_line_items($line_items);

		return $new_payment;

	} // end duplicate;

} // end class Payment;
