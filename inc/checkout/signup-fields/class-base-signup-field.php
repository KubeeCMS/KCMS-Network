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
				'type' => 'note',
				'desc' => sprintf('<div class="wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded wu-w-full">%s</div>', __('This is a site-related field. For that reason, this field will not show up when no plans are present on the shopping cart.', 'wp-ultimo')),
			);

		} // end if;

		/*
		 * Checks if this is a user field
		 */
		if ($this->is_user_field()) {

			$final_field_list['_user_notice_field_' . uniqid()] = array(
				'type' => 'note',
				'desc' => sprintf('<div class="wu-p-2 wu-bg-blue-100 wu-text-blue-600 wu-rounded wu-w-full">%s</div>', __('This is a customer-related field. For that reason, this field will not show up when the user is logged and already has a customer on file.', 'wp-ultimo')),
			);

		} // end if;

		foreach ($final_field_list as $key => &$field) {

			$field['html_attr'] = wu_get_isset($field, 'html_attr', array());

			$value = wu_get_isset($attributes, $key, null);

			if (wu_get_isset($field['html_attr'], 'data-model')) {

				$model_name = wu_get_isset($field['html_attr'], 'data-model', 'product');

				$models = explode(',', $value);

				$func_name = "wu_get_{$model_name}";

				if (function_exists($func_name)) {

					$selected = array_map(function($id) use ($func_name) {

						$model = call_user_func($func_name, abs($id));

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

			$field['wrapper_html_attr'] = array(
				'v-cloak' => 1,
				'v-show'  => sprintf('require("type", "%s") && require("tab", "content")', $this->get_type()) . ($show_reqs ? " && $show_reqs" : ''),
			);

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
			'element_classes',
			'element_id',
			'width',
			'logged',
		);

		$field_keys = array_keys($this->get_fields());

		return array_merge($this->default_fields(), $field_keys, $styles);

	} // end get_all_attributes;

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
			'title'       => __('Field Name', 'wp-ultimo'),
			'placeholder' => __('e.g. info-name', 'wp-ultimo'),
			'tooltip'     => __('Only alpha-numeric and hyphens allowed.', 'wp-ultimo'),
			'value'       => '',
			'html_attr'   => array(
				'required'     => 'required',
				'v-on:input'   => 'id = $event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, "")',
				'v-bind:value' => 'id',
			),
		);

		$fields['name'] = array(
			'type'        => 'text',
			'title'       => __('Field Label', 'wp-ultimo'),
			'placeholder' => __('e.g. Field A', 'wp-ultimo'),
			'tooltip'     => __('Leave blank to hide the field label.', 'wp-ultimo'),
			'value'       => '',
			'html_attr'   => array(
				'v-model' => 'name',
			),
		);

		$fields['placeholder'] = array(
			'type'        => 'text',
			'title'       => __('Field Placeholder', 'wp-ultimo'),
			'placeholder' => '',
			'tooltip'     => '',
			'value'       => '',
			'html_attr'   => array(
				'v-model' => 'placeholder',
			),
		);

		$fields['tooltip'] = array(
			'type'        => 'textarea',
			'title'       => __('Field Tooltip', 'wp-ultimo'),
			'placeholder' => '',
			'tooltip'     => '',
			'value'       => '',
			'html_attr'   => array(
				'v-model' => 'tooltip',
			),
		);

		$fields['default_value'] = array(
			'type'        => 'text',
			'title'       => __('Default Value', 'wp-ultimo'),
			'placeholder' => __('e.g. None', 'wp-ultimo'),
			'tooltip'     => __('This value will be used when the field is not required and the customer does not enter anything.', 'wp-ultimo'),
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
			'placeholder' => '',
			'tooltip'     => '',
			'value'       => 'user_meta',
			'options'     => array(
				'customer_meta' => __('Customer Meta', 'wp-ultimo'),
				'site_meta'     => __('Site Meta', 'wp-ultimo'),
				'site_option'   => __('Site Option', 'wp-ultimo'),
			),
			'html_attr'   => array(
				'v-model' => 'save_as',
			),
		);

		$fields['required'] = array(
			'type'      => 'toggle',
			'title'     => __('Required', 'wp-ultimo'),
			'desc'      => __('Mark this field as required.', 'wp-ultimo'),
			'value'     => 0,
			'html_attr' => array(
				'v-model' => 'required',
			),
		);

		return $fields;

	} // end fields_list;

} // end class Base_Signup_Field;
