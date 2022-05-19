<?php
/**
 * Describes a field and contains helper functions for sanitization and validation.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Describes a field and contains helper functions for sanitization and validation.
 *
 * @since 2.0.0
 */
class Field implements \JsonSerializable {

	/**
	 * Holds the attributes of this field.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $atts = array();

	/**
	 * Holds the value of the settings represented by this field.
	 *
	 * @since 2.0.0
	 * @var mixed
	 */
	protected $value = null;

	/**
	 * Set and the attributes passed via the constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Field id. This is going to be used to retrieve the value from the database later.
	 * @param array  $atts Field attributes.
	 */
	public function __construct($id, $atts) {

		$this->set_attributes($id, $atts);

	} // end __construct;

	/**
	 * Set and the attributes passed via the constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Field id. This is going to be used to retrieve the value from the database later.
	 * @param array  $atts Field attributes.
	 * @return void
	 */
	public function set_attributes($id, $atts) {

		$this->atts = wp_parse_args($atts, array(
			'id'                => $id,
			'type'              => 'text',
			'icon'              => 'dashicons-wu-cog',
			'action'            => false,
			'form'              => false,
			'title'             => false,
			'img'               => false,
			'desc'              => false,
			'content'           => false,
			'display_value'     => false,
			'default_value'     => false,
			'tooltip'           => false,
			'args'              => false,
			'sortable'          => false,
			'placeholder'       => false,
			'options'           => false,
			'options_template'  => false,
			'require'           => false,
			'button'            => false,
			'width'             => false,
			'rules'             => false,
			'min'               => false,
			'max'               => false,
			'allow_html'        => false,
			'append'            => false,
			'order'             => false,
			'dummy'             => false,
			'disabled'          => false,
			'capability'        => false,
			'edit'              => false,
			'copy'              => false,
			'validation'        => false,
			'meter'             => false,
			'href'              => false,
			'raw'               => false,
			'money'             => false,
			'stacked'           => false, // If the field is inside a restricted container
			'columns'           => 1,
			'classes'           => '',
			'wrapper_classes'   => '',
			'html_attr'         => array(),
			'wrapper_html_attr' => array(),
			'sub_fields'        => array(),
			'prefix'            => '',
			'suffix'            => '',
			'prefix_html_attr'  => array(),
			'suffix_html_attr'  => array(),
		));

	} // end set_attributes;

	/**
	 * Set a particular attribute.
	 *
	 * @since 2.0.0
	 *
	 * @param string $att The attribute name.
	 * @param mixed  $value The new attribute value.
	 * @return void
	 */
	public function set_attribute($att, $value) {

		$this->atts[$att] = $value;

	} // end set_attribute;

	/**
	 * Returns the list of field attributes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_attributes() {

		return $this->atts;

	} // end get_attributes;

	/**
	 * Makes sure old fields remain compatible.
	 *
	 * We are making some field type name changes in 2.0.
	 * This method lists an array with aliases in the following format:
	 *
	 * - old_type_name => new_type_name.
	 *
	 * We throw a deprecation notice to make sure developers update their code appropriately.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_compat_template_name() {

		$aliases = array(
			'heading'             => 'header',
			'heading_collapsible' => 'header',
			'select2'             => 'select',
			'checkbox'            => 'toggle',
		);

		$deprecated = array(
			'heading',
			'heading_collapsible',
			'select2',
		);

		if (array_key_exists($this->type, $aliases)) {

			$new_type_name = $aliases[$this->type];

			if (array_key_exists($this->type, $deprecated)) {

				// translators: The %1$s placeholder is the old type name, the second, the new type name.
				_doing_it_wrong('wu_add_field', sprintf(__('The field type "%1$s" is no longer supported, use "%2$s" instead.'), $this->type, $new_type_name), '2.0.0');

			} // end if;

			/*
			 * Back Compat for Select2 Fields
			 */
			if ($this->type === 'select2') {

				$this->atts['html_attr']['data-selectize'] = 1;
				$this->atts['html_attr']['multiple']       = 1;

			} // end if;

			return $new_type_name;

		} // end if;

		return false;

	} // end get_compat_template_name;

	/**
	 * Returns the template name for a field.
	 *
	 * We use this to go to the views folder and fetch the HTML template.
	 * The return here is not an absolute path, as the folder depends on the view the form is using.
	 *
	 * @see \WP_Ultimo\UI\Forms
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_template_name() {

		$compat_name = $this->get_compat_template_name();

		$view_name = $compat_name ? $compat_name : $this->type;

		return str_replace('_', '-', $view_name);

	} // end get_template_name;

	/**
	 * Returns attributes as class properties.
	 *
	 * @since 2.0.0
	 *
	 * @param string $att Attribute to retrieve.
	 * @return mixed
	 */
	public function __get($att) {

		$allowed_callable = array(
			'title',
			'desc',
			'content',
			'display_value',
			'default_value',
			'tooltip',
			'options',
			'require',
			'validation',
			'value',
			'html_attr',
			'img',
		);

		$attr = isset($this->atts[$att]) ? $this->atts[$att] : false;

		if (in_array($att, $allowed_callable, true) && is_callable($attr)) {

			$attr = call_user_func($attr, $this);

		} // end if;

		if ($att === 'wrapper_classes' && isset($this->atts['wrapper_html_attr']['v-show'])) {

			$this->atts['wrapper_classes'] = $this->atts['wrapper_classes'] . ' wu-requires-other';

		} // end if;

		if ($att === 'type' && $this->atts[$att] === 'submit') {

			$this->atts['wrapper_classes'] = $this->atts['wrapper_classes'] . ' wu-submit-field';

		} // end if;

		if ($att === 'type' && $this->atts[$att] === 'tab-select') {

			$this->atts['wrapper_classes'] = $this->atts['wrapper_classes'] . ' wu-tab-field';

		} // end if;

		if ($att === 'wrapper_classes' && is_a($this->form, '\\WP_Ultimo\\UI\\Form')) {

			return $this->form->field_wrapper_classes . ' ' . $this->atts['wrapper_classes'];

		} // end if;

		if ($att === 'classes' && is_a($this->form, '\\WP_Ultimo\\UI\\Form')) {

			return $this->form->field_classes . ' ' . $this->atts['classes'];

		} // end if;

		if ($att === 'title' && $attr === false && isset($this->atts['name'])) {

			$attr = $this->atts['name'];

		} // end if;

		return $attr;

	} // end __get;

	/**
	 * Returns the list of sanitization callbacks for each field type
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function sanitization_rules() {

		$rules = array(
			'text'           => 'sanitize_text_field',
			'header'         => '__return_null',
			'number'         => array($this, 'validate_number_field'),
			'wp_editor'      => array($this, 'validate_textarea_field'),
			'textarea'       => array($this, 'validate_textarea_field'),
			'checkbox'       => 'wu_string_to_bool',
			'multi_checkbox' => false,
			'select2'        => false,
			'multiselect'    => false,
		);

		return apply_filters('wu_settings_fields_sanitization_rules', $rules);

	} // end sanitization_rules;

	/**
	 * Returns the value of the setting represented by this field.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_value() {

		return $this->value;

	} // end get_value;

	/**
	 * Sets the value of the settings represented by this field.
	 *
	 * This alone won't save the setting to the database. This method also invokes the
	 * sanitization callback, so we can be sure the data is ready for database insertion.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value Value of the settings being represented by this field.
	 * @return WP_Ultimo\UI\Field
	 */
	public function set_value($value) {

		$this->value = $value;

		if (!$this->raw) {

			$this->sanitize();

		} // end if;

		return $this;

	} // end set_value;

	/**
	 * Runs the value of the field through the sanitization callback.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function sanitize() {

		$rules = $this->sanitization_rules();

		$sanitize_method = isset($rules[$this->type]) ? $rules[$this->type] : $rules['text'];

		if ($sanitize_method) {

			$this->value = call_user_func($sanitize_method, $this->value);

		} // end if;

	} // end sanitize;

	/**
	 * Sanitization callback for fields of type number.
	 *
	 * Checks if the new value set is between the min and max boundaries.
	 *
	 * @since 2.0.0
	 *
	 * @param int|float $value Value of the settings being represented by this field.
	 * @return int|float
	 */
	protected function validate_number_field($value) {

		/**
		 * Check if the value respects the min/max values.
		 */
		if ($this->min && $value < $this->min) {

			return $this->min;

		} // end if;

		if ($this->max && $value > $this->max) {

			return $this->max;

		} // end if;

		return $value;

	} // end validate_number_field;

	/**
	 * Cleans the value submitted via a textarea or wp_editor field for database insertion.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value Value of the settings being represented by this field.
	 * @return string
	 */
	protected function validate_textarea_field($value) {

		if ($this->allow_html) {

			return stripslashes(wp_filter_post_kses(addslashes($value)));

		} // end if;

		return wp_strip_all_tags(stripslashes($value));

	} // end validate_textarea_field;

	/**
	 * Return HTML attributes for the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_html_attributes() {

		if (is_callable($this->atts['html_attr'])) {

			$this->atts['html_attr'] = call_user_func($this->atts['html_attr']);

		} // end if;

		$attributes = $this->atts['html_attr'];

		unset($this->atts['html_attr']['class']);

		if ($this->type === 'number') {

			if ($this->min !== false) {

				$attributes['min'] = $this->min;

			} // end if;

			if ($this->max !== false) {

				$attributes['max'] = $this->max;

			} // end if;

		} // end if;

		/*
		 * Adds money formatting and masking
		 */
		if ($this->money !== false) {

			$attributes['v-bind'] = 'money_settings';

		} // end if;

		return wu_array_to_html_attrs($attributes);

	} // end get_html_attributes;

	/**
	 * Return HTML attributes for the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_wrapper_html_attributes() {

		$attributes = $this->atts['wrapper_html_attr'];

		unset($this->atts['wrapper_html_attr']['class']);

		return wu_array_to_html_attrs($attributes);

	} // end get_wrapper_html_attributes;

	/**
	 * Implements our on json_decode version of this object. Useful for use in vue.js
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function jsonSerialize() {

		return $this->atts;

	} // end jsonSerialize;

} // end class Field;
