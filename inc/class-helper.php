<?php
/**
 * WP Ultimo helper methods for including and rendering files, assets, etc
 *
 * @package WP_Ultimo
 * @subpackage Helper
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo helper methods for including and rendering files, assets, etc
 *
 * @since 2.0.0
 */
class Helper {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * List of view types that are subject to view overriding
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $replaceable_views = array(
		'signup',
		'emails',
		'forms',
	);

	/**
	 * Adds hooks to be added at the original instantiation.
	 *
	 * @since 1.9.0
	 */
	public function init() {

		// Overwrite
		add_filter('wu_view_override', array($this, 'view_override'), 10, 3);

	} // end init;

	/**
	 * Returns the full path to the plugin folder
	 *
	 * @since 0.0.1
	 * @param string $dir Path relative to the plugin root you want to access.
	 * @return string
	 */
	public function path($dir) {

		return WP_ULTIMO_PLUGIN_DIR . $dir;

	} // end path;

	/**
	 * Returns the URL to the plugin folder.
	 *
	 * @since 0.0.1
	 * @param string $dir Path relative to the plugin root you want to access.
	 * @return string
	 */
	public function url($dir) {

		return apply_filters('wp_ultimo_url', WP_ULTIMO_PLUGIN_URL . $dir);

	} // end url;

	/**
	 * Shorthand for url('assets/img'). Returns the URL for assets inside the assets folder.
	 *
	 * @since 0.0.1
	 * @param string $asset Asset file name with the extention.
	 * @param string $assets_dir Assets sub-directory. Defaults to 'img'.
	 * @param string $base_dir   Base dir. Defaults to 'assets'.
	 * @return string
	 */
	public function get_asset($asset, $assets_dir = 'img', $base_dir = 'assets') {

		if (!defined('SCRIPT_DEBUG') || !SCRIPT_DEBUG) {

			$asset = preg_replace('/(?<!\.min)(\.js|\.css)/', '.min$1', $asset);

		} // end if;

		return $this->url("$base_dir/$assets_dir/$asset");

	} // end get_asset;

	/**
	 * Renders a view file from the view folder.
	 *
	 * @since 0.0.1
	 * @param string       $view View file to render. Do not include the .php extension.
	 * @param boolean      $vars Key => Value pairs to be made available as local variables inside the view scope.
	 * @param string|false $default_view View to be used if the view passed is not found. Used as fallback.
	 * @return void
	 */
	public function render($view, $vars = false, $default_view = false) {

		/**
		 * Allow plugin developers to add extra variable to the render context globally.
		 *
		 * @since 2.0.0
		 * @param array $vars Array containing variables passed by the render call.
		 * @param string $view Name of the view to be rendered.
		 * @param string $default_view Name of the fallback_view
		 * @return array
		 */
		$vars = apply_filters('wp_ultimo_render_vars', $vars, $view, $default_view);

		$template = $this->path("views/$view.php");

		// Make passed variables available
		if (is_array($vars)) {

			extract($vars); // phpcs:ignore

		} // end if;

		/**
		 * Only allow templating for emails and signup for now
		 */
		if (preg_match('/(' . implode('|', $this->replaceable_views) . ')\w+/', $view)) {

			$template = apply_filters('wu_view_override', $template, $view, $default_view);

		} // end if;

		if (!file_exists($template) && $default_view) {

			$template = $this->path("views/$default_view.php");

		} // end if;

		// Load our view
		include $template;

	} // end render;

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

	/**
	 * This function return 'slugfied' options terms to be used as options ids.
	 *
	 * @since 0.0.1
	 * @param string $term Returns a string based on the term and this plugin slug.
	 * @return string
	 */
	public function slugfy($term) {

		return "wp-ultimo_$term";

	} // end slugfy;

	/**
	 * Get the value of a slugfied network option
	 *
	 * @since 1.9.6
	 * @param string $option_name Option name.
	 * @param mixed  $default The default value.
	 * @return mixed
	 */
	public function get_option($option_name = 'settings', $default = array()) {

		$option_value = get_network_option(null, $this->slugfy($option_name), $default);

		return apply_filters('wu_get_option', $option_value, $option_name, $default);

	} // end get_option;

	/**
	 * Save slugfied network option
	 *
	 * @since 1.9.6
	 * @param string $option_name The option name to save.
	 * @param mixed  $value       The new value of the option.
	 * @return boolean
	 */
	public function save_option($option_name = 'settings', $value) {

		return update_network_option(null, $this->slugfy($option_name), $value);

	} // end save_option;

	/**
	 * Delete slugfied network option
	 *
	 * @since 1.9.6
	 * @param string $option_name The option name to delete.
	 * @return boolean
	 */
	public function delete_option($option_name) {

		return delete_network_option(null, $this->slugfy($option_name));

	} // end delete_option;

	/**
	 * Creates a WP Ultimo folder inside the uploads folder. Returns the path to the folder.
	 *
	 * @since 2.0.0
	 *
	 * @param string $folder Name of the folder.
	 * @return string
	 */
	public function maybe_create_folder($folder) {

		is_multisite() && switch_to_blog(get_current_site()->blog_id);

		$uploads = wp_upload_dir(null, false);

		is_multisite() && restore_current_blog();

		$folder_path = trailingslashit($uploads['basedir'] . '/' . $folder);

		/*
		 * Checks if the folder exists.
		 */
		if (!file_exists($folder_path)) {

			// Creates the Folder
			wp_mkdir_p($folder_path);

			// Creates htaccess
			$htaccess = $folder_path . '.htaccess';

			if (!file_exists($htaccess)) {

				$fp = @fopen($htaccess, 'w');

				@fputs($fp, 'deny from all'); // phpcs:ignore

				@fclose($fp); // phpcs:ignore

			} // end if;

			// Creates index
			$index = $folder_path . 'index.html';

			if (!file_exists($index)) {

				$fp = @fopen($index, 'w');

				@fputs($fp, ''); // phpcs:ignore

				@fclose($fp); // phpcs:ignore

			} // end if;

		} // end if;

		return $folder_path;

	} // end maybe_create_folder;

	/**
	 * Drop our custom tables.
	 *
	 * @since 2.0.0
	 * @return void
	 * @throws \Exception In case of failures, an exception is thrown.
	 */
	public function drop_tables() {

		$tables = apply_filters('wu_drop_tables', \WP_Ultimo\Loaders\Table_Loader::get_instance()->get_tables());

		$except = array(
			'blogs',
			'blogmeta',
		);

		$except = apply_filters('wu_drop_tables_except', $except);

		foreach ($tables as $table) {

			if (!in_array($table->name, $except, true)) {

				$table->uninstall();

			} // end if;

		} // end foreach;

	} // end drop_tables;

} // end class Helper;
