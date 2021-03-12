<?php
/**
 * The Product model.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

// Exit if accessed directly
defined('ABSPATH') || exit;

use WP_Ultimo\Models\Base_Model;
use WP_Ultimo\Database\Products\Product_Type;

/**
 * Product model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Product extends Base_Model {

	use Traits\Limitable, \WP_Ultimo\Traits\WP_Ultimo_Plan_Deprecated;

	/**
	 * The product name.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = '';

	/**
	 * The product slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The product description.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $description = '';

	/**
	 * Currency for this product. 3-letter currency code.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $currency = 'USD';

	/**
	 * The type of billing associated with this product.
	 *
	 * Can be one of 'free', 'paid', and 'contact_us'.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $pricing_type = 'paid';

	/**
	 * The product setup fee.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $setup_fee = 0;

	/**
	 * Product that this product relates to.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $parent_id;

	/**
	 * Is this product recurring?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $recurring = 1;

	/**
	 * Duration of the trial period.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $trial_duration = 0;

	/**
	 * Unit of the trial period duration.
	 *
	 * - day
	 * - week
	 * - month
	 * - year
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $trial_duration_unit = 'day';

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
	 * The product amount.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $amount = 0;

	/**
	 * The number of times we should charge this product.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $billing_cycles = 0;

	/**
	 * The product list order. Useful when ordering products in a list.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $list_order = 10;

	/**
	 * Is this product active?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $active = true;

	/**
	 * Type of the product.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $type = 'plan';

	/**
	 * Date when this was created.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_created = '0000-00-00 00:00:00';

	/**
	 * Date when this was last modified.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_modified = '0000-00-00 00:00:00';

	/**
	 * ID of the featured image being used on this product.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $feature_image_id;

	/**
	 * Is the product taxable?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $taxable = true;

	/**
	 * What is the tax category that should be used.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $tax_category;

	/**
	 * Feature list for pricing tables.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $feature_list;

	/**
	 * Customer role on sites under this plan.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $customer_role;

	/**
	 * Price variations.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $price_variations;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Products\\Product_Query';

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

		$id = $this->get_id();

		return array(
			'parent_id'        => 'integer',
			'amount'           => 'numeric|default:0',
			'duration'         => 'default:1',
			'billing_cycles'   => 'numeric|default:0',
			'active'           => 'default:1',
			'type'             => 'default:plan',
			'price_variations' => 'price_variations',
			'slug'             => "required|unique:\WP_Ultimo\Models\Product,slug,{$id}|min:3",
		);

	} // end validation_rules;

	/**
	 * Get featured image ID.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_featured_image_id() {

		if ($this->feature_image_id === null) {

			return $this->get_meta('wu_featured_image_id');

		} // end if;

		return $this->feature_image_id;

	} // end get_featured_image_id;

	/**
	 * Get featured image url.
	 *
	 * @since 2.0.0
	 * @param string $size The size of the image to retrieve.
	 * @return string
	 */
	public function get_featured_image($size = 'medium') {

		is_multisite() && switch_to_blog(wu_get_main_site_id());

		$image_attributes = wp_get_attachment_image_src($this->get_featured_image_id(), $size);

		is_multisite() && restore_current_blog();

		return $image_attributes ? $image_attributes[0] : '';

	} // end get_featured_image;

	/**
	 * Set featured image ID.
	 *
	 * @since 2.0.0
	 * @param int $image_id Holds the ID of the featured image.
	 * @return void
	 */
	public function set_featured_image_id($image_id) {

		$this->meta['wu_featured_image_id'] = $image_id;

		$this->feature_image_id = $image_id;

	} // end set_featured_image_id;

	/**
	 * Get the product slug.
	 *
	 * @return string
	 */
	public function get_slug() {

		return $this->slug;

	} // end get_slug;

	/**
	 * Set the slug name.
	 *
	 * @param string $slug The product slug.
	 */
	public function set_slug($slug) {

		$this->slug = $slug;

	} // end set_slug;

	/**
	 * Get the product name.
	 *
	 * @return string
	 */
	public function get_name() {

		return $this->name;

	} // end get_name;

	/**
	 * Set the product name.
	 *
	 * @param string $name The product name.
	 */
	public function set_name($name) {

		$this->name = $name;

	} // end set_name;

	/**
	 * Get the product description.
	 *
	 * @return string
	 */
	public function get_description() {

		return $this->description;

	} // end get_description;

	/**
	 * Set the product description.
	 *
	 * @param string $description The product description.
	 */
	public function set_description($description) {

		$this->description = $description;

	} // end set_description;

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
	 * Get can be one of 'free', 'paid', and 'contact_us'.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_pricing_type() {

		return $this->pricing_type;

	} // end get_pricing_type;

	/**
	 * Set pricing type can be one of 'free', 'paid', and 'contact_us'.
	 *
	 * @since 2.0.0
	 * @param string $pricing_type Can be one of 'free', 'paid', and 'contact_us'.
	 * @return void
	 */
	public function set_pricing_type($pricing_type) {

		$this->pricing_type = $pricing_type;

		if ($pricing_type === 'free') {

			$this->amount = 0;

			$this->recurring = false;

		} // end if;

	} // end set_pricing_type;

	/**
	 * Checks if a given product offers a trial period.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_trial() {

		return $this->get_trial_duration() > 0;

	} // end has_trial;

	/**
	 * Get duration of the trial.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_trial_duration() {

		return $this->trial_duration;

	} // end get_trial_duration;

	/**
	 * Set duration of the trial.
	 *
	 * @since 2.0.0
	 * @param int $trial_duration Duration of the trial.
	 * @return void
	 */
	public function set_trial_duration($trial_duration) {

		$this->trial_duration = $trial_duration;

	} // end set_trial_duration;

	/**
	 * Get the trial duration unit.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_trial_duration_unit() {

		return $this->trial_duration_unit;

	} // end get_trial_duration_unit;

	/**
	 * Set the trial duration unit.
	 *
	 * @since 2.0.0
	 * @param string $trial_duration_unit The trial duration unit.
	 * @return void
	 */
	public function set_trial_duration_unit($trial_duration_unit) {

		$this->trial_duration_unit = $trial_duration_unit;

	} // end set_trial_duration_unit;

	/**
	 * Get time interval between charges.
	 *
	 * @return int
	 */
	public function get_duration() {

		return $this->duration;

	} // end get_duration;

	/**
	 * Set time interval between charges.
	 *
	 * @param int $duration Time interval between charges.
	 */
	public function set_duration($duration) {

		$this->duration = $duration;

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
	 * Set the product amount.
	 *
	 * @param int $amount The product amount.
	 */
	public function set_amount($amount) {

		$this->amount = wu_to_float($amount);

	} // end set_amount;

	/**
	 * Get the product setup fee..
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_setup_fee() {

		return $this->setup_fee;

	} // end get_setup_fee;

	/**
	 * Set the product setup fee..
	 *
	 * @since 2.0.0
	 * @param int $setup_fee The product setup fee.
	 * @return void
	 */
	public function set_setup_fee($setup_fee) {

		$this->setup_fee = wu_to_float($setup_fee);

	} // end set_setup_fee;

	/**
	 * Checks if a given product haw a setup fee.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_setup_fee() {

		return $this->get_setup_fee() > 0;

	} // end has_setup_fee;

	/**
	 * Get the product initial amount
	 *
	 * @return int
	 */
	public function get_initial_amount() {

		return $this->get_amount() + $this->get_setup_fee();

	} // end get_initial_amount;

	/**
	 * Returns the product price structure in a way human can understand it.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $include_fees If we need to include fees.
	 * @return string
	 */
	public function get_price_description($include_fees = true) {

		$pricing = array();

		if ($this->is_free()) {

			return __('Free!', 'wp-ultimo');

		} // end if;

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

		if ($this->has_setup_fee() && $include_fees) {

			$pricing['fee'] = sprintf(
				// translators: %1$s is the formatted price of the setup fee
				__('Setup Fee of %1$s', 'wp-ultimo'),
				wu_format_currency($this->get_setup_fee(), $this->get_currency())
			);

		} // end if;

		return implode(' + ', $pricing);

	} // end get_price_description;

	/**
	 * Get the product list order. Useful when ordering products in a list.
	 *
	 * @return int
	 */
	public function get_list_order() {

		return $this->list_order;

	} // end get_list_order;

	/**
	 * Set the product list order. Useful when ordering products in a list.
	 *
	 * @param int $list_order The product list order. Useful when ordering products in a list.
	 */
	public function set_list_order($list_order) {

		$this->list_order = $list_order;

	} // end set_list_order;

	/**
	 * Get is this product active?.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_active() {

		return (bool) $this->active;

	} // end is_active;

	/**
	 * Set is this product active?
	 *
	 * @since 2.0.0
	 * @param boolean $active Is this product active.
	 * @return void
	 */
	public function set_active($active) {

		$this->active = (bool) $active;

	} // end set_active;

	/**
	 * Get type of the product.
	 *
	 * @return boolean
	 */
	public function get_type() {

		return $this->type;

	} // end get_type;

	/**
	 * Set type of the product.
	 *
	 * @param boolean $type Type of the product.
	 */
	public function set_type($type) {

		$this->type = $type;

	} // end set_type;

	/**
	 * Returns the Label for a given type.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type_label() {

		$type = new Product_Type($this->get_type());

		return $type->get_label();

	} // end get_type_label;

	/**
	 * Gets the classes for a given class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type_class() {

		$type = new Product_Type($this->get_type());

		return $type->get_classes();

	} // end get_type_class;

	/**
	 * Get product that this product relates to..
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function get_parent_id() {

		return $this->parent_id;

	} // end get_parent_id;

	/**
	 * Set product that this product relates to..
	 *
	 * @since 2.0.0
	 * @param boolean $parent_id Product that this product relates to.
	 * @return void
	 */
	public function set_parent_id($parent_id) {

		$this->parent_id = $parent_id;

	} // end set_parent_id;

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

		return empty($this->get_amount()) && empty($this->get_initial_amount());

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
	 * Get the number of times we should charge this product.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_billing_cycles() {

		return (int) $this->billing_cycles;

	} // end get_billing_cycles;

	/**
	 * Checks if this product recurs forever.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_forever_recurring() {

		return empty($this->get_billing_cycles()) || $this->get_billing_cycles() <= 0;

	} // end is_forever_recurring;

	/**
	 * Set the number of times we should charge this product.
	 *
	 * @since 2.0.0
	 * @param int $billing_cycles The number of times we should charge this product.
	 * @return void
	 */
	public function set_billing_cycles($billing_cycles) {

		$this->billing_cycles = (int) $billing_cycles;

	} // end set_billing_cycles;

	/**
	 * Get date when this was created..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_created() {

		return $this->date_created;

	} // end get_date_created;

	/**
	 * Set date when this was created..
	 *
	 * @since 2.0.0
	 * @param string $date_created Date when this was created.
	 * @return void
	 */
	public function set_date_created($date_created) {

		$this->date_created = $date_created;

	} // end set_date_created;

	/**
	 * Get date when this was last modified..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_modified() {

		return $this->date_modified;

	} // end get_date_modified;

	/**
	 * Set date when this was last modified..
	 *
	 * @since 2.0.0
	 * @param string $date_modified Date when this was last modified.
	 * @return void
	 */
	public function set_date_modified($date_modified) {

		$this->date_modified = $date_modified;

	} // end set_date_modified;

	/**
	 * By default, we just use the to_array method, but you can rewrite this.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_search_results() {

		$image = $this->get_featured_image('thumbnail');

		$search_result = $this->to_array();

		$search_result['type']            = $this->get_type_label();
		$search_result['formatted_price'] = $this->get_price_description();
		$search_result['image']           = $image ? sprintf('<img class="wu-rounded wu-mr-3" height="40" width="40" src="%s">', esc_attr($image)) : '';

		return $search_result;

	} // end to_search_results;

	/**
	 * Get products to show as cross-sell options.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_cross_sell_options() {

		$array = array_map('wu_get_product_by_slug', array('seo', 'brand', 'content'));

		return array_filter($array);

	} // end get_cross_sell_options;

	// Secondary Info, to be saved as meta

	/**
	 * Checks if the product is taxable.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_taxable() {

		$is_taxable = (bool) $this->get_meta('taxable', true);

		return apply_filters('wu_product_is_taxable', $is_taxable, $this);

	} // end is_taxable;

	/**
	 * Sets the taxable status of the product.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $is_taxable The new taxable status.
	 * @return void
	 */
	public function set_taxable($is_taxable) {

		$this->meta['taxable'] = (bool) $is_taxable;

		$this->taxable = $this->meta['taxable'];

	} // end set_taxable;

	/**
	 * Returns the tax category to apply.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_tax_category() {

		if ($this->tax_category === null) {

			$this->tax_category = $this->get_meta('tax_category', 'default');

		} // end if;

		return apply_filters('wu_product_tax_category', $this->tax_category, $this);

	}  // end get_tax_category;

	/**
	 * Sets the tax category to apply.
	 *
	 * @since 2.0.0
	 *
	 * @param string $tax_category The slug of the new tax category.
	 * @return void
	 */
	public function set_tax_category($tax_category) {

		$this->meta['tax_category'] = $tax_category;

		$this->tax_category = $this->meta['tax_category'];

	} // end set_tax_category;

	/**
	 * Get feature list for pricing tables..
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_feature_list() {

		if ($this->feature_list === null) {

			$this->feature_list = $this->get_meta('feature_list');

		} // end if;

		return $this->feature_list;

	} // end get_feature_list;

	/**
	 * Set feature list for pricing tables..
	 *
	 * @since 2.0.0
	 * @param array $feature_list Feature list for pricing tables.
	 * @return void
	 */
	public function set_feature_list($feature_list) {

		$this->meta['feature_list'] = $feature_list;

		$this->feature_list = $this->meta['feature_list'];

	} // end set_feature_list;

	/**
	 * Get the customer role to force customers to be on this plan.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_customer_role() {

		if ($this->customer_role === null) {

			$this->customer_role = $this->get_meta('customer_role', 'administrator');

		} // end if;

		return $this->customer_role;

	} // end get_customer_role;

	/**
	 * Set the customer role to force customers to be on this plan.
	 *
	 * @since 2.0.0
	 * @param array $customer_role Feature list for pricing tables.
	 * @return void
	 */
	public function set_customer_role($customer_role) {

		$this->meta['customer_role'] = $customer_role;

		$this->customer_role = $this->meta['customer_role'];

	} // end set_customer_role;

	/**
	 * Returns the price variations for this product.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_price_variations() {

		if ($this->price_variations === null) {

			$this->price_variations = $this->get_meta('price_variations', array());

		} // end if;

		return $this->price_variations;

	} // end get_price_variations;

	/**
	 * Sets the new price variations.
	 *
	 * @since 2.0.0
	 * @param array $price_variations Price variations array.
	 * @return void
	 */
	public function set_price_variations($price_variations) {

		$this->meta['price_variations'] = $price_variations;

		$this->price_variations = $this->meta['price_variations'];

	} // end set_price_variations;

	/**
	 * Get a particular price variation.
	 *
	 * @since 2.0.0
	 *
	 * @param int    $duration The duration.
	 * @param string $duration_unit The duration unit.
	 * @return object
	 */
	public function get_price_variation($duration, $duration_unit) {

		$price_variations = $this->get_price_variations();

		if ($duration === 12 && $duration_unit === 'month') {

			$duration = 1;

			$duration_unit = 'year';

		} // end if;

		foreach ($price_variations as $pv) {

			if (abs($pv['duration']) === abs($duration) && $pv['duration_unit'] === $duration_unit) {

				$pv['amount'] = wu_to_float($pv['amount']);

				$pv['monthly_amount'] = $pv['amount'] / (wu_convert_duration_unit_to_month($duration_unit) * $pv['duration']);

				return (object) $pv;

			} // end if;

		} // end foreach;

		return false;

	} // end get_price_variation;

	/**
	 * Save (create or update) the model on the database.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function save() {

		if (empty($this->slug) && $this->name) {

			$this->set_slug(sanitize_title($this->name));

		} // end if;

		$results = parent::save();

		if (!is_wp_error($results) && has_action('wu_save_plan')) {

			$compat_plan = new \WU_Plan($this);

			do_action_deprecated('wu_save_plan', array($compat_plan), '2.0.0', 'wu_product_post_save');

		} // end if;

		return $results;

	} // end save;

} // end class Product;
