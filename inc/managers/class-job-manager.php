<?php
/**
 * Broadcast Manager
 *
 * Handles processes related to products.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Job
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to products.
 *
 * @since 2.0.0
 */
class Job_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

	} // end init;

} // end class Job_Manager;
