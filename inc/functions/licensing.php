<?php
/**
 * Licensing Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the license key to a given URL.
 *
 * @since 2.0.0
 *
 * @param string $url URL to attach the license key to.
 * @return string
 */
function wu_with_license_key($url) {

	$license_key = '';

	$license = \WP_Ultimo\License::get_instance();

	$license_key = $license->get_license_key();

	return add_query_arg('license_key', rawurlencode($license_key), $url);

} // end wu_with_license_key;
