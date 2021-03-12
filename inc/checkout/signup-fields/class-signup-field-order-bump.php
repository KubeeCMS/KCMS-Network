<?php
/**
 * Creates a cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Order
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields;

use \WP_Ultimo\Checkout\Signup_Fields\Base_Signup_Field;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
class Signup_Field_Order_Bump extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'order_bump';

	} // end get_type;

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_required() {

		return false;

	} // end is_required;

	/**
	 * Requires the title of the field/element type.
	 *
	 * This is used on the Field/Element selection screen.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Order Bump', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the description of the field/element.
	 *
	 * This is used as the title attribute of the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('PT Description', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_icon() {

		return 'dashicons-wu-price-tag';

	} // end get_icon;

	/**
	 * Returns the default values for the field-elements.
	 *
	 * This is passed through a wp_parse_args before we send the values
	 * to the method that returns the actual fields for the checkout form.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function defaults() {

		return array(
			''
		);

	} // end defaults;

	/**
	 * List of keys of the default fields we want to display on the builder.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function default_fields() {

		return array(
			'id',
			'name',
		);

	} // end default_fields;

	/**
	 * If you want to force a particular attribute to a value, declare it here.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function force_attributes() {

		return array(
			'required' => true,
		);

	}  // end force_attributes;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return array(
			'product'               => array(
				'type'        => 'model',
				'title'       => __('Product', 'wp-ultimo'),
				'placeholder' => __('Product', 'wp-ultimo'),
				'tooltip'     => '',
				'html_attr'   => array(
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 1,
				),
			),
			'display_product_image' => array(
				'type'    => 'toggle',
				'title'   => __('Display Product Image?', 'wp-ultimo'),
				'desc'    => __('Set as the primary domain.', 'wp-ultimo'),
				'tooltip' => __('Setting this as the primary domain will remove any other domain mapping marked as the primary domain for this site.', 'wp-ultimo'),
				'value'   => 1,
			),
		);

	} // end get_fields;

	/**
	 * Returns the field/element actual field array to be used on the checkout form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes Attributes saved on the editor form.
	 * @return array An array of fields, not the field itself.
	 */
	public function to_fields_array($attributes) {

		$product_id = $attributes['product'];

		$product = is_numeric($product_id) ? wu_get_product($product_id) : wu_get_product_by_slug($product_id);

		if (!$product) {

			return array();

		} // end if;

		$link = sprintf('<a class="button btn" href="#wu-checkout-add-%s">Add to Cart</a>', $product->get_slug());

		$image = '';

		if ($attributes['display_product_image']) {

			$image = $product->get_featured_image('thumbnail');

			if ($image) {

				$image = sprintf('<div class="wu-w-thumb wu-h-thumb wu-rounded wu-overflow-hidden wu-text-center wu-inline-block wu-mr-4">
					<img src="%s" class="wu-h-full">
				</div>', $image);

			} // end if;

		} // end if;

		$html = sprintf('<div class="wu-bg-gray-100 wu-my-2 wu-flex wu-px-4 wu-py-4 wu-border wu-border-solid wu-block wu-rounded wu-border-gray-400 wu-items-center wu-justify-between">
			<div class="wu-flex wu-items-center">
				%s
				<span>
					%s <span class="wu-block wu-text-xs">%s</span>
				</span>
			</div> 
			<div>%s</div>
			<input type="checkbox" style="display: none;" name="products[]" value="%s" v-model="products">
		</div>', $image, $attributes['name'], $product->get_price_description(), $link, $product->get_slug());

		return array(
			'order_bump' => array(
				'type' => 'note',
				'desc' => $html,
			),
		);

	} // end to_fields_array;

} // end class Signup_Field_Order_Bump;
