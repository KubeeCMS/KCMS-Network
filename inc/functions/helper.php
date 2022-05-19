<?php
/**
 * Core Helper Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Exception\Runtime_Exception;

/**
 * Returns the WP Ultimo version.
 *
 * @since 2.0.0
 * @return string
 */
function wu_get_version() {

	return WP_Ultimo()->version;

} // end wu_get_version;

/**
 * Check the debug status.
 *
 * @since 2.0.11
 * @return bool
 */
function wu_is_debug() {

	return defined('WP_ULTIMO_DEBUG') && WP_ULTIMO_DEBUG;

} // end wu_is_debug;

/**
 * Checks if WP Ultimo is being loaded as a must-use plugin.
 *
 * @since 2.0.0
 * @return bool
 */
function wu_is_must_use() {

	return defined('WP_ULTIMO_IS_MUST_USE') && WP_ULTIMO_IS_MUST_USE;

} // end wu_is_must_use;

/**
 * Checks if an array key value is set and returns it.
 *
 * If the key is not set, returns the $default parameter.
 * This function is a helper to serve as a shorthand for the tedious
 * and ugly $var = isset($array['key'])) ? $array['key'] : $default.
 * Using this, that same line becomes wu_get_isset($array, 'key', $default);
 *
 * @since 2.0.0
 *
 * @param array  $array Array to check key.
 * @param string $key Key to check.
 * @param mixed  $default Default value, if the key is not set.
 * @return mixed
 */
function wu_get_isset($array, $key, $default = false) {

	if (!is_array($array)) {

		$array = (array) $array;

	} // end if;

	return isset($array[$key]) ? $array[$key] : $default;

} // end wu_get_isset;

/**
 * Returns the main site id for the network.
 *
 * @since 2.0.0
 * @return int
 */
function wu_get_main_site_id() {

	_wu_require_hook('ms_loaded');

	global $current_site;

	return $current_site->blog_id;

} // end wu_get_main_site_id;

/**
 * This function return 'slugfied' options terms to be used as options ids.
 *
 * @since 0.0.1
 * @param string $term Returns a string based on the term and this plugin slug.
 * @return string
 */
function wu_slugify($term) {

	return "wp-ultimo_$term";

} // end wu_slugify;

/**
 * Returns the full path to the plugin folder.
 *
 * @since 2.0.11
 * @param string $dir Path relative to the plugin root you want to access.
 * @return string
 */
function wu_path($dir) {

	return WP_ULTIMO_PLUGIN_DIR . $dir;

} // end wu_path;

/**
 * Returns the URL to the plugin folder.
 *
 * @since 2.0.11
 * @param string $dir Path relative to the plugin root you want to access.
 * @return string
 */
function wu_url($dir) {

	return apply_filters('wp_ultimo_url', WP_ULTIMO_PLUGIN_URL . $dir);

} // end wu_url;

/**
 * Shorthand to retrieving variables from $_GET, $_POST and $_REQUEST;
 *
 * @since 2.0.0
 *
 * @param string  $key Key to retrieve.
 * @param boolean $default Default value, when the variable is not available.
 * @return mixed
 */
function wu_request($key, $default = false) {

	$value = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;

	return apply_filters('wu_request', $value, $key, $default);

} // end wu_request;

/**
 * Throws an exception if a given hook was not yet run.
 *
 * @since 2.0.11
 *
 * @param string $hook The hook to check. Defaults to 'ms_loaded'.
 * @throws Runtime_Exception When the hook has not yet run.
 * @return void
 */
function _wu_require_hook($hook = 'ms_loaded') { // phpcs:ignore

	if (!did_action($hook)) {

		$message = "This function can not yet be run as it relies on processing that happens on hook {$hook}.";

		throw new Runtime_Exception($message);

	} // end if;

} // end _wu_require_hook;

/**
 * Checks if reflection is available.
 *
 * Opcache settings can stripe comments and
 * make reflection unavailable.
 *
 * @since 2.0.11
 * @return boolean
 */
function wu_are_code_comments_available() {

	static $res;

	if ($res === null) {

		$res = (bool) (new \ReflectionFunction(__FUNCTION__))->getDocComment();

	} // end if;

	return $res;

} // end wu_are_code_comments_available;

/**
 * Join string into a single path string.
 *
 * @since 2.0.11
 * @param string ...$parts The parts of the path to join.
 * @return string The URL string.
 */
function wu_path_join(...$parts) {

	if (sizeof($parts) === 0) {

		return '';

	} // end if;

	$prefix = ($parts[0] === DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';

	$processed = array_filter(array_map(function($part) {

		return rtrim($part, DIRECTORY_SEPARATOR);

	}, $parts), function ($part) {

		return !empty($part);

	});

	return $prefix . implode(DIRECTORY_SEPARATOR, $processed);

} // end wu_path_join;


/**
 * Add a log entry to chosen file.
 *
 * @since 2.0.0
 *
 * @param string $handle Name of the log file to write to.
 * @param string $message Log message to write.
 * @return void
 */
function wu_log_add($handle, $message) {

	\WP_Ultimo\Logger::add($handle, $message);

} // end wu_log_add;

/**
 * Clear entries from chosen file.
 *
 * @since 2.0.0
 *
 * @param mixed $handle Name of the log file to clear.
 * @return void
 */
function wu_log_clear($handle) {

	\WP_Ultimo\Logger::clear($handle);

} // end wu_log_clear;

/**
 * Maybe log errors to the file.
 *
 * @since 2.0.0
 *
 * @param \Throwable $e The exception object.
 * @return void
 */
function wu_maybe_log_error($e) {

	if (defined('WP_DEBUG') && WP_DEBUG) {

		error_log($e);

	} // end if;

} // end wu_maybe_log_error;

/**
 * Get the function caller.
 *
 * @since 2.0.0
 *
 * @param integer $depth The depth of the backtrace.
 * @return string|null
 */
function wu_get_function_caller($depth = 1) {

	$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $depth + 1);

	$caller = isset($backtrace[$depth]['function']) ? $backtrace[$depth]['function'] : null;

	return $caller;

} // end wu_get_function_caller;
