<?php
/**
 * Elementor Compatibility Layer
 *
 * Handles Elementor Support
 *
 * @package WP_Ultimo
 * @subpackage Compat/Elementor_Compat
 * @since 2.0.0
 */

namespace WP_Ultimo\Compat;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles Elementor Support
 *
 * @since 2.0.0
 */
class Elementor_Compat {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_duplicate_site', array($this, 'regenerate_css'));

	} // end init;

	/**
	 * Makes sure we force elementor to regenerate the styles when necessary.
	 *
	 * @since 1.10.10
	 * @param array $site Info about the duplicated site.
	 * @return void
	 */
	public function regenerate_css($site) {

		if (!class_exists('\Elementor\Plugin')) {

			return;

		} // end if;

		if (!isset($site['site_id'])) {

			return;

		} // end if;

		switch_to_blog($site['site_id']);

		\Elementor\Plugin::$instance->files_manager->clear_cache();

		restore_current_blog();

	} // end regenerate_css;

} // end class Elementor_Compat;
