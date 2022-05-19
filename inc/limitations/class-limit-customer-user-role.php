<?php
/**
 * Customer User Role Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.10
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer User Role Limit Module.
 *
 * @since 2.0.10
 */
class Limit_Customer_User_Role extends Limit {

	/**
	 * The module id.
	 *
	 * @since 2.0.10
	 * @var string
	 */
	protected $id = 'customer_user_role';

	/**
	 * Returns a default state.
	 *
	 * @since 2.0.10
	 * @return array
	 */
	public static function default_state() {

		return array(
			'enabled' => true,
			'limit'   => 'default'
		);

	} // end default_state;

	/**
	 * The check method is what gets called when allowed is called.
	 *
	 * Each module needs to implement a check method, that returns a boolean.
	 * This check can take any form the developer wants.
	 *
	 * @since 2.0.10
	 *
	 * @param mixed  $value_to_check Value to check.
	 * @param mixed  $limit The list of limits in this modules.
	 * @param string $type Type for sub-checking.
	 * @return bool
	 */
	public function check($value_to_check, $limit, $type = '') {

		return true;

	} // end check;

	/**
	 * Gets the limit data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type Type for sub-checking.
	 * @return mixed
	 */
	public function get_limit($type = '') {

		$default_value = wu_get_setting('default_role', 'administrator');

		return empty($this->limit) || $this->limit === 'default' ? $default_value : $this->limit;

	} // end get_limit;

} // end class Limit_Customer_User_Role;
