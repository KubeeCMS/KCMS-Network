<?php
/**
 * Describes a form that can be used in different contexts, with different view files for each field type.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use \WP_Ultimo\UI\Field;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Describes a form that can be used in different contexts, with different view files for each field type.
 *
 * @since 2.0.0
 */
class Form implements \JsonSerializable {

	/**
	 * Holds the attributes of this field.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $atts = array();

	/**
	 * Holds the fields we want to display using this form.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Set and the attributes passed via the constructor.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Form id. This is going to be used to retrieve the value from the database later.
	 * @param array  $fields List of arrays representing the form fields.
	 * @param array  $atts Form attributes.
	 */
	public function __construct($id, $fields, $atts = array()) {

		$this->atts = apply_filters("wu_{$id}_form_atts", wp_parse_args($atts, array(
			'id'                    => $id,
			'method'                => 'post',
			'before'                => '',
			'after'                 => '',
			'action'                => false,
			'title'                 => false,
			'wrap_in_form_tag'      => false,
			'wrap_tag'              => 'div',
			'classes'               => false,
			'field_wrapper_classes' => false,
			'field_classes'         => false,
			'views'                 => 'settings/fields',
			'variables'             => array(),
			'step'                  => (object) array(
				'classes'    => '',
				'element_id' => '',
			),
			'html_attr'             => array(
				'class' => '',
			),
		)));

		$this->set_fields($fields);

	} // end __construct;

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
			'before',
			'after',
		);

		$attr = isset($this->atts[$att]) ? $this->atts[$att] : false;

		if (in_array($att, $allowed_callable, true) && is_callable($attr)) {

			$attr = call_user_func($attr, $this);

		} // end if;

		return $attr;

	} // end __get;

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
	 * Returns the list of fields used by the form.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return (array) $this->fields;

	} // end get_fields;

	/**
	 * Casts fields to \WP_Ultimo\UI\Fields and stores them on private list.
	 *
	 * @since 2.0.0
	 *
	 * @param array $fields List of fields of the form.
	 * @return void
	 */
	public function set_fields($fields) {

		$id = $this->id;

		/**
		 * Filters the fields on a form. The form is identified by the ID in the filter name.
		 *
		 * @since 2.0.0
		 *
		 * @param array $fields List of fields of the form.
		 */
		$fields = apply_filters("wu_{$id}_form_fields", $fields);

		foreach ($fields as $field_slug => $field) {

			$field['form'] = $this;

			$this->fields[$field_slug] = new Field($field_slug, $field);

		} // end foreach;

	} // end set_fields;

	/**
	 * Renders the form with its fields.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render() {

		$variables = array_merge($this->variables, array(
			'form_slug' => $this->id,
			'form'      => $this,
			'step'      => $this->step,
		));

		ob_start();

		foreach ($this->get_fields() as $field_slug => $field) {

			$template_name = $field->get_template_name();

			if (wu_get_isset($field->wrapper_html_attr, 'id') === false) {

				$new_wrapper_attributes = $field->wrapper_html_attr;

				$new_wrapper_attributes['id'] = "wrapper-field-$field_slug";

				$field->set_attribute('wrapper_html_attr', $new_wrapper_attributes);

			} // end if;

			wu_get_template("{$this->views}/field-{$template_name}", array(
				'field_slug' => $field_slug,
				'field'      => $field,
			), "{$this->views}/field-text");

		} // end foreach;

		$rendered_fields = ob_get_clean();

		$variables['rendered_fields'] = $rendered_fields;

		wu_get_template("{$this->views}/form", $variables);

	} // end render;

	/**
	 * Return HTML attributes for the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_html_attributes() {

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

		return wu_array_to_html_attrs($attributes);

	} // end get_html_attributes;

	/**
	 * Implements our on json_decode version of this object. Useful for use in vue.js
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function jsonSerialize() {

		return $this->atts;

	} // end jsonSerialize;

} // end class Form;
