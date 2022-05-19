<?php
/**
 * Broadcast Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Alias function to be used on the templates
 *
 * @param  string       $view Template to be get.
 * @param  array        $args Arguments to be parsed and made available inside the template file.
 * @param string|false $default_view View to be used if the view passed is not found. Used as fallback.
 * @return void
 */
function wu_get_template($view, $args = array(), $default_view = false) {

	/**
	 * Allow plugin developers to add extra variable to the render context globally.
	 *
	 * @since 2.0.0
	 * @param array $args Array containing variables passed by the render call.
	 * @param string $view Name of the view to be rendered.
	 * @param string $default_view Name of the fallback_view
	 * @return array
	 */
	$args = apply_filters('wp_ultimo_render_vars', $args, $view, $default_view);

	$template = wu_path("views/$view.php");

	// Make passed variables available
	if (is_array($args)) {

		extract($args); // phpcs:ignore

	} // end if;

	/**
	 * Allows developers to add additional folders to the replaceable list.
	 *
	 * Be careful, as allowing additional folders might cause
	 * out-of-date copies to be loaded instead of the WP Ultimo versions.
	 *
	 * @since 2.0.0
	 * @param array $replaceable_views List of allowed folders.
	 * @return array
	 */
	$replaceable_views = apply_filters('wu_view_override_replaceable_views', array(
		'signup',
		'emails',
		'forms',
	));

	/*
		* Only allow template for emails and signup for now
		*/
	if (preg_match('/(' . implode('|', $replaceable_views) . ')\w+/', $view)) {

		$template = apply_filters('wu_view_override', $template, $view, $default_view);

	} // end if;

	if (!file_exists($template) && $default_view) {

		$template = wu_path("views/$default_view.php");

	} // end if;

	// Load our view
	include $template;

} // end wu_get_template;

/**
 * Alias function to be used on the templates;
 * Rather than directly including the template, it returns the contents inside a variable
 *
 * @param  string       $view Template to be get.
 * @param  array        $args Arguments to be parsed and made available inside the template file.
 * @param string|false $default_view View to be used if the view passed is not found. Used as fallback.
 * @return string
 */
function wu_get_template_contents($view, $args = array(), $default_view = false) {

	ob_start();

		wu_get_template($view, $args, $default_view); // phpcs:ignore

	return ob_get_clean();

} // end wu_get_template_contents;
