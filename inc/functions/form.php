<?php
/**
 * Form Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Managers\Form_Manager;

/**
 * Registers a new Ajax Form.
 *
 * Ajax forms are forms that get loaded via an ajax call using thickbox.
 * This is useful for displaying inline edit forms that support Vue and our
 * Form/Fields API.
 *
 * @since 2.0.0
 * @see \WP_Ultimo\Managers\Form_Manager::register_form
 *
 * @param string $form_id Form id.
 * @param array  $atts Form attributes, check wp_parse_atts call below.
 * @return mixed
 */
function wu_register_form($form_id, $atts = array()) {

	return Form_Manager::get_instance()->register_form($form_id, $atts);

} // end wu_register_form;

/**
 * Returns the ajax URL for a given form.
 *
 * @since 2.0.0
 * @see \WP_Ultimo\Managers\Form_Manager::get_form_url
 *
 * @param string  $form_id The id of the form to return.
 * @param array   $atts List of parameters, check wp_parse_args below.
 * @param boolean $inline If this form is has content.
 * @return string
 */
function wu_get_form_url($form_id, $atts = array(), $inline = false) {

	if ($inline) {

		$atts = wp_parse_args($atts, array(
			'inlineId' => $form_id,
			'width'    => '400',
			'height'   => '360',
		));

		// TB_inline?height=300&width=300&inlineId=wu-add-field
		return add_query_arg($atts, '#TB_inline');

	} // end if;

	return Form_Manager::get_instance()->get_form_url($form_id, $atts, $inline);

} // end wu_get_form_url;

/**
 * Adds our fork of the thickbox script.
 *
 * @since 2.0.0
 * @return void
 */
function add_wubox() { // phpcs:ignore

	wp_enqueue_script('wubox');

} // end add_wubox;
