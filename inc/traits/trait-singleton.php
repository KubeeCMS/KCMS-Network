<?php
/**
 * A trait to be included in entities to enable REST API endpoints.
 *
 * @package WP_Ultimo
 * @subpackage Apis
 * @since 2.0.0
 */

namespace WP_Ultimo\Traits;

/**
 * Singleton trait.
 */
trait Singleton {

	/**
	 * Makes sure we are only using one instance of the class
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Returns the instance of WP_Ultimo
	 *
	 * @return object
	 */
	public static function get_instance() {

		if (!static::$instance instanceof static) {

			static::$instance = new static();

			static::$instance->init();

		} // end if;

		return static::$instance;

	} // end get_instance;

	/**
	 * Runs only once, at the first instantiation of the Singleton.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {} // end init;

} // end trait Singleton;
