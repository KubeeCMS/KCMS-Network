<?php
/**
 * Geolocation Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Get the customers' IP address.
 *
 * @since 2.0.0
 * @return string
 */
function wu_get_ip() {

	$geolocation = \WP_Ultimo\Geolocation::geolocate_ip('', true);

	return apply_filters('wu_get_ip', $geolocation['ip']);

} // end wu_get_ip;
