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

use WP_Ultimo\Models\Event;
use WP_Ultimo\Objects\Event_Code;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a lighter ajax option to WP Ultimo.
 *
 * @since 1.9.14
 */
class Base_Manager {} // end class Base_Manager;
