<?php
/**
 * Domain_Mapping Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Domain_Mapping Limit Module.
 *
 * @since 2.0.0
 */
class Limit_Domain_Mapping extends Limit {

	/**
	 * The module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'domain_mapping';

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

		return $check;

	} // end check;

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

} // end class Limit_Domain_Mapping;
