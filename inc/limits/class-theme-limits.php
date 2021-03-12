<?php
/**
 * Handles limitations to post types, uploads and more.
 *
 * @package WP_Ultimo
 * @subpackage Limits
 * @since 2.0.0
 */

namespace WP_Ultimo\Limits;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles limitations to post types, uploads and more.
 *
 * @since 2.0.0
 */
class Theme_Limits {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs on the first and only instantiation.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('ms_loaded', array($this, 'load_limitations'));

	} // end init;

	/**
	 * Apply limitations if they are available.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_limitations() {

		if (wu_get_current_site()->has_limitations()) {

			add_filter('stylesheet', array($this, 'force_active_theme'));

			add_filter('template', array($this, 'force_active_theme'));

			add_filter('allowed_themes', array($this, 'add_extra_available_themes'));

			add_filter('site_allowed_themes', array($this, 'add_extra_available_themes'));

		} // end if;

	} // end load_limitations;

	/**
	 * Force the activation of one particularly selected theme.
	 *
	 * @since 2.0.0
	 *
	 * @param string $stylesheet The default theme being used.
	 * @return string
	 */
	public function force_active_theme($stylesheet) {

		if (is_main_site()) {

			return $stylesheet;

		} // end if;

		$forced_active_theme = wu_get_current_site()->get_limitations()->get_forced_active_theme();

		if ($forced_active_theme) {

			return $forced_active_theme;

		} // end if;

		return $stylesheet;

	} // end force_active_theme;

	/**
	 * Deactivates the plugins that people are not allowed to use.
	 *
	 * @since 2.0.0
	 *
	 * @param array $themes Array with the plugins activated.
	 * @return array
	 */
	public function add_extra_available_themes($themes) {
		/*
		 * Bail on network admin =)
		 */
		if (is_network_admin()) {

			return $themes;

		} // end if;

		$_themes = array_keys(wu_get_current_site()->get_limitations()->get_allowed_themes());

		foreach ($_themes as $theme_stylesheet) {

			$should_appear = wu_get_current_site()->get_limitations()->theme_has_behavior($theme_stylesheet, array('activate', 'available'));

			if (!wu_get_current_site()->is_theme_allowed($theme_stylesheet)) {

				if (isset($themes[$theme_stylesheet])) {

					unset($themes[$theme_stylesheet]);

				} // end if;

			} elseif ($should_appear && !isset($themes[$theme_stylesheet])) {

				$themes[] = $theme_stylesheet;

			} // end if;

		} // end foreach;

		return $themes;

	} // end add_extra_available_themes;

} // end class Theme_Limits;
