<?php
/**
 * Plugins Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Objects
 * @since 2.0.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Plugins Limit Module.
 *
 * @since 2.0.0
 */
class Limit_Plugins extends Limit {

	/**
	 * The module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'plugins';

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

		$plugin = (object) $this->{$value_to_check};

		if ($type === 'visible') {

			$check = $plugin->visibility === 'visible';

		} elseif ($type === 'hidden') {

			$check = $plugin->visibility === 'hidden';

		} elseif ($type === 'default') {

			$check = $plugin->behavior === 'default';

		} elseif ($type === 'force_active') {

			$check = $plugin->behavior === 'force_active';

		} elseif ($type === 'force_inactive') {

			$check = $plugin->behavior === 'force_inactive';

		} elseif ($type === 'force_active_locked') {

			$check = $plugin->behavior === 'force_active_locked';

		} elseif ($type === 'force_inactive_locked') {

			$check = $plugin->behavior === 'force_inactive_locked';

		} // end if;

		return $check;

	} // end check;

	/**
	 * Adds a magic getter for plugins.
	 *
	 * @since 2.0.0
	 *
	 * @param string $plugin_name The plugin name.
	 * @return object
	 */
	public function __get($plugin_name) {

		return (object) wu_get_isset($this->get_limit(), $plugin_name, $this->get_default_permissions($plugin_name));

	} // end __get;

	/**
	 * Returns a list of plugins by behavior and visibility.
	 *
	 * @since 2.0.0
	 *
	 * @param null|string $behavior The behaviour to search for.
	 * @param null|string $visibility The visibility to search for.
	 * @return array
	 */
	public function get_by_type($behavior = null, $visibility = null) {

		$search_params = array();

		if ($behavior) {

			$search_params[] = array('behavior', $behavior);

		} // end if;

		if ($visibility) {

			$search_params[] = array('visibility', $visibility);

		} // end if;

		$results = \WP_Ultimo\Dependencies\Arrch\Arrch::find((array) $this->get_limit(), array(
			'where' => $search_params,
		));

		return $results;

	} // end get_by_type;

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
			'behavior'   => 'default',
		);

	} // end get_default_permissions;

	/**
	 * Checks if a theme exists on the current module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $plugin_name The theme name.
	 * @return bool
	 */
	public function exists($plugin_name) {

		$results = wu_get_isset($this->get_limit(), $plugin_name, array());

		return wu_get_isset($results, 'visibility', 'not-set') !== 'not-set' || wu_get_isset($results, 'behavior', 'not-set') !== 'not-set';

	} // end exists;

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

} // end class Limit_Plugins;
