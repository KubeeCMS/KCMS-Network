<?php
/**
 * Themes Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Themes Limit Module.
 *
 * @since 2.0.0
 */
class Limit_Themes extends Limit {

	/**
	 * The module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'themes';

	/**
	 * The theme being currently forced for this site.
	 *
	 * @since 2.0.0
	 * @var null|false|string Null when first initialized, false when no theme is forced or the theme name.
	 */
	protected $forced_active_theme;

	/**
	 * The check method is what gets called when allowed is called.
	 *
	 * Each module needs to implement a check method, that returns a boolean.
	 * This check can take any form the developer wants.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed  $value_to_check Value to check.
	 * @param mixed  $limit The list of limits in this modules.
	 * @param string $type Type for sub-checking.
	 * @return bool
	 */
	public function check($value_to_check, $limit, $type = '') {

		$check = false;

		$theme = (object) $this->{$value_to_check};

		if ($type === 'visible') {

			$check = $theme->visibility === 'visible';

		} elseif ($type === 'hidden') {

			$check = $theme->visibility === 'hidden';

		} elseif ($type === 'available') {

			$check = $theme->behavior === 'available';

		} elseif ($type === 'not_available') {

			$check = $theme->behavior === 'not_available';

		} // end if;

		return $check;

	} // end check;

	/**
	 * Adds a magic getter for themes.
	 *
	 * @since 2.0.0
	 *
	 * @param string $theme_name The theme name.
	 * @return object
	 */
	public function __get($theme_name) {

		return (object) wu_get_isset($this->get_limit(), $theme_name, $this->get_default_permissions($theme_name));

	} // end __get;

	/**
	 * Returns default permissions.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type Type for sub-checking.
	 * @return array
	 */
	public function get_default_permissions($type) {

		return array(
			'visibility' => 'visible',
			'behavior'   => 'available',
		);

	} // end get_default_permissions;

	/**
	 * Checks if a theme exists on the current module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $theme_name The theme name.
	 * @return bool
	 */
	public function exists($theme_name) {

		$results = wu_get_isset($this->get_limit(), $theme_name, array());

		return wu_get_isset($results, 'visibility', 'not-set') !== 'not-set' || wu_get_isset($results, 'behavior', 'not-set') !== 'not-set';

	} // end exists;

	/**
	 * Get all themes.
	 *
	 * @since 2.0.0
	 * @return array List of theme stylesheets.
	 */
	public function get_all_themes() {

		$themes = (array) $this->get_limit();

		return array_keys($themes);

	} // end get_all_themes;

	/**
	 * Get available themes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_available_themes() {

		$limits = $this->get_limit();

		$available = array();

		foreach ($limits as $theme_slug => $theme_settings) {

			$theme_settings = (object) $theme_settings;

			if ($theme_settings->behavior === 'available') {

				$available[] = $theme_slug;

			} // end if;

		} // end foreach;

		return $available;

	} // end get_available_themes;

	/**
	 * Get the forced active theme for the current limitations.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_forced_active_theme() {

		$active_theme = false;

		$limits = $this->get_limit();

		if (empty($limits)) {

			return $active_theme;

		} // end if;

		if ($this->forced_active_theme !== null) {

			return $this->forced_active_theme;

		} // end if;

		foreach ($limits as $theme_slug => $theme_settings) {

			$theme_settings = (object) $theme_settings;

			if ($theme_settings->behavior === 'force_active') {

				$active_theme = $theme_slug;

			} // end if;

		} // end foreach;

		$this->forced_active_theme = $active_theme;

		return $this->forced_active_theme;

	} // end get_forced_active_theme;

	/**
	 * Checks if the module is enabled.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type Type for sub-checking.
	 * @return boolean
	 */
	public function is_enabled($type = '') {

		return true;

	} // end is_enabled;

} // end class Limit_Themes;
