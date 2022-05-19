<?php
/**
 * Base_Manager
 *
 * Singleton class that handles hooks that need to be registered only once.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Base_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a lighter ajax option to WP Ultimo.
 *
 * @since 1.9.14
 */
class Base_Manager {

	/**
	 * A valid init method is required.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function init() {} // end init;

} // end class Base_Manager;
