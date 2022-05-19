<?php
/**
 * WP Ultimo custom Autoloader.
 *
 * @package WP_Ultimo
 * @subpackage Autoloader
 * @since 2.0.0
 */

namespace WP_Ultimo;

use WP_Ultimo\Dependencies\Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Auto-loads class files inside the inc folder.
 *
 * @since 2.0.0
 */
class Autoloader {

	/**
	 * Makes sure we are only using one instance of the class
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Static-only class.
	 */
	private function __construct() {} // end __construct;

	/**
	 * Initializes our custom autoloader
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function init() {

		if (!static::$instance instanceof static) {

			static::$instance = new WP_Namespace_Autoloader(array(
				'directory'            => dirname(dirname(__FILE__)),
				'namespace_prefix'     => 'WP_Ultimo',
				'classes_dir'          => 'inc',
				'lowercase'            => array('file', 'folders'),
				'underscore_to_hyphen' => array('file', 'folders'),
				'debug'                => Autoloader::is_debug(),
			));

			static::$instance->init();

		} // end if;

	} // end init;

	/**
	 * Checks for unit tests and WP_ULTIMO_DEBUG.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public static function is_debug() {

		return false; // return wu_is_debug();

	} // end is_debug;

} // end class Autoloader;
