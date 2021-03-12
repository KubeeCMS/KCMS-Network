<?php
// WP Ultimo Starts #
/**
 * Sunrise File
 *
 * WordPress Core has a few ways of allowing plugin developers to run things earlier in the app lifecycle.
 * One of this ways is to place a sunrise.php file inside the wp-content directory while setting
 * The SUNRISE constant to true.
 *
 * This tells WordPress that it should load our sunrise file before plugins get loaded and
 * the request is processed. We use this great power to handle domain mapping logic and more.
 *
 * @author      Arindo Duque
 * @category    WP_Ultimo
 * @package     WP_Ultimo/Sunrise
 * @version     2.0.0
 */

define('WPULTIMO_SUNRISE_VERSION', '2.0.0');

/**
 * Include sunrise loader
 */
$wu_sunrise = defined('WP_PLUGIN_DIR')
	? WP_PLUGIN_DIR . '/wp-ultimo/inc/class-sunrise.php'
	: WP_CONTENT_DIR . '/plugins/wp-ultimo/inc/class-sunrise.php';

if (file_exists($wu_sunrise)) {

	require_once $wu_sunrise;

	WP_Ultimo\Sunrise::init();

} // end if;

// WP Ultimo Ends #
