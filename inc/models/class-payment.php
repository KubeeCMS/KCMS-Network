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
use \WP_Ultimo\Models\Customer;
use \WP_Ultimo\Models\Product;
use \WP_Ultimo\Database\Payments\Payment_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Payment model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Payment extends Base_Model {

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
	 * @var mixed
	 */
	protected $membership_id;

	/**
	 * Parent payment.
	 *
	 * @since 2.0.0
	 * @var mixed
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

		return array(
			'parent_id' => 'integer',
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
	 * @return mixed
	 */
	public function get_customer_id() {

		return $this->customer_id;

	} // end get_customer_id;

	/**
	 * Set the value of customer_id.
	 *
	 * @since 2.0.0
	 * @param mixed $customer_id ID of the customer attached to this payment.
	 * @return void
	 */
	public function set_customer_id($customer_id) {

		$this->customer_id = $customer_id;

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
	 * @return mixed
	 */
	public function get_membership_id() {

		return $this->membership_id;

	} // end get_membership_id;

	/**
	 * Set membership ID.
	 *
	 * @since 2.0.0
	 * @param mixed $membership_id Membership ID.
	 * @return void
	 */
	public function set_membership_id($membership_id) {

		$this->membership_id = $membership_id;

	} // end set_membership_id;

	/**
	 * Get parent payment ID.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_parent_id() {

		return $this->parent_id;

	} // end get_parent_id;

	/**
	 * Set parent payment ID.
	 *
	 * @since 2.0.0
	 * @param mixed $parent_id Parent payment.
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
	 * @param string $currency Currency for this payment. 3-letter currency code.
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
	 * @param float $subtotal Value before taxes, discounts, fees and etc.
	 * @return void
	 */
	public function set_subtotal($subtotal) {

		$this->subtotal = $subtotal;

	} // end set_subtotal;

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
	 * @param float $total This takes into account fees, discounts, credits, etc.
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
	 * @param string $status Status of the status.
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
	 * @param string $gateway Gateway used to process this payment.
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

		$total = 0;

		foreach ($line_items as $line_item) {

			$line_item->recalculate_totals();

			$tax_total += $line_item->get_tax_total();

			$sub_total += $line_item->get_subtotal();

			$total += $line_item->get_total();

		} // end foreach;

		$this->attributes(array(
			'tax_total' => $tax_total,
			'subtotal'  => $sub_total,
			'total'     => $total,
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
	 * @param int $product_id ID of the product of this payment.
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
	 * @param string $gateway_payment_id ID of the payment on the gateway, if it exists.
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
	 * @param integer $discount_total The total value in discounts.
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

} // end class Payment;
