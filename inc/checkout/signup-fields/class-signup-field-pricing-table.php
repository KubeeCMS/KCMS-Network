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
class Signup_Field_Pricing_Table extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'pricing_table';

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

		return __('Pricing Table', 'wp-ultimo');

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

		return __('Adds a pricing table section that customers can use to choose a plan to subscribe to.', 'wp-ultimo');

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

		return __('Adds a pricing table section that customers can use to choose a plan to subscribe to.', 'wp-ultimo');

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

		return 'dashicons-wu dashicons-wu-columns';

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
			'pricing_table_products'               => implode(',', array_keys(wu_get_plans_as_options())),
			'pricing_table_template'               => 'list',
			'force_different_durations'            => false,
			'hide_pricing_table_when_pre_selected' => false,
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
			// 'name',
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
			'id'       => 'pricing_table',
			'name'     => __('Plan Selection', 'wp-ultimo'),
			'required' => true,
		);

	} // end force_attributes;

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_pricing_table_templates() {

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('pricing_table');

		return $available_templates;

	} // end get_pricing_table_templates;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = array();

		$editor_fields['pricing_table_products'] = array(
			'type'        => 'model',
			'title'       => __('Products', 'wp-ultimo'),
			'placeholder' => __('e.g. Premium', 'wp-ultimo'),
			'desc'        => __('Be sure to add the products in the order you want them to show up.', 'wp-ultimo'),
			'tooltip'     => '',
			'order'       => 20,
			'html_attr'   => array(
				'data-model'        => 'product',
				'data-value-field'  => 'id',
				'data-label-field'  => 'name',
				'data-search-field' => 'name',
				'data-include'      => implode(',', array_keys(wu_get_plans_as_options())),
				'data-max-items'    => 999,
			),
		);

		$editor_fields['force_different_durations'] = array(
			'type'      => 'toggle',
			'title'     => __('Force Different Durations', 'wp-ultimo'),
			'desc'      => __('Check this option to force the display of plans with different recurring durations.', 'wp-ultimo'),
			'tooltip'   => '',
			'value'     => 0,
			'order'     => 22,
			'html_attr' => array(
				'v-model' => 'force_different_durations',
			),
		);

		$editor_fields['hide_pricing_table_when_pre_selected'] = array(
			'type'      => 'toggle',
			'title'     => __('Hide when Pre-Selected', 'wp-ultimo'),
			'desc'      => __('Prevent customers from seeing this field when a plan was already selected via the URL.', 'wp-ultimo'),
			'tooltip'   => __('If the pricing table field is the only field in the current step, the step will be skipped.', 'wp-ultimo'),
			'value'     => 0,
			'order'     => 24,
			'html_attr' => array(
				'v-model' => 'hide_pricing_table_when_pre_selected',
			),
		);

		$editor_fields['pricing_table_template'] = array(
			'type'   => 'group',
			'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('pricing_table'),
			'order'  => 26,
			'fields' => array(
				'pricing_table_template' => array(
					'type'            => 'select',
					'title'           => __('Pricing Table Template', 'wp-ultimo'),
					'placeholder'     => __('Select your Template', 'wp-ultimo'),
					'options'         => array($this, 'get_pricing_table_templates'),
					'wrapper_classes' => 'wu-flex-grow',
					'html_attr'       => array(
						'v-model' => 'pricing_table_template',
					),
				),
			),
		);

		// @todo: re-add developer notes.
		// $editor_fields['_dev_note_develop_your_own_template_2'] = array(
		// 'type'            => 'note',
		// 'order'           => 99,
		// 'wrapper_classes' => 'sm:wu-p-0 sm:wu-block',
		// 'classes'         => '',
		// 'desc'            => sprintf('<div class="wu-p-4 wu-bg-blue-100 wu-text-grey-600">%s</div>', __('Want to add customized pricing table templates?<br><a target="_blank" class="wu-no-underline" href="https://help.wpultimo.com/article/343-customize-your-checkout-flow-using-field-templates">See how you can do that here</a>.', 'wp-ultimo')),
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

		wp_enqueue_style('legacy-shortcodes', wu_get_asset('legacy-shortcodes.css', 'css'), array('dashicons'), wu_get_version());

		wp_add_inline_style('legacy-shortcodes', \WP_Ultimo\Checkout\Legacy_Checkout::get_instance()->get_legacy_dynamic_styles());

		$product_list = explode(',', $attributes['pricing_table_products']);

		$products = array_map('wu_get_product', $product_list);

		$products = array_filter($products, function($item) {

			/**
			 * Clear the product list out of invalid items and inactive products.
			 */
			return $item && $item->is_active();

		});

		/**
		 * Hide when pre-selected.
		 */
		if ($attributes['hide_pricing_table_when_pre_selected'] && wu_request('wu_preselected') === 'products') {

			return array();

		} // end if;

		$template_attributes = array(
			'products'                  => $products,
			'name'                      => $attributes['name'],
			'force_different_durations' => $attributes['force_different_durations'],
			'classes'                   => wu_get_isset($attributes, 'element_classes', ''),
		);

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('pricing_table', $attributes['pricing_table_template']);

		$content = $template_class ? $template_class->render_container($template_attributes) : __('Template does not exist.', 'wp-ultimo');

		$checkout_fields = array();

		$checkout_fields[$attributes['id']] = array(
			'type'              => 'note',
			'id'                => $attributes['id'],
			'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
			'classes'           => wu_get_isset($attributes, 'element_classes', ''),
			'desc'              => $content,
			'wrapper_html_attr' => array(
				'style' => $this->calculate_style_attr(),
			),
		);

		return $checkout_fields;

	} // end to_fields_array;

} // end class Signup_Field_Pricing_Table;
