<?php
/**
 * Model Helper Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Cast a list of models to a list of arrays containing the model properties.
 *
 * @since 2.0.0
 *
 * @param \WP_Ultimo\Models\Base_Model $model The model to cast to array.
 * @return array
 */
function wu_cast_model_to_array($model) {

	if (is_a($model, '\\WP_Ultimo\\Models\\Base_Model')) {

		$model = $model->to_array();

	} // end if;

	return $model;

} // end wu_cast_model_to_array;

/**
 * Converts a list of Model objects to a list of ID => $label_field
 *
 * @since 2.0.0
 *
 * @param array  $models The list of models to convert.
 * @param string $label_field The name of the field to use.
 * @return array
 */
function wu_models_to_options($models, $label_field = 'name') {

	$options_list = array();

	foreach ($models as $model) {

		$options_list[$model->get_id()] = call_user_func(array($model, "get_{$label_field}"));

	} // end foreach;

	return $options_list;

} // end wu_models_to_options;

/**
 * Get the schema of a particular model.
 *
 * @since 2.0.11
 *
 * @param string $class_name The fully qualified model name.
 * @return array
 */
function wu_model_get_schema($class_name) {

	$schema = array();

	if (method_exists($class_name, 'get_schema')) {

		$schema = $class_name::get_schema();

	} // end if;

	return $schema;

} // end wu_model_get_schema;

/**
 * Returns a list of required fields form a model schema.
 *
 * @since 2.0.11
 *
 * @param string $class_name The fully qualified model name.
 * @return array
 */
function wu_model_get_required_fields($class_name) {

	$required_fields = array();

	if (method_exists($class_name, 'validation_rules')) {

		$validation_rules = (new $class_name)->validation_rules();

		foreach ($validation_rules as $field => $validation_rule) {

			if (strpos($validation_rule, 'required|') !== false || $validation_rule === 'required') {

				$required_fields[] = $field;

			} // end if;

		} // end foreach;

	} // end if;

	return $required_fields;

} // end wu_model_get_required_fields;
