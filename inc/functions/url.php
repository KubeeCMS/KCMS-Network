<?php
/**
 * URL Helper Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns the current URL.
 *
 * @since 2.0.0
 * @return string
 */
function wu_get_current_url() {
	/*
	 * When doing ajax requests, we don't want the admin-ajax URL, but
	 * the initiator URL.
	 */
	if (wp_doing_ajax() && isset($_SERVER['HTTP_REFERER'])) {

		return $_SERVER['HTTP_REFERER'];

	} // end if;

	return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

} // end wu_get_current_url;

/**
 * Replaces or removes the scheme from a URL.
 *
 * @since 2.0.0
 *
 * @param string $url The URL to process.
 * @param string $new_scheme An empty string, https, or http.
 * @return string
 */
function wu_replace_scheme($url, $new_scheme = '') {

	return preg_replace('(^https?://)', $new_scheme, $url);

} // end wu_replace_scheme;

/**
 * Wrapper to the network_admin_url function for WP Ultimo admin urls.
 *
 * @since 2.0.0
 *
 * @param string $path WP Ultimo page.
 * @param array  $query URL query parameters.
 * @return string
 */
function wu_network_admin_url($path, $query = array()) {

	$path = sprintf('admin.php?page=%s', $path);

	$url = network_admin_url($path);

	return add_query_arg($query, $url);

} // end wu_network_admin_url;

/**
 * Get the light ajax implementation URL.
 *
 * @since 2.0.0
 *
 * @param null|string $when The wu-when parameter to be used, defaults to plugins_loaded.
 * @param array       $query_args List of additional query args to add to the final URL.
 * @param int         $site_id The site to use as a base.
 * @param null|string $scheme URL scheme. Follows the same rules as the scheme param of get_home_url.
 * @return string
 */
function wu_ajax_url($when = null, $query_args = array(), $site_id = false, $scheme = null) {

	if (empty($site_id)) {

		$site_id = get_current_blog_id();

	} // end if;

	$base_url = get_home_url($site_id, '', $scheme);

	if (!is_array($query_args)) {

		$query_args = array();

	} // end if;

	$query_args['wu-ajax'] = 1;
	$query_args['r']       = wp_create_nonce('wu-ajax-nonce');

	if ($when) {

		$query_args['wu-when'] = base64_encode($when);

	} // end if;

	$url = add_query_arg($query_args, $base_url);

	return apply_filters('wu_ajax_url', $url, $query_args, $when, $site_id);

} // end wu_ajax_url;
