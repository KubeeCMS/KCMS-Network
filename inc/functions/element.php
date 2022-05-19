<?php
/**
 * Element Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.5
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Triggers the setup_preview hooks for all registered elements.
 *
 * @since 2.0.5
 * @return void
 */
function wu_element_setup_preview() {

	!did_action('wu_element_preview') && do_action('wu_element_preview');

} // end wu_element_setup_preview;
