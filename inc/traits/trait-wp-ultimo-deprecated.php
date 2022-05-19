<?php
/**
 * A trait to be included in entities to WP_Ultimo Class depecrated methods.
 *
 * @package WP_Ultimo
 * @subpackage Apis
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

/**
 * WP_Ultimo_Deprecated trait.
 */
trait WP_Ultimo_Deprecated {

	/**
	 * Deprecated: WP_Ultimo->slugfy().
	 *
	 * @since 2.0.0
	 * @param string $term Returns a string based on the term and this plugin slug.
	 * @return void
	 */
	public function slugfy($term) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_slugify($term)');

		wu_slugify($term);

	} // end slugfy;

	/**
	 * Deprecated: WP_Ultimo->add_page_to_branding()
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_page_to_branding() {

		_deprecated_function(__METHOD__, '2.0.0');

	} // end add_page_to_branding;

	/**
	 * Renders a view file from the view folder.
	 *
	 * @deprecated 2.0.0
	 *
	 * @since 0.0.1
	 * @param string  $view View file to render. Do not include the .php extension.
	 * @param boolean $vars Key => Value pairs to be made available as local variables inside the view scope.
	 * @return void
	 */
	public function render($view, $vars = false) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_get_template()');

		wu_get_template($view, $vars);

	} // end render;

	/**
	 * Returns the full path to the plugin folder
	 *
	 * @deprecated 2.0.0
	 *
	 * @since 0.0.1
	 * @param string $dir Path relative to the plugin root you want to access.
	 * @return string
	 */
	public function path($dir) {

		_deprecated_function(__METHOD__, '2.0.0', 'wu_path()');

		return wu_path($dir);

	} // end path;

	/**
	 * Deprecated: Add messages to be displayed as notices
	 *
	 * @deprecated 2.0.0
	 *
	 * @param string  $message Message to be displayed.
	 * @param string  $type    Success, error, warning or info.
	 * @param boolean $network Where to display, network admin or normal admin.
	 * @return void
	 */
	public function add_message($message, $type = 'success', $network = false) {

		_deprecated_function(__METHOD__, '2.0.0', 'WP_Ultimo()->notices->add()');

		$panel = $network ? 'network-admin' : 'admin';

		$ultimo = WP_Ultimo();

		if (isset($ultimo->notices) && $ultimo->notices) {

			$ultimo->notices->add($message, $type, $panel);

		} // end if;

	} // end add_message;

	/**
	 * Deprecated: This function is here to make sure that the plugin is network active
	 * and that this is a multisite install.
	 *
	 * @deprecated 2.0.0
	 *
	 * @since 1.0.0
	 * @return boolean
	 */
	public function check_before_run() {

		_deprecated_function(__METHOD__, '2.0.0', 'WP_Ultimo()->is_loaded()');

		return WP_Ultimo()->is_loaded();

	} // end check_before_run;

	/**
	 * Deprecated: enqueue_select2.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_select2() {

		_deprecated_function(__METHOD__, '2.0.0');

		wp_enqueue_style('wu-select2css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all');

		wp_enqueue_script('wu-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array('jquery'), '1.0', true);

	} // end enqueue_select2;

} // end trait WP_Ultimo_Deprecated;
