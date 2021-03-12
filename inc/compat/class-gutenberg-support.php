<?php
/**
 * Gutenberg Support
 *
 * Allows WP Ultimo to filter Gutenberg thingys.
 *
 * @since       1.9.14
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Compat
 * @version     0.0.1
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds support to Gutenberg filters.
 *
 * @since 2.0.0
 */
class Gutenberg_Support {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Filterable function that let users decide if they want to remove
	 * Gutenberg support and modifications by Ultimo.
	 *
	 * @since 1.9.14
	 * @return bool
	 */
	public function should_load() {

		if (function_exists('has_blocks')) {

			return true;

		} // end if;

		return apply_filters('wu_gutenberg_support_should_load', true);

	} // end should_load;

	/**
	 * Initializes the Class, if we need it.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		if ($this->should_load()) {

			add_action('admin_enqueue_scripts', array($this, 'add_scripts'));

		} // end if;

	} // end init;

	/**
	 * Adds the Gutenberg Filters scripts.
	 *
	 * @since 1.9.14
	 * @return void
	 */
	public function add_scripts() {

		wp_register_script('wu-gutenberg-support', wu_get_asset('gutenberg-support.js', 'js'), array('jquery'), wu_get_version(), true);

    // translators: the placeholder is replaced with the network name.
		$preview_message = apply_filters('wu_gutenberg_support_preview_message', sprintf(__('<strong>%s</strong> is generating the preview...', 'wp-ultimo'), get_network_option(null, 'site_name')));

		wp_localize_script('wu-gutenberg-support', 'wu_gutenberg', array(
			'logo'                => esc_url(wu_get_network_logo()),
			'replacement_message' => $preview_message,
		));

		wp_enqueue_script('wu-gutenberg-support');

	} // end add_scripts;

} // end class Gutenberg_Support;
