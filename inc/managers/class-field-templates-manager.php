<?php
/**
 * Field templates manager
 *
 * Keeps track of registered field templates.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Signup_Fields
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use \WP_Ultimo\Managers\Base_Manager;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Keeps track of registered field templates.
 *
 * @since 2.0.0
 */
class Field_Templates_Manager extends Base_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Initialize the managers with the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_ajax_nopriv_wu_render_field_template', array($this, 'serve_field_template'));

		add_action('wu_ajax_wu_render_field_template', array($this, 'serve_field_template'));

	} // end init;

	/**
	 * Serve the HTML markup for the templates.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function serve_field_template() {

		$template = wu_replace_dashes(wu_request('template'));

		$template_parts = explode('/', $template);

		$template_class = $this->get_template_class($template_parts[0], $template_parts[1]);

		if (!$template_class) {

			wp_send_json_error(new \WP_Error('template', __('Template not found.', 'wp-ultimo')));

		} // end if;

		$key = $template_parts[0];

		$attributes = apply_filters("wu_{$key}_render_attributes", wu_request('attributes'));

		wp_send_json_success(array(
			'html' => $template_class->render($attributes),
		));

	} // end serve_field_template;

	/**
	 * Returns the list of registered signup field types.
	 *
	 * Developers looking for add new types of fields to the signup
	 * should use the filter wu_checkout_forms_field_types to do so.
	 *
	 * @see wu_checkout_forms_field_types
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_field_templates() {

		$field_templates = array();

		/*
		 * Adds default template selection templates.
		 */
		$field_templates['template_selection'] = array(
			'clean'   => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Template_Selection\\Clean_Template_Selection_Field_Template',
			'minimal' => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Template_Selection\\Minimal_Template_Selection_Field_Template',
			'legacy'  => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Template_Selection\\Legacy_Template_Selection_Field_Template',
		);

		/*
		 * Adds the default period selector templates.
		 */
		$field_templates['period_selection'] = array(
			'clean'  => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Period_Selection\\Clean_Period_Selection_Field_Template',
			'legacy' => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Period_Selection\\Legacy_Period_Selection_Field_Template',
		);

		/*
		* Adds the default pricing table templates.
		*/
		$field_templates['pricing_table'] = array(
			'list'   => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Pricing_Table\\List_Pricing_Table_Field_Template',
			'legacy' => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Pricing_Table\\Legacy_Pricing_Table_Field_Template',
		);

		/*
		 * Adds the default order-bump templates.
		 */
		$field_templates['order_bump'] = array(
			'simple' => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Order_Bump\\Simple_Order_Bump_Field_Template',
		);

		/*
		 * Adds the default order-summary templates.
		 */
		$field_templates['order_summary'] = array(
			'clean' => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Order_Summary\\Clean_Order_Summary_Field_Template',
		);

		/*
		 * Adds the default order-summary templates.
		 */
		$field_templates['steps'] = array(
			'clean'   => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Steps\\Clean_Steps_Field_Template',
			'minimal' => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Steps\\Minimal_Steps_Field_Template',
			'legacy'  => '\\WP_Ultimo\\Checkout\\Signup_Fields\\Field_Templates\\Steps\\Legacy_Steps_Field_Template',
		);

		/*
		 * Allow developers to add new field templates
		 */
		do_action('wu_register_field_templates');

		/**
		 * Our APIs to add new field templates hook into here.
		 * Do not use this filter directly. Use the wu_register_field_template()
		 * function instead.
		 *
		 * @see wu_register_field_template()
		 *
		 * @since 2.0.0
		 * @param array $field_templates
		 * @return array
		 */
		return apply_filters('wu_checkout_field_templates', $field_templates);

	} // end get_field_templates;

	/**
	 * Get the field templates for a field type. Returns only the class names.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field_type The field type id.
	 * @return array
	 */
	public function get_templates($field_type) {

		return wu_get_isset($this->get_field_templates(), $field_type, array());

	} // end get_templates;

	/**
	 * Get the instance of the template class.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field_type The field type id.
	 * @param string $field_template_id The field template id.
	 * @return object
	 */
	public function get_template_class($field_type, $field_template_id) {

		$templates = $this->get_instantiated_field_types($field_type);

		return wu_get_isset($templates, $field_template_id);

	} // end get_template_class;

	/**
	 * Returns the field templates as a key => title array of options.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field_type The field type id.
	 * @return array
	 */
	public function get_templates_as_options($field_type) {

		$templates = $this->get_instantiated_field_types($field_type);

		$options = array();

		foreach ($templates as $template_id => $template) {

			$options[$template_id] = $template->get_title();

		} // end foreach;

		return $options;

	} // end get_templates_as_options;

	/**
	 * Returns the field templates as a key => info_array array of fields.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field_type The field type id.
	 * @return array
	 */
	public function get_templates_info($field_type) {

		$templates = $this->get_instantiated_field_types($field_type);

		$options = array();

		foreach ($templates as $template_id => $template) {

			$options[$template_id] = array(
				'id'          => $template_id,
				'title'       => $template->get_title(),
				'description' => $template->get_description(),
				'preview'     => $template->get_preview(),
			);

		} // end foreach;

		return $options;

	} // end get_templates_info;

	/**
	 * Instantiate a field template.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class_name The class name.
	 * @return \WP_Ultimo\Checkout\Signup_Fields\Base_Signup_Field
	 */
	public function instantiate_field_template($class_name) {

		return new $class_name;

	} // end instantiate_field_template;

	/**
	 * Returns an array with all fields, instantiated.
	 *
	 * @since 2.0.0
	 * @param string $field_type The field type id.
	 * @return array
	 */
	public function get_instantiated_field_types($field_type) {

		$holder_name = "instantiated_{$field_type}_templates";

		if (!property_exists($this, $holder_name) || $this->{$holder_name} === null) {

			$this->{$holder_name} = array_map(array($this, 'instantiate_field_template'), $this->get_templates($field_type));

		} // end if;

		return $this->{$holder_name};

	} // end get_instantiated_field_types;

	/**
	 * Render preview block.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field_type The field type id.
	 * @return string
	 */
	public function render_preview_block($field_type) {

		$preview_block = '<div class="wu-w-full">';

		foreach (Field_Templates_Manager::get_instance()->get_templates_info($field_type) as $template_slug => $template_info) {

			$image_tag = $template_info['preview'] ? sprintf('<img class="wu-object-cover wu-image-preview wu-w-7 wu-h-7 wu-rounded wu-mr-3" src="%1$s" data-image="%1$s">', $template_info['preview']) : '<div class="wu-w-7 wu-h-7 wu-bg-gray-200 wu-rounded wu-text-gray-600 wu-flex wu-items-center wu-justify-center wu-mr-2">
				<span class="dashicons-wu-image"></span>
			</div>';

			$preview_block .= sprintf("<div v-show='%4\$s_template === \"%1\$s\"' class='wu-w-full wu-flex wu-items-center'>
				<div class='wu-flex wu-items-center'>%2\$s</div><div class='wu-flex-wrap wu-overflow-hidden'>%3\$s</div>
			</div>", $template_info['id'], $image_tag, $template_info['description'], $field_type);

		} // end foreach;

		$preview_block .= '</div>';

		return $preview_block;

	} // end render_preview_block;

} // end class Field_Templates_Manager;
