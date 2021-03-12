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
class Signup_Field_Template_Selection extends Base_Signup_Field {

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_type() {

		return 'template_selection';

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

		return __('Template Selection', 'wp-ultimo');

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

		return __('Add a template selection field to the sign-up flow.', 'wp-ultimo');

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

		return 'dashicons-wu-dial-pad';

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
			'template_selection_sites'    => array(),
			'template_selection_template' => 'checkout/partials/legacy-template-selection',
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
			'id'       => 'template_selection',
			'required' => true,
		);

	} // end force_attributes;

	/**
	 * Returns the list of available pricing table templates.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_template_selection_templates() {

		$templates = array(
			'checkout/partials/legacy-template-selection' => __('Simple List', 'wp-ultimo'),
		);

		return apply_filters('wu_get_template_selection_templates', $templates);

	} // end get_template_selection_templates;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = array();

		$editor_fields['template_selection_sites'] = array(
			'type'        => 'model',
			'title'       => __('Template Sites', 'wp-ultimo'),
			'placeholder' => __('Template Sites', 'wp-ultimo'),
			'tooltip'     => '',
			'html_attr'   => array(
				'data-model'        => 'site',
				'data-value-field'  => 'blog_id',
				'data-label-field'  => 'title',
				'data-search-field' => 'title',
				'data-max-items'    => 10,
			),
		);

		$editor_fields['template_selection_template'] = array(
			'type'        => 'select',
			'title'       => __('Template Selector Template', 'wp-ultimo'),
			'placeholder' => __('Select your Template', 'wp-ultimo'),
			'options'     => array($this, 'get_template_selection_templates'),
		);

		$editor_fields['_dev_note_develop_your_own_template'] = array(
			'type' => 'note',
			'desc' => sprintf('<div class="wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded wu-w-full">%s</div>', __('Want to add customized site selection templates? <a href="#">See how you can do that here</a>.', 'wp-ultimo')),
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

		$site_list = explode(',', $attributes['template_selection_sites']);

		$sites = array_map('wu_get_site', $site_list);

		$sites = array_filter($sites);

		$content = wu_get_template_contents($attributes['template_selection_template'], array(
			'sites'      => $sites,
			'name'       => $attributes['name'],
			'categories' => \WP_Ultimo\Models\Site::get_all_categories(),
		));

		$checkout_fields[$attributes['id']] = array(
			'type' => 'note',
			'desc' => $content,
		);

		return $checkout_fields;

	} // end to_fields_array;

} // end class Signup_Field_Template_Selection;
