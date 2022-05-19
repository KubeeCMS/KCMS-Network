<?php
/**
 * Default API hooks.
 *
 * @package WP_Ultimo
 * @subpackage API
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a lighter ajax option to WP Ultimo.
 *
 * @since 1.9.14
 */
class Views {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Adds hooks to be added at the original instantiation.
	 *
	 * @since 1.9.0
	 */
	public function init() {

		add_filter('wu_view_override', array($this, 'view_override'), 10, 3);

	} // end init;

	/**
	 * Allows us to search templates when we are not in the main site environment
	 *
	 * @todo Can this be improved? Do we need to re-check the Template Path in here? Not sure...
	 *
	 * @since 1.9.0
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @param bool         $load           If true the template file will be loaded if it is found.
	 * @param bool         $require_once   Whether to require_once or require. Default true. Has no effect if $load is false.
	 * @return string The template filename if one is located.
	 */
	public function custom_locate_template($template_names, $load = false, $require_once = true) {

		switch_to_blog(get_current_site()->blog_id);

		$stylesheet_path = get_stylesheet_directory();

		restore_current_blog();

		$located = '';

		foreach ((array) $template_names as $template_name) {

			if (!$template_name) {

				continue;

			} // end if;

			if (file_exists( $stylesheet_path . '/' . $template_name)) {

				$located = $stylesheet_path . '/' . $template_name;

				break;

			} elseif (file_exists(TEMPLATEPATH . '/' . $template_name)) {

				$located = TEMPLATEPATH . '/' . $template_name;

				break;

			} elseif (file_exists(ABSPATH . WPINC . '/theme-compat/' . $template_name)) {

				$located = ABSPATH . WPINC . '/theme-compat/' . $template_name;

				break;

			} // end if;

		} // end foreach;

		if ($load && '' !== $located) {

			load_template($located, $require_once);

		} // end if;

		return $located;

	} // end custom_locate_template;

	/**
	 * Check if an alternative view exists and override
	 *
	 * @param  string $original_path The original path of the view.
	 * @param  string $view          View path.
	 * @return string  The new path.
	 */
	public function view_override($original_path, $view) {

		if (is_main_site()) {

			$found = locate_template("wp-ultimo/$view.php");

		} else {

			$found = $this->custom_locate_template("wp-ultimo/$view.php");

		} // end if;

		return $found ? $found : $original_path;

	} // end view_override;

} // end class Views;
