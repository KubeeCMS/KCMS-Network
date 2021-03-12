<?php
/**
 * Checkout Forms Functions
 *
 * Public APIs to load and deal with WP Ultimo checkout forms.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Checkout_Form
 * @version     2.0.0
 */

use \WP_Ultimo\Models\Checkout_Form;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns a checkout_form.
 *
 * @since 2.0.0
 *
 * @param int $checkout_form_id The ID of the checkout_form.
 * @return \WP_Ultimo\Models\Checkout_Form|false
 */
function wu_get_checkout_form($checkout_form_id) {

	return \WP_Ultimo\Models\Checkout_Form::get_by_id($checkout_form_id);

} // end wu_get_checkout_form;

/**
 * Queries checkout_forms.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Checkout_Form[]
 */
function wu_get_checkout_forms($query = array()) {

	return \WP_Ultimo\Models\Checkout_Form::query($query);

} // end wu_get_checkout_forms;

/**
 * Returns a checkout_form based on slug.
 *
 * @since 2.0.0
 *
 * @param string $checkout_form_slug The slug of the checkout_form.
 * @return \WP_Ultimo\Models\Checkout_Form|false
 */
function wu_get_checkout_form_by_slug($checkout_form_slug) {

	return \WP_Ultimo\Models\Checkout_Form::get_by('slug', $checkout_form_slug);

} // end wu_get_checkout_form_by_slug;

/**
 * Creates a new checkout form.
 *
 * @since 2.0.0
 *
 * @param array $checkout_form_data Checkout_Form data.
 * @return \WP_Error|\WP_Ultimo\Models\Checkout_Form
 */
function wu_create_checkout_form($checkout_form_data) {

	$checkout_form_data = wp_parse_args($checkout_form_data, array(
		'name'              => false,
		'slug'              => false,
		'settings'          => array(),
		'allowed_countries' => array(),
		'date_created'      => current_time('mysql'),
		'date_modified'     => current_time('mysql'),
	));

	$checkout_form = new Checkout_Form($checkout_form_data);

	$saved = $checkout_form->save();

	return is_wp_error($saved) ? $saved : $checkout_form;

} // end wu_create_checkout_form;
