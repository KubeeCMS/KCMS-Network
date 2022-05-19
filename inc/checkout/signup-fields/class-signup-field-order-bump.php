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
use \WP_Ultimo\Managers\Field_Templates_Manager;

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

		return __('Adds a product offer that the customer can click to add to the current cart.', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the tooltip of the field/element.
	 *
	 * This is used as the tooltip attribute of the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_tooltip() {

		return __('Adds a product offer that the customer can click to add to the current cart.', 'wp-ultimo');

	} // end get_tooltip;

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_icon() {

		return 'dashicons-wu-gift';

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
			'order_bump_template' => 'simple',
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
			// 'id',
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
			'order_bump_template' => 'simple',
		);

	} // end force_attributes;

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_templates() {

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('order_bump');

		return $available_templates;

	} // end get_templates;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = array(
			'product'               => array(
				'type'        => 'model',
				'title'       => __('Product', 'wp-ultimo'),
				'placeholder' => __('e.g. Premium', 'wp-ultimo'),
				'desc'        => __('Select the product that will be presented to the customer as an add-on option.', 'wp-ultimo'),
				'tooltip'     => '',
				'order'       => 12,
				'html_attr'   => array(
					'data-model'        => 'product',
					'data-value-field'  => 'id',
					'data-label-field'  => 'name',
					'data-search-field' => 'name',
					'data-max-items'    => 1,
				),
			),
			'display_product_image' => array(
				'order' => 14,
				'type'  => 'toggle',
				'title' => __('Display Product Image', 'wp-ultimo'),
				'desc'  => __('Toggle to display the product image as well, if one is available.', 'wp-ultimo'),
				'value' => 1,
			),
		);

		// $editor_fields['order_bump_template'] = array(
		// 'type'   => 'group',
		// 'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('order_bump'),
		// 'order'  => 98,
		// 'fields' => array(
		// 'order_bump_template' => array(
		// 'type'            => 'select',
		// 'title'           => __('Layout', 'wp-ultimo'),
		// 'placeholder'     => __('Select your Layout', 'wp-ultimo'),
		// 'options'         => array($this, 'get_templates'),
		// 'wrapper_classes' => 'wu-flex-grow',
		// 'html_attr'       => array(
		// 'v-model' => 'order_bump_template',
		// ),
		// ),
		// ),
		// );

		// @todo: re-add developer notes.
		// $editor_fields['_dev_note_develop_your_own_template_order_bump'] = array(
		// 'type'            => 'note',
		// 'order'           => 99,
		// 'wrapper_classes' => 'sm:wu-p-0 sm:wu-block',
		// 'classes'         => '',
		// 'desc'            => sprintf('<div class="wu-p-4 wu-bg-blue-100 wu-text-grey-600">%s</div>', __('Want to add customized order bump templates?<br><a target="_blank" class="wu-no-underline" href="https://help.wpultimo.com/article/343-customize-your-checkout-flow-using-field-templates">See how you can do that here</a>.', 'wp-ultimo')),
		// );

		return $editor_fields;

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

		$attributes['product'] = $product;

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('order_bump', $attributes['order_bump_template']);

		$content = $template_class ? $template_class->render_container($attributes) : __('Template does not exist.', 'wp-ultimo');

		return array(
			$attributes['id'] => array(
				'type'            => 'note',
				'desc'            => $content,
				'wrapper_classes' => $attributes['element_classes'],
			),
		);

	} // end to_fields_array;

} // end class Signup_Field_Order_Bump;
