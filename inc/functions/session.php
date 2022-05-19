<?php
/**
 * Session Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Gets or creates a Session object.
 *
 * @since 2.0.0
 *
 * @param string $session_key The session key.
 * @return \WP_Ultimo\Session
 */
function wu_get_session($session_key) {

	return new \WP_Ultimo\Session($session_key);

} // end wu_get_session;
