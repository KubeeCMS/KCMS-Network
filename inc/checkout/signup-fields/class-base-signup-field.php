<?php
/**
 * Creates a cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Order
 * @since 2.0.0
 */

namespace WP_Ultimo\Checkout\Signup_Fields;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Creates an cart with the parameters of the purchase being placed.
 *
 * @package WP_Ultimo
 * @subpackage Checkout
 * @since 2.0.0
 */
abstract class Base_Signup_Field {

	/**
	 * Holds the field attributes.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $attributes;

	/**
	 * Returns the type of the field.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	abstract public function get_type();

	/**
	 * Returns if this field should be present on the checkout flow or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	abstract public function is_required();

	/**
	 * Requires the title of the field/element type.
	 *
	 * This is used on the Field/Element selection screen.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	abstract public function get_title();

	/**
	 * Returns the description of the field/element.
	 *
	 * This is used as the title attribute of the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	abstract public function get_description();

	/**
	 * Returns the tooltip of the field/element.
	 *
	 * This is used as the tooltip attribute of the selector.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	abstract function get_tooltip();

	/**
	 * Returns the icon to be used on the selector.
	 *
	 * Can be either a dashicon class or a wu-dashicon class.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	abstract public function get_icon();

	/**
	 * Returns the list of additional fields specific to this type.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	abstract public function get_fields();

	/**
	 * Returns the field/element actual field array to be used on the checkout form.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes Attributes saved on the editor form.
	 * @return array An array of fields, not the field itself.
	 */
	abstract public function to_fields_array($attributes);

	/**
	 * Set's if a field should not be available on the form creation.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_hidden() {

		return false;

	} // end is_hidden;

	/**
	 * Defines if this field/element is related to site creation or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_site_field() {

		return false;

	} // end is_site_field;

	/**
	 * Defines if this field/element is related to user/customer creation or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_user_field() {

		return false;

	} // end is_user_field;

	/**
	 * Returns the field as an array that the form builder can understand.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_field_as_type_option() {

		return array(
			'title'            => $this->get_title(),
			'desc'             => $this->get_description(),
			'tooltip'          => $this->get_tooltip(),
			'type'             => $this->get_type(),
			'icon'             => $this->get_icon(),
			'required'         => $this->is_required(),
			'default_fields'   => $this->default_fields(),
			'force_attributes' => $this->force_attributes(),
			'all_attributes'   => $this->get_all_attributes(),
			'fields'           => array($this, 'get_editor_fields'),
		);

	} // end get_field_as_type_option;

	/**
	 * Modifies the HTML attr array before sending it over to the form.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $html_attr The current attributes.
	 * @param string $field_name Field name.
	 * @return array
	 */
	public function get_editor_fields_html_attr($html_attr, $field_name) {

		return $html_attr;

	} // end get_editor_fields_html_attr;

	/**
	 * Get the tabs available for this field.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_tabs() {

		return array(
			'content',
			'style',
		);

	} // end get_tabs;

	/**
	 * Gets the pre-filled value for the field.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_value() {

		if (wu_get_isset($this->attributes, 'from_request') && wu_get_isset($this->attributes, 'id')) {

			return wu_request($this->attributes['id'], '');

		} // end if;

		return wu_get_isset($this->attributes, 'default_value', '');

	} // end get_value;

	/**
	 * Calculate the style attributes for the field.
	 *
	 * @since 2.0.4
	 * @return string
	 */
	public function calculate_style_attr() {

		$styles = array();

		$width = (int) wu_get_isset($this->attributes, 'width');

		if ($width) {

			if ($width !== 100) {

				$styles[] = 'float: left';

				$styles[] = sprintf('width: %s%%', $width);

			} // end if;

		} else {

			$styles[] = 'clear: both';

		} // end if;

		return implode('; ', $styles);

	} // end calculate_style_attr;

	/**
	 * Sets the config values for the current field.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes Array containing settings for the field.
	 * @return void
	 */
	public function set_attributes($attributes) {

		$this->attributes = $attributes;

	} // end set_attributes;

	/**
	 * If you want to force a particular attribute to a value, declare it here.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function force_attributes() {

		return array();

	}  // end force_attributes;

	/**
	 * Default values for the editor fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function defaults() {

		return array();

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
			'tooltip',
			'default',
			'required',
		);

	} // end default_fields;

	/**
	 * Returns the editor fields.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes The list of attributes of the field.
	 * @return array
	 */
	public function get_editor_fields($attributes = array()) {

		$final_field_list = $this->get_fields();

		/*
		 * Checks if this is a site field
		 */
		if ($this->is_site_field()) {

			$final_field_list['_site_notice_field_' . uniqid()] = array(
				'type'    => 'note',
				'classes' => 'wu--mt-px',
				'desc'    => sprintf('<div class="wu-p-4 wu--m-4 wu-bg-blue-100 wu-text-blue-600 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid">%s</div>', __('This is a site-related field. For that reason, this field will not show up when no plans are present on the shopping cart.', 'wp-ultimo')),
				'order'   => 98.5,
			);

		} // end if;

		/*
		 * Checks if this is a user field
		 */
		if ($this->is_user_field()) {

			$final_field_list['_user_notice_field_' . uniqid()] = array(
				'type'    => 'note',
				'classes' => 'wu--mt-px',
				'desc'    => sprintf('<div class="wu-p-4 wu--m-4 wu-bg-blue-100 wu-text-blue-600 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid">%s</div>', __('This is a customer-related field. For that reason, this field will not show up when the user is logged and already has a customer on file.', 'wp-ultimo')),
				'order'   => 98.5,
			);

		} // end if;

		foreach ($final_field_list as $key => &$field) {

			$field['html_attr'] = wu_get_isset($field, 'html_attr', array());

			$value = wu_get_isset($attributes, $key, null);

			$field['default'] = wu_get_isset($this->defaults(), $key, '');

			if ($value === null) {

				$value = $field['default'];

			} // end if;

			if (wu_get_isset($field['html_attr'], 'data-model')) {

				$model_name = wu_get_isset($field['html_attr'], 'data-model', 'product');

				$models = explode(',', $value);

				$func_name = "wu_get_{$model_name}";

				if (function_exists($func_name)) {

					$selected = array_map(function($id) use ($func_name) {

						$model = call_user_func($func_name, absint($id));

						if (!$model) {

							return false;

						} // end if;

						return $model->to_search_results();

					}, $models);

					$selected = array_filter($selected);

					$field['html_attr']['data-selected'] = json_encode($selected);

				} // end if;

			} // end if;

			if (!is_null($value)) {

				$field['value'] = $value;

			} // end if;

			$field['html_attr'] = $this->get_editor_fields_html_attr($field['html_attr'], $field['type']);

			/**
			 * Default v-show
			 */
			$show_reqs = false;

			if (isset($field['wrapper_html_attr'])) {

				$show_reqs = wu_get_isset($field['wrapper_html_attr'], 'v-show');

			} // end if;

			$tab = wu_get_isset($field, 'tab', 'content');

			$field['wrapper_html_attr'] = array_merge(wu_get_isset($field, 'wrapper_html_attr', array()), array(
				'v-cloak' => 1,
				'v-show'  => sprintf('require("type", "%s") && require("tab", "%s")', $this->get_type(), $tab) . ($show_reqs ? " && $show_reqs" : ''),
			));

		} // end foreach;

		return $final_field_list;

	} // end get_editor_fields;

	/**
	 * Returns a list of all the attributes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_all_attributes() {

		$styles = array(
			'wrapper_element_classes',
			'element_classes',
			'element_id',
			'from_request',
			'width',
			'logged',
		);

		$field_keys = array_keys($this->get_fields());

		return array_merge($this->default_fields(), $field_keys, $styles);

	} // end get_all_attributes;

	/**
	 * Treat the attributes array to avoid reaching the input var limits.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes The attributes.
	 * @return array
	 */
	public function reduce_attributes($attributes) {

		return $attributes;

	} // end reduce_attributes;

	/**
	 * List of all the default fields available.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function fields_list() {

		$fields = array();

		$fields['id'] = array(
			'type'        => 'text',
			'title'       => __('Field ID', 'wp-ultimo'),
			'placeholder' => __('e.g. info-name', 'wp-ultimo'),
			'tooltip'     => __('Only alpha-numeric and hyphens allowed.', 'wp-ultimo'),
			'desc'        => __('The ID of the field. This is used to reference the field.', 'wp-ultimo'),
			'value'       => wu_request('id', ''),
			'html_attr'   => array(
				'v-on:input'   => 'id = $event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, "")',
				'v-bind:value' => 'id',
			),
		);

		$fields['name'] = array(
			'type'        => 'text',
			'title'       => __('Field Label', 'wp-ultimo'),
			'placeholder' => __('e.g. Your Name', 'wp-ultimo'),
			'desc'        => __('This is what your customer see as the field title.', 'wp-ultimo'),
			'tooltip'     => __('Leave blank to hide the field label. You can also set a placeholder value and tip in the "Additional Settings" tab.', 'wp-ultimo'),
			'value'       => '',
			'html_attr'   => array(
				'v-model' => 'name',
			),
		);

		$fields['placeholder'] = array(
			'type'        => 'text',
			'title'       => __('Field Placeholder', 'wp-ultimo'),
			'placeholder' => __('e.g. Placeholder value', 'wp-ultimo'),
			'desc'        => __('This value appears inside the field, as an example of how to fill it.', 'wp-ultimo'),
			'tooltip'     => '',
			'value'       => '',
			'tab'         => 'advanced',
			'html_attr'   => array(
				'v-model' => 'placeholder',
			),
		);

		$fields['tooltip'] = array(
			'type'        => 'textarea',
			'title'       => __('Field Tooltip', 'wp-ultimo'),
			'placeholder' => __('e.g. This field is great, be sure to fill it.', 'wp-ultimo'),
			// translators: %is is the icon for a question mark.
			'desc'        => sprintf(__('Any text entered here will be shown when the customer hovers the %s icon next to the field label.', 'wp-ultimo'), wu_tooltip(__('Just like this!', 'wp-ultimo'))),
			'tooltip'     => '',
			'value'       => '',
			'tab'         => 'advanced',
			'html_attr'   => array(
				'v-model' => 'tooltip',
				'rows'    => 4,
			),
		);

		$fields['default_value'] = array(
			'type'        => 'text',
			'title'       => __('Default Value', 'wp-ultimo'),
			'placeholder' => __('e.g. None', 'wp-ultimo'),
			'value'       => '',
			'html_attr'   => array(
				'v-model' => 'default_value',
			),
		);

		$fields['note'] = array(
			'type'        => 'textarea',
			'title'       => __('Content', 'wp-ultimo'),
			'placeholder' => '',
			'tooltip'     => '',
			'value'       => '',
			'html_attr'   => array(
				'v-model' => 'content',
			),
		);

		$fields['limits'] = array(
			'type'    => 'group',
			'title'   => __('Field Length', 'wp-ultimo'),
			'tooltip' => '',
			'fields'  => array(
				'min' => array(
					'type'            => 'number',
					'value'           => '',
					'placeholder'     => __('Min', 'wp-ultimo'),
					'wrapper_classes' => 'wu-w-1/2',
					'html_attr'       => array(
						'v-model' => 'min',
					),
				),
				'max' => array(
					'type'            => 'number',
					'value'           => '',
					'placeholder'     => __('Max', 'wp-ultimo'),
					'wrapper_classes' => 'wu-ml-2 wu-w-1/2',
					'html_attr'       => array(
						'v-model' => 'max',
					),
				),
			),
		);

		$fields['save_as'] = array(
			'type'        => 'select',
			'title'       => __('Save As', 'wp-ultimo'),
			'desc'        => __('Select how you want to save this piece of meta data. You can attach it to the customer or the site as site meta or as site option.', 'wp-ultimo'),
			'placeholder' => '',
			'tooltip'     => '',
			'value'       => 'customer_meta',
			'order'       => 99.5,
			'options'     => array(
				'customer_meta' => __('Customer Meta', 'wp-ultimo'),
				'user_meta'     => __('User Meta', 'wp-ultimo'),
				'site_meta'     => __('Site Meta', 'wp-ultimo'),
				'site_option'   => __('Site Option', 'wp-ultimo'),
				'nothing'       => __('Do not save', 'wp-ultimo'),
			),
			'html_attr'   => array(
				'v-model' => 'save_as',
			),
		);

		$fields['required'] = array(
			'type'      => 'toggle',
			'title'     => __('Required', 'wp-ultimo'),
			'desc'      => __('Mark this field as required. The checkout will not proceed unless this field is filled.', 'wp-ultimo'),
			'value'     => 0,
			'order'     => 98,
			'html_attr' => array(
				'v-model' => 'required',
			),
		);

		return $fields;

	} // end fields_list;

} // end class Base_Signup_Field;
