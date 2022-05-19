<?php
/**
 * Site_Templates Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Site_Templates Limit Module.
 *
 * @since 2.0.0
 */
class Limit_Site_Templates extends Limit {

	/**
	 * The module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'site_templates';

	/**
	 * The mode of template assignment/selection.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $mode = 'default';

	/**
	 * Allows sub-type limits to set their own default value for enabled.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	private $enabled_default_value = true;

	/**
	 * Sets up the module based on the module data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data The module data.
	 * @return void
	 */
	public function setup($data) {

		parent::setup($data);

		$this->mode = wu_get_isset($data, 'mode', 'default');

	} // end setup;

	/**
	 * Returns the mode. Can be one of three: default, assign_template and choose_available_templates.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_mode() {

		return $this->mode;

	} // end get_mode;

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

		$check = true;

		$theme = (object) $this->{$value_to_check};

		if ($type === 'available') {

			$check = $theme->behavior === 'available';

		} elseif ($type === 'not_available') {

			$check = $theme->behavior === 'not_available';

		} elseif ($type === 'pre_selected') {

			$check = $theme->behavior === 'pre_selected';

		} // end if;

		return $check;

	} // end check;

	/**
	 * Adds a magic getter for themes.
	 *
	 * @since 2.0.0
	 *
	 * @param string $template_id The template site id.
	 * @return object
	 */
	public function __get($template_id) {

		$template_id = str_replace('site_', '', $template_id);

		return (object) wu_get_isset($this->get_limit(), $template_id, $this->get_default_permissions($template_id));

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
			'behavior' => 'available',
		);

	} // end get_default_permissions;

	/**
	 * Checks if a theme exists on the current module.
	 *
	 * @since 2.0.0
	 *
	 * @param string $template_id The template site id.
	 * @return bool
	 */
	public function exists($template_id) {

		$template_id = str_replace('site_', '', $template_id);

		$results = wu_get_isset($this->get_limit(), $template_id, array());

		return wu_get_isset($results, 'behavior', 'not-set') !== 'not-set';

	} // end exists;

	/**
	 * Get all themes.
	 *
	 * @since 2.0.0
	 * @return array List of theme stylesheets.
	 */
	public function get_all_templates() {

		$templates = (array) $this->get_limit();

		return array_keys($templates);

	} // end get_all_templates;

	/**
	 * Get available themes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_available_site_templates() {

		$limits = $this->get_limit();

		if (!$limits) {

			return false;

		} // end if;

		$limits = (array) $limits;

		$available = array();

		foreach ($limits as $site_id => $site_settings) {

			$site_settings = (object) $site_settings;

			if ($site_settings->behavior === 'available' || $site_settings->behavior === 'pre_selected') {

				$available[] = $site_id;

			} // end if;

		} // end foreach;

		return $available;

	} // end get_available_site_templates;

	/**
	 * Get the forced active theme for the current limitations.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_pre_selected_site_template() {

		$limits = $this->get_limit();

		$pre_selected_site_template = false;

		if (!$limits) {

			return $pre_selected_site_template;

		} // end if;

		foreach ($limits as $site_id => $site_settings) {

			$site_settings = (object) $site_settings;

			if ($site_settings->behavior === 'pre_selected') {

				$pre_selected_site_template = $site_id;

			} // end if;

		} // end foreach;

		return $pre_selected_site_template;

	} // end get_pre_selected_site_template;

	/**
	 * Handles limits on post submission.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function handle_limit() {

		$module = wu_get_isset($_POST['modules'], $this->id, array());

		return wu_get_isset($module, 'limit', $this->get_limit());

	} // end handle_limit;

	/**
	 * Handles other elements when saving. Used for custom attributes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $module The current module, extracted from the request.
	 * @return array
	 */
	public function handle_others($module) {

		$_module = wu_get_isset($_POST['modules'], $this->id, array());

		$module['mode'] = wu_get_isset($_module, 'mode', 'default');

		return $module;

	} // end handle_others;

	/**
	 * Returns a default state.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function default_state() {

		return array(
			'enabled' => true,
			'limit'   => null,
			'mode'    => 'default',
		);

	} // end default_state;

} // end class Limit_Site_Templates;
