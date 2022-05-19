<?php
/**
 * Pages Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Checks if the current post is a registration page.
 *
 * @since 2.0.0
 * @return boolean
 */
function wu_is_registration_page() {

	global $post;

	if (!is_main_site()) {

		return false;

	} // end if;

	if (!is_a($post, '\WP_Post')) {

		return false;

	} // end if;

	return absint(wu_get_setting('default_registration_page', 0)) === $post->ID;

} // end wu_is_registration_page;

/**
 * Checks if the current page is a login page.
 *
 * @since 2.0.11
 * @return bool
 */
function wu_is_login_page() {

	global $pagenow;

	$is_login_element_present = \WP_Ultimo\UI\Login_Form_Element::get_instance()->is_actually_loaded();

	$is_default_wp_login = $pagenow === 'wp-login.php';

	return $is_login_element_present || $is_default_wp_login;

} // end wu_is_login_page;
