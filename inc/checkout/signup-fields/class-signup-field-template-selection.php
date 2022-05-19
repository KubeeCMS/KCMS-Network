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

		return __('Templates', 'wp-ultimo');

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

		return __('Adds a template selection section. This allows the customer to choose a pre-built site to be used as a template for the site being currently created.', 'wp-ultimo');

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

		return __('Adds a template selection section. This allows the customer to choose a pre-built site to be used as a template for the site being currently created.', 'wp-ultimo');

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

		return 'dashicons-wu-layout';

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
			'template_selection_sites'                  => implode(',', wu_get_site_templates(array('fields' => 'ids'))),
			'template_selection_template'               => 'clean',
			'cols'                                      => 3,
			'hide_template_selection_when_pre_selected' => false,
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
			'id'       => 'template_selection',
			'name'     => __('Template Selection', 'wp-ultimo'),
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

		$available_templates = Field_Templates_Manager::get_instance()->get_templates_as_options('template_selection');

		return $available_templates;

	} // end get_template_selection_templates;

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		$editor_fields = array();

		$editor_fields['cols'] = array(
			'type' => 'hidden',
		);

		$editor_fields['template_selection_sites'] = array(
			'type'        => 'model',
			'title'       => __('Template Sites', 'wp-ultimo'),
			'placeholder' => __('e.g. Template Site 1, My Agency', 'wp-ultimo'),
			'desc'        => __('Be sure to add the templates in the order you want them to show up.', 'wp-ultimo'),
			'order'       => 22,
			'html_attr'   => array(
				'v-model'           => 'template_selection_sites',
				'data-model'        => 'site',
				'data-value-field'  => 'blog_id',
				'data-label-field'  => 'title',
				'data-search-field' => 'title',
				'data-max-items'    => 999,
				'data-include'      => implode(',', wu_get_site_templates(array(
					'fields' => 'blog_id',
				))),
			),
		);

		$editor_fields['hide_template_selection_when_pre_selected'] = array(
			'type'      => 'toggle',
			'title'     => __('Hide when Pre-Selected', 'wp-ultimo'),
			'desc'      => __('Prevent customers from seeing this field when a template was already selected via the URL.', 'wp-ultimo'),
			'tooltip'   => __('If the template selection field is the only field in the current step, the step will be skipped.', 'wp-ultimo'),
			'value'     => 0,
			'order'     => 23,
			'html_attr' => array(
				'v-model' => 'hide_template_selection_when_pre_selected',
			),
		);

		$editor_fields['template_selection_template'] = array(
			'type'   => 'group',
			'order'  => 24,
			'desc'   => Field_Templates_Manager::get_instance()->render_preview_block('template_selection'),
			'fields' => array(
				'template_selection_template' => array(
					'type'            => 'select',
					'title'           => __('Template Selector Template', 'wp-ultimo'),
					'placeholder'     => __('Select your Template', 'wp-ultimo'),
					'options'         => array($this, 'get_template_selection_templates'),
					'wrapper_classes' => 'wu-flex-grow',
					'html_attr'       => array(
						'v-model' => 'template_selection_template',
					),
				),
			),
		);

		// @todo: re-add developer notes.
		// $editor_fields['_dev_note_develop_your_own_template_1'] = array(
		// 'type'            => 'note',
		// 'order'           => 99,
		// 'wrapper_classes' => 'sm:wu-p-0 sm:wu-block',
		// 'classes'         => '',
		// 'desc'            => sprintf('<div class="wu-p-4 wu-bg-blue-100 wu-text-grey-600">%s</div>', __('Want to add customized template selection templates?<br><a target="_blank" class="wu-no-underline" href="https://help.wpultimo.com/article/343-customize-your-checkout-flow-using-field-templates">See how you can do that here</a>.', 'wp-ultimo')),
		// );

		return $editor_fields;

	} // end get_fields;

	/**
	 * Treat the attributes array to avoid reaching the input var limits.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes The attributes.
	 * @return array
	 */
	public function reduce_attributes($attributes) {

		$array_sites = json_decode(json_encode($attributes['sites']), true);

		$attributes['sites'] = array_values(array_column($array_sites, 'blog_id'));

		return $attributes;

	} // end reduce_attributes;

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

		$checkout_fields['template_id'] = array(
			'type'      => 'hidden',
			'html_attr' => array(
				'v-model' => 'template_id',
			),
		);

		/**
		 * Hide when pre-selected.
		 */
		if ($attributes['hide_template_selection_when_pre_selected'] && wu_request('wu_preselected') === 'template_id') {

			return $checkout_fields;

		} // end if;

		wp_register_script('wu-legacy-signup', wu_get_asset('legacy-signup.js', 'js'), array('jquery', 'wu-functions'));

		wp_localize_script('wu-legacy-signup', 'wpu', array(
			'default_pricing_option' => 1,
		));

		wp_enqueue_script('wu-legacy-signup');

		wp_enqueue_style('legacy-shortcodes', wu_get_asset('legacy-shortcodes.css', 'css'), array('dashicons'));

		$site_list = explode(',', $attributes['template_selection_sites']);

		$sites = array_map('wu_get_site', $site_list);

		$sites = array_filter($sites);

		$template_attributes = array(
			'sites'      => $sites,
			'name'       => $attributes['name'],
			'cols'       => $attributes['cols'],
			'categories' => \WP_Ultimo\Models\Site::get_all_categories($sites),
		);

		$template_class = Field_Templates_Manager::get_instance()->get_template_class('template_selection', $attributes['template_selection_template']);

		$content = $template_class ? $template_class->render_container($template_attributes, $this) : __('Template does not exist.', 'wp-ultimo');

		$checkout_fields[$attributes['id']] = array(
			'type'            => 'note',
			'desc'            => $content,
			'wrapper_classes' => $attributes['element_classes'],
		);

		return $checkout_fields;

	} // end to_fields_array;

} // end class Signup_Field_Template_Selection;
