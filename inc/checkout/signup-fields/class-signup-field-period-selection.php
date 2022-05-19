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
class Signup_Field_Period_Selection extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'period_selection';

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

		return __('Period Select', 'wp-ultimo');

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

		return __('Adds a period selector, that allows customers to switch between different billing periods.', 'wp-ultimo');

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

		return __('Adds a period selector, that allows customers to switch between different billing periods.', 'wp-ultimo');

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

		return 'dashicons-wu dashicons-wu-toggle-right';

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
			'period_selection_template' => 'clean',
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
			'id'       => 'period_selection',
			'name'     => __('Plan Duration Switch', 'wp-ultimo'),
			'required' => true,
		);

	} // end force_attributes;

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_template_options() {

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('period_selection');

		return $available_templates;

	} // end get_template_options;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = array();

		$editor_fields['period_selection_template'] = array(
			'type'   => 'group',
			'order'  => 98.4,
			'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('period_selection'),
			'fields' => array(
				'period_selection_template' => array(
					'type'            => 'select',
					'title'           => __('Period Selector Template', 'wp-ultimo'),
					'placeholder'     => __('Select your Template', 'wp-ultimo'),
					'options'         => array($this, 'get_template_options'),
					'wrapper_classes' => 'wu-flex-grow',
					'html_attr'       => array(
						'v-model' => 'period_selection_template',
					),
				),
			),
		);

		$editor_fields['period_options_header'] = array(
			'type'  => 'small-header',
			'title' => __('Options', 'wp-ultimo'),
			'desc'  => __('Add different options below. These need to match your product price variations.', 'wp-ultimo'),
			'order' => 90,
		);

		$editor_fields['period_options_empty'] = array(
			'type'              => 'note',
			'desc'              => __('Add the first option using the button below.', 'wp-ultimo'),
			'classes'           => 'wu-text-gray-600 wu-text-xs wu-text-center wu-w-full',
			'wrapper_classes'   => 'wu-bg-gray-100 wu-items-end',
			'order'             => 90.5,
			'wrapper_html_attr' => array(
				'v-if'    => 'period_options.length === 0',
				'v-cloak' => '1',
			),
		);

		$editor_fields['period_options'] = array(
			'type'              => 'group',
			'tooltip'           => '',
			'order'             => 91,
			'wrapper_classes'   => 'wu-relative wu-bg-gray-100 wu-pb-2',
			'wrapper_html_attr' => array(
				'v-if'    => 'period_options.length',
				'v-for'   => '(period_option, index) in period_options',
				'v-cloak' => '1',
			),
			'fields'            => array(
				'period_options_remove'        => array(
					'type'            => 'note',
					'desc'            => sprintf('<a title="%s" class="wu-no-underline wu-inline-block wu-text-gray-600 wu-mt-2 wu-mr-2" href="#" @click.prevent="() => period_options.splice(index, 1)"><span class="dashicons-wu-squared-cross"></span></a>', __('Remove', 'wp-ultimo')),
					'wrapper_classes' => 'wu-absolute wu-top-0 wu-right-0',
				),
				'period_options_duration'      => array(
					'type'            => 'number',
					'title'           => __('Duration', 'wp-ultimo'),
					'placeholder'     => '',
					'wrapper_classes' => 'wu-w-2/12',
					'min'             => 1,
					'html_attr'       => array(
						'v-model'     => 'period_option.duration',
						'steps'       => 1,
						'v-bind:name' => '"period_options[" + index + "][duration]"',
					),
				),
				'period_options_duration_unit' => array(
					'type'            => 'select',
					'title'           => '&nbsp',
					'placeholder'     => '',
					'wrapper_classes' => 'wu-w-5/12 wu-mx-2',
					'html_attr'       => array(
						'v-model'     => 'period_option.duration_unit',
						'v-bind:name' => '"period_options[" + index + "][duration_unit]"',
					),
					'options'         => array(
						'day'   => __('Days', 'wp-ultimo'),
						'week'  => __('Weeks', 'wp-ultimo'),
						'month' => __('Months', 'wp-ultimo'),
						'year'  => __('Years', 'wp-ultimo'),
					),
				),
				'period_options_label'         => array(
					'type'            => 'text',
					'title'           => __('Label', 'wp-ultimo'),
					'placeholder'     => __('e.g. Monthly', 'wp-ultimo'),
					'wrapper_classes' => 'wu-w-5/12',
					'html_attr'       => array(
						'v-model'     => 'period_option.label',
						'v-bind:name' => '"period_options[" + index + "][label]"',
					),
				),
			),
		);

		$editor_fields['repeat'] = array(
			'order'             => 92,
			'type'              => 'submit',
			'title'             => __('+ Add option', 'wp-ultimo'),
			'classes'           => 'wu-uppercase wu-text-2xs wu-text-blue-700 wu-border-none wu-bg-transparent wu-font-bold wu-text-right wu-w-full wu-cursor-pointer',
			'wrapper_classes'   => 'wu-bg-gray-100 wu-items-end',
			'wrapper_html_attr' => array(
				'v-cloak' => '1',
			),
			'html_attr'         => array(
				'v-on:click.prevent' => '() => period_options.push({
					duration: 1,
					duration_unit: "month",
					label: "",
				})',
			),
		);

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

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('period_selection', $attributes['period_selection_template']);

		$content = $template_class ? $template_class->render_container($attributes) : __('Template does not exist.', 'wp-ultimo');

		$checkout_fields = array();

		$checkout_fields[$attributes['id']] = array(
			'type'            => 'note',
			'id'              => $attributes['id'],
			'wrapper_classes' => $attributes['element_classes'],
			'desc'            => $content,
		);

		$checkout_fields['duration'] = array(
			'type'      => 'hidden',
			'html_attr' => array(
				'v-model' => 'duration',
			),
		);

		$checkout_fields['duration_unit'] = array(
			'type'      => 'hidden',
			'html_attr' => array(
				'v-model' => 'duration_unit',
			),
		);

		return $checkout_fields;

	} // end to_fields_array;

} // end class Signup_Field_Period_Selection;
