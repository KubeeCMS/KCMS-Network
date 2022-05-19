<?php
/**
 * Base Field Template
 *
 * @package WP_Ultimo
 * @subpackage Checkout\Signup_Fields
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields\Field_Templates;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base Field Template
 *
 * @since 2.0.0
 */
class Base_Field_Template {

	/**
	 * Field template id.
	 *
	 * Needs to take the following format: field-type/id.
	 * e.g. pricing-table/clean.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id;

	/**
	 * Field template attributes.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $attributes;

	/**
	 * Field Template Constructor
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes The attributes passed to the field.
	 */
	public function __construct($attributes = array()) {

		$this->attributes = $attributes;

	} // end __construct;

	/**
	 * The render type for the template.
	 *
	 * Field templates can have two different render types, ajax and dynamic.
	 * If ajax is selected, when we detect a change in the billing period and other
	 * sensitive info, an ajax request is made to fetch the new pricing table HTML
	 * markup.
	 *
	 * If dynamic is selected, nothing is done as the template can handle
	 * reactive updates natively (using Vue.js)
	 *
	 * In terms of performance, dynamic is preferred, but ajax should
	 * work just fine.
	 *
	 * @since 2.0.0
	 * @return string Either ajax or dynamic
	 */
	public function get_render_type() {

		return 'ajax';

 	} // end get_render_type;

	/**
	 * The title of the field template.
	 *
	 * This is used on the template selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return __('Field Template', 'wp-ultimo');

	} // end get_title;

	/**
	 * The description of the field template.
	 *
	 * This is used on the template selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Description', 'wp-ultimo');

	} // end get_description;

	/**
	 * The preview image of the field template.
	 *
	 * The URL of the image preview.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview() {

		return '';

	} // end get_preview;

	/**
	 * The content of the template.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes The field template attributes.
	 * @return void
	 */
	public function output($attributes) {} // end output;

	/**
	 * Renders the content.
	 *
	 * This method should not be override.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes The field template attributes.
	 * @return string
	 */
	public function render($attributes) {

		ob_start();

		$this->output($attributes);

		return ob_get_clean();

	} // end render;

	/**
	 * Displays the content on the checkout form as a wrapper.
	 *
	 * This method should not be override.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $attributes The field template attributes.
	 * @param object $signup_field The base field.
	 * @return string
	 */
	public function render_container($attributes, $signup_field = false) {

		if ($this->get_render_type() === 'ajax') {

			if ($signup_field) {

				$attributes = $signup_field->reduce_attributes($attributes);

			} // end if;

			$markup = sprintf('<dynamic :template="get_template(\'%s\', %s)"></dynamic>', esc_js($this->id), esc_attr(json_encode($attributes)));

		} else {

			$markup = $this->render($attributes);

		} // end if;

		return $markup;

	} // end render_container;

} // end class Base_Field_Template;
