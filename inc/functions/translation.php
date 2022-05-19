<?php
/**
 * Translation Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.11
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Get the translatable version of a string.
 *
 * @since 2.0.5
 *
 * @param string $string The string to get.
 * @return string
 */
function wu_get_translatable_string($string) {

	if (is_string($string) === false) {

		return $string;

	} // end if;

	$translatable_strings = include WP_ULTIMO_PLUGIN_DIR . '/data/translatable-strings.php';

	$translatable_strings = apply_filters('wu_translatable_strings', $translatable_strings, $string);

	return wu_get_isset($translatable_strings, $string, $string);

} // end wu_get_translatable_string;
