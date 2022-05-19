<?php
/**
 * Users Limit Module.
 *
 * @package WP_Ultimo
 * @subpackage Limitations
 * @since 2.0.0
 */

namespace WP_Ultimo\Limitations;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Users Limit Module.
 *
 * @since 2.0.0
 */
class Limit_Users extends Limit_Subtype {

	/**
	 * The module id.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'users';

} // end class Limit_Users;
