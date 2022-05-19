<?php
// WP Ultimo Starts #
/**
 * WP Ultimo Sunrise
 * Plugin URI: https://wpultimo.com
 * Version: 2.0.0.6
 * Author: Arindo Duque
 * Author URI: https://wpultimo.com
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * WordPress Core has a few ways of allowing plugin developers to run things earlier in the app lifecycle.
 * One of this ways is to place a sunrise.php file inside the wp-content directory while setting
 * The SUNRISE constant to true.
 *
 * This tells WordPress that it should load our sunrise file before plugins get loaded and
 * the request is processed. We use this great power to handle domain mapping logic and more.
 * 
 * @since 2.0.0.5 Adds a network admin notice warning that sunrise is still active when Ultimo is deactivated.
 * @since 2.0.0.5 Change return statement to a continue statement to prevent an early exit from the file.
 *
 * @author      Arindo Duque
 * @category    WP_Ultimo
 * @package     WP_Ultimo/Sunrise
 * @version     2.0.0.5
 */

defined('ABSPATH') || exit;

define('WP_ULTIMO_SUNRISE_VERSION', '2.0.0.7');

$wu_sunrise = defined('WP_PLUGIN_DIR')
	? WP_PLUGIN_DIR . '/wp-ultimo/inc/class-sunrise.php'
	: WP_CONTENT_DIR . '/plugins/wp-ultimo/inc/class-sunrise.php';

$wu_mu_sunrise = defined('WPMU_PLUGIN_DIR')
	? WPMU_PLUGIN_DIR . '/wp-ultimo/inc/class-sunrise.php'
	: WP_CONTENT_DIR . '/mu-plugins/wp-ultimo/inc/class-sunrise.php';

/**
 * We search for the sunrise class file
 * in the plugins and mu-plugins folders.
 * 
 * @since 2.0.0.3 Sunrise Version.
 */
$possible_sunrises = array(
	$wu_sunrise,
	$wu_mu_sunrise,
);

foreach ($possible_sunrises as $sunrise) {

	if (file_exists($sunrise)) {

		if ($sunrise === $wu_mu_sunrise) {

		 /**
		  * Use a particular function that is defined
		  * after mu-plugins are loaded but before regular plugins
		  * to check if we are being loaded in a must-use context.
		  */
			define('WP_ULTIMO_IS_MUST_USE', true);

		} // end if;
	
		require_once $sunrise;

		define('WP_ULTIMO_SUNRISE_FILE', $sunrise);
	
		WP_Ultimo\Sunrise::init();

		add_action('network_admin_notices', 'wu_remove_sunrise_warning', 0);

		continue; // Exit the loop;
	
	} // end if;

} // end if;

/**
 * Include Mercator.
 * 
 * This is here purely for backwards compatibility reasons.
 * The file included here is a dumb file in version 2.0.7+.
 * 
 * @since 2.0.7
 */
$wu_mercator = defined('WP_PLUGIN_DIR')
	? WP_PLUGIN_DIR . '/wp-ultimo/inc/mercator/mercator.php'
	: WP_CONTENT_DIR . '/plugins/wp-ultimo/inc/mercator/mercator.php';

if (file_exists($wu_mercator)) {

	require $wu_mercator;

} // end if;

/**
 * Adds a warning when WP Ultimo is not present but the sunrise file is.
 *
 * @since 2.0.0
 * @return void
 */
function wu_remove_sunrise_warning() { 
	
	if (function_exists('WP_Ultimo') === false) {

	?>

	<div class="notice notice-warning">
		<p>
			WP Ultimo is deactivated, yet its <strong>sunrise.php</strong> file is still being loaded. If you have no intentions of continuing to use WP Ultimo and this was not a temporary deactivation, we recommend removing the <code>define('SUNRISE', true);</code> line from your <strong>wp-config.php</strong> file. Keeping WP Ultimo <strong>sunrise.php</strong> file active without WP Ultimo can lead to unexpected behaviors and it is not advised.
		</p>
	</div>

	<?php

	} // end if;

} // end wu_remove_sunrise_warning;

// WP Ultimo Ends #
