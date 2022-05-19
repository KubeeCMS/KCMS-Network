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
class Signup_Field_Order_Summary extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'order_summary';

	} // end get_type;

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_required() {

		return true;

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

		return __('Order Summary', 'wp-ultimo');

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

		return __('Adds a summary table with prices, key subscription dates, discounts, and taxes.', 'wp-ultimo');

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

		return __('Adds a summary table with prices, key subscription dates, discounts, and taxes.', 'wp-ultimo');

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

		return 'dashicons-wu-dollar-sign';

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
			'order_summary_template' => 'clean',
			'table_columns'          => 'simple',
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
			'id' => 'order_summary',
		);

	}  // end force_attributes;

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_templates() {

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('order_summary');

		return $available_templates;

	} // end get_templates;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = array();

		$editor_fields['table_columns'] = array(
			'type'    => 'select',
			'title'   => __('Table Columns', 'wp-ultimo'),
			'desc'    => __('"Simplified" will condense all discount and tax info into separate rows to keep the table with only two columns. "Display All" adds a discounts and taxes column to each product row.', 'wp-ultimo'),
			'options' => array(
				'simple' => __('Simplified', 'wp-ultimo'),
				'full'   => __('Display All', 'wp-ultimo'),
			)
		);

		$editor_fields['order_summary_template'] = array(
			'type'   => 'group',
			'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('order_summary'),
			'fields' => array(
				'order_summary_template' => array(
					'type'            => 'select',
					'title'           => __('Layout', 'wp-ultimo'),
					'placeholder'     => __('Select your Layout', 'wp-ultimo'),
					'options'         => array($this, 'get_templates'),
					'wrapper_classes' => 'wu-flex-grow',
					'html_attr'       => array(
						'v-model' => 'order_summary_template',
					),
				),
			),
		);

		// @todo: re-add developer notes.
		// $editor_fields['_dev_note_develop_your_own_template_order_summary'] = array(
		// 'type'            => 'note',
		// 'order'           => 99,
		// 'wrapper_classes' => 'sm:wu-p-0 sm:wu-block',
		// 'classes'         => '',
		// 'desc'            => sprintf('<div class="wu-p-4 wu-bg-blue-100 wu-text-grey-600">%s</div>', __('Want to add customized order summary templates?<br><a target="_blank" class="wu-no-underline" href="https://help.wpultimo.com/article/343-customize-your-checkout-flow-using-field-templates">See how you can do that here</a>.', 'wp-ultimo')),
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

		$checkout_fields = array();

		/*
		 * Backwards compatibility with previous betas
		 */
		if ($attributes['order_summary_template'] === 'simple') {

			$attributes['order_summary_template'] = 'clean';

		} // end if;

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('order_summary', $attributes['order_summary_template']);

		$content = $template_class ? $template_class->render_container($attributes) : __('Template does not exist.', 'wp-ultimo');

		$checkout_fields[$attributes['id']] = array(
			'type'              => 'note',
			'desc'              => $content,
			'wrapper_classes'   => wu_get_isset($attributes, 'wrapper_element_classes', ''),
			'classes'           => wu_get_isset($attributes, 'element_classes', ''),
			'wrapper_html_attr' => array(
				'style' => $this->calculate_style_attr(),
			),
		);

		return $checkout_fields;

	} // end to_fields_array;

} // end class Signup_Field_Order_Summary;
