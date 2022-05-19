<?php
/**
 * Checkout Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Managers\Signup_Fields_Manager;

/**
 * Needs to be removed.
 *
 * @todo Remove this and use out functions instead.
 * @since 2.0.0
 * @return \WP_Error
 */
function wu_errors() {

	global $wu_errors;

	if (!is_wp_error($wu_errors)) {

		$wu_errors = new \WP_Error();

	} // end if;

	return $wu_errors;

} // end wu_errors;

/**
 * Generate an idempotency key.
 *
 * @since 2.0.0
 *
 * @param array  $args Arguments used to create or update the current object.
 * @param string $context The context in which the key was generated.
 * @return string
 */
function wu_stripe_generate_idempotency_key($args, $context = 'new') {

	$idempotency_key = md5(json_encode($args));

	/**
	 * Filters the idempotency_key value sent with the Stripe charge options.
	 *
	 * @since 3.5.0
	 *
	 * @param string $idempotency_key Value of the idempotency key.
	 * @param array  $args            Arguments used to help generate the key.
	 * @param string $context         Context under which the idempotency key is generated.
	*/
	$idempotency_key = apply_filters('wu_stripe_generate_idempotency_key', $idempotency_key, $args, $context);

	return $idempotency_key;

} // end wu_stripe_generate_idempotency_key;

/**
 * Loops through the signup field types to return the checkout fields.
 *
 * @since 2.0.0
 *
 * @param array $fields List of signup field types.
 * @return array
 */
function wu_create_checkout_fields($fields = array()) {

	$field_types = Signup_Fields_Manager::get_instance()->get_field_types();

	$actual_fields = array();

	// Extra check to prevent error messages from being displayed.
	if (!is_array($fields)) {

		$fields = array();

	} // end if;

	foreach ($fields as $field) {

		$type = $field['type'];

		if (!wu_get_isset($field_types, $type)) {

			continue;

		} // end if;

		try {

			$field_class = new $field_types[$type];

		} catch (\Throwable $e) {

			continue;

		} // end try;

		$field = wp_parse_args($field, $field_class->defaults());

		$field = array_merge($field, $field_class->force_attributes());

		/*
		 * Check Field Visibility
		 */
		$visibility = wu_get_isset($field, 'logged', 'always');

		if ($visibility !== 'always') {

			if ($visibility === 'guests_only' && is_user_logged_in()) {

				continue;

			} // end if;

			if ($visibility === 'logged_only' && !is_user_logged_in()) {

				continue;

			} // end if;

		} // end if;

		/*
		 * Pass the attributes down to the field class.
		 */
		$field_class->set_attributes($field);

		/*
		 * Makes sure we have default indexes.
		 */
		$field = wp_parse_args($field, array(
			'element_classes' => '',
			'classes'         => '',
		));

		$actual_fields = array_merge($actual_fields, $field_class->to_fields_array($field));

	} // end foreach;

	return $actual_fields;

} // end wu_create_checkout_fields;

/**
 * Returns the URL for the registration page.
 *
 * @since 2.0.0
 * @param string|false $path Path to attach to the end of the URL.
 * @return string
 */
function wu_get_registration_url($path = false) {

	$checkout_pages = \WP_Ultimo\Checkout\Checkout_Pages::get_instance();

	$url = $checkout_pages->get_page_url('register');

	if (!$url) {

		return '#no-registration-url';

	} // end if;

	return $url . $path;

} // end wu_get_registration_url;

/**
 * Returns the URL for the login page.
 *
 * @since 2.0.0
 * @param string|false $path Path to attach to the end of the URL.
 * @return string
 */
function wu_get_login_url($path = false) {

	$checkout_pages = \WP_Ultimo\Checkout\Checkout_Pages::get_instance();

	$url = $checkout_pages->get_page_url('login');

	if (!$url) {

		return '#no-login-url';

	} // end if;

	return $url . $path;

} // end wu_get_login_url;

/**
 * Checks if we allow for multiple memberships.
 *
 * @todo: review this.
 * @since 2.0.0
 * @return boolean
 */
function wu_multiple_memberships_enabled() {

	return true;

} // end wu_multiple_memberships_enabled;

/**
 * Get the number of days in a billing cycle.
 *
 * Taken from WooCommerce.
 *
 * @since 2.0.0
 * @param string $duration_unit Unit: day, month, or year.
 * @param int    $duration Cycle duration.
 *
 * @return int
 */
function wu_get_days_in_cycle($duration_unit, $duration) {

	$days_in_cycle = 0;

	switch ($duration_unit) {

		case 'day':
			$days_in_cycle = $duration;
			break;
		case 'week':
			$days_in_cycle = $duration * 7;
			break;
		case 'month':
			$days_in_cycle = $duration * 30.4375; // Average days per month over 4 year period
			break;
		case 'year':
			$days_in_cycle = $duration * 365.25; // Average days per year over 4 year period
			break;

	} // end switch;

	return $days_in_cycle;

} // end wu_get_days_in_cycle;

/**
 * Register a new field type.
 *
 * Field types are types of field (duh!) that can be
 * added to the checkout flow and other forms inside WP Ultimo.
 *
 * @see https://help.wpultimo.com/article/344-add-custom-field-types-to-wp-ultimo
 *
 * @since 2.0.0
 *
 * @param string $field_type_id The field type id. E.g. pricing_table, template_selection.
 * @param string $field_type_class_name The field type class name. The "absolute" path to the class.
 * @return void
 */
function wu_register_field_type($field_type_id, $field_type_class_name) {

	add_filter('wu_checkout_field_types', function($field_types) use ($field_type_id, $field_type_class_name) {

		$field_types[$field_type_id] = $field_type_class_name;

		return $field_types;

	});

} // end wu_register_field_type;

/**
 * Register a new field template for a field type.
 *
 * Field templates are different layouts that can be added to
 * WP Ultimo to be used as the final representation of a given
 * checkout field.
 *
 * @see https://help.wpultimo.com/article/343-customize-your-checkout-flow-using-field-templates
 *
 * @since 2.0.0
 *
 * @param string $field_type The field type. E.g. pricing_table, template_selection.
 * @param string $field_template_id The field template ID. e.g. clean, minimal.
 * @param string $field_template_class_name The field template class name. The "absolute" path to the class.
 * @return void
 */
function wu_register_field_template($field_type, $field_template_id, $field_template_class_name) {

	add_filter('wu_checkout_field_templates', function($field_templates) use ($field_type, $field_template_id, $field_template_class_name) {

		$field_templates_for_field_type = wu_get_isset($field_templates, $field_type, array());

		$field_templates_for_field_type[$field_template_id] = $field_template_class_name;

		$field_templates[$field_type] = $field_templates_for_field_type;

		return $field_templates;

	});

} // end wu_register_field_template;
