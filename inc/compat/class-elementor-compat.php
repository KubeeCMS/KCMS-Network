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

		add_filter('wu_should_redirect_to_primary_domain', array($this, 'maybe_prevent_redirection'));

		add_action('elementor/widget/shortcode/skins_init', array($this, 'maybe_setup_preview'));

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

		$file_manager = \Elementor\Plugin::$instance->files_manager; // phpcs:ignore

		if (!empty($file_manager)) {

			$file_manager->clear_cache();

		} // end if;

		restore_current_blog();

	} // end regenerate_css;

	/**
	 * Prevents redirection to primary domain when in Elementor preview mode.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $should_redirect If we should redirect or not.
	 * @return bool
	 */
	public function maybe_prevent_redirection($should_redirect) {

		return wu_request('elementor-preview', false) === false ? $should_redirect : true;

	} // end maybe_prevent_redirection;

	/**
	 * Maybe adds the setup preview for elements inside elementor.
	 *
	 * @since 2.0.5
	 * @return void
	 */
	public function maybe_setup_preview() {

		$elementor_actions = array(
			'elementor',
			'elementor_ajax',
		);

		if (in_array(wu_request('action'), $elementor_actions, true)) {

			wu_element_setup_preview();

		} // end if;

	} // end maybe_setup_preview;

} // end class Elementor_Compat;
