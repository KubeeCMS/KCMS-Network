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
class Signup_Field_Select extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'select';

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

		return __('Select', 'wp-ultimo');

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

		return __('Adds a select field.', 'wp-ultimo');

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

		return __('Adds a select field.', 'wp-ultimo');

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

		return 'dashicons-wu-list1';

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
			'placeholder',
			'default_value',
			'tooltip',
			'required',
			'save_as',
		);

	} // end default_fields;

	/**
	 * If you want to force a particular attribute to a value, declare it here.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function force_attributes() {

		return array();

	} // end force_attributes;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = array();

		$editor_fields['options_header'] = array(
			'order' => 12,
			'type'  => 'small-header',
			'title' => __('Options', 'wp-ultimo'),
			'desc'  => __('Add different options below. The first option is used as the default.', 'wp-ultimo'),
		);

		$editor_fields['options_empty'] = array(
			'type'              => 'note',
			'desc'              => __('Add the first option using the button below.', 'wp-ultimo'),
			'classes'           => 'wu-text-gray-600 wu-text-xs wu-text-center wu-w-full',
			'wrapper_classes'   => 'wu-bg-gray-100 wu-items-end',
			'order'             => 13,
			'wrapper_html_attr' => array(
				'v-if'    => 'options.length === 0',
				'v-cloak' => '1',
			),
		);

		$editor_fields['options'] = array(
			'order'             => 14,
			'type'              => 'group',
			'tooltip'           => '',
			'wrapper_classes'   => 'wu-relative wu-bg-gray-100',
			'wrapper_html_attr' => array(
				'v-if'    => 'options.length',
				'v-for'   => '(option, index) in options',
				'v-cloak' => '1',
			),
			'fields'            => array(
				'options_remove' => array(
					'type'            => 'note',
					'desc'            => sprintf('<a title="%s" class="wu-no-underline wu-inline-block wu-text-gray-600 wu-mt-2 wu-mr-2" href="#" @click.prevent="() => options.splice(index, 1)"><span class="dashicons-wu-squared-cross"></span></a>', __('Remove', 'wp-ultimo')),
					'wrapper_classes' => 'wu-absolute wu-top-0 wu-right-0',
				),
				'options_key'    => array(
					'type'            => 'text',
					'title'           => __('Value', 'wp-ultimo'),
					'placeholder'     => __('e.g. option1', 'wp-ultimo'),
					'wrapper_classes' => 'wu-w-1/2 wu-mr-2',
					'html_attr'       => array(
						'v-model'     => 'option.key',
						'steps'       => 1,
						'v-bind:name' => '"options[" + index + "][key]"',
					),
				),
				'options_label'  => array(
					'type'            => 'text',
					'title'           => __('Label', 'wp-ultimo'),
					'placeholder'     => __('e.g. Option 1', 'wp-ultimo'),
					'wrapper_classes' => 'wu-w-1/2 wu-ml-2',
					'html_attr'       => array(
						'v-model'     => 'option.label',
						'v-bind:name' => '"options[" + index + "][label]"',
					),
				),
			),
		);

		$editor_fields['repeat_select_option'] = array(
			'order'             => 16,
			'type'              => 'submit',
			'title'             => __('+ Add option', 'wp-ultimo'),
			'classes'           => 'wu-uppercase wu-text-2xs wu-text-blue-700 wu-border-none wu-bg-transparent wu-font-bold wu-text-right wu-w-full wu-cursor-pointer',
			'wrapper_classes'   => 'wu-bg-gray-100 wu-items-end',
			'wrapper_html_attr' => array(
				'v-cloak' => '1',
			),
			'html_attr'         => array(
				'type'               => 'button',
				'v-on:click.prevent' => '() => options.push({})',
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

		$options = array();

		foreach ($attributes['options'] as $_option) {

			$options[$_option['key']] = $_option['label'];

		} // end foreach;

		return array(
			$attributes['id'] => array(
				'type'            => 'select',
				'id'              => $attributes['id'],
				'name'            => $attributes['name'],
				'placeholder'     => $attributes['placeholder'],
				'tooltip'         => $attributes['tooltip'],
				'default'         => $attributes['default_value'],
				'required'        => $attributes['required'],
				'wrapper_classes' => $attributes['element_classes'],
				'options'         => $options,
				'value'           => $this->get_value(),
			),
		);

	} // end to_fields_array;

} // end class Signup_Field_Select;
