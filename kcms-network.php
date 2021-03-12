<?php
/**
 * Plugin Name: KCMS Network
 * Description: The Ultimate Website as a Service (WaaS) platform builder.
 * Plugin URI: https://github.com/KubeeCMS/KCMS-Network/
 * Text Domain: wp-ultimo
 * Version: 2.0.0-beta.2
 * Author: Kubee
 * Author URI: https://github.com/KubeeCMS/
 * Network: true
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /lang
 *
 * WP Ultimo is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WP Ultimo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Ultimo. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author   Arindo Duque and NextPress
 * @category Core
 * @package  WP_Ultimo
 * @version  2.0.0-beta.2
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

if (!defined('WP_ULTIMO_PLUGIN_FILE')) {

	define('WP_ULTIMO_PLUGIN_FILE', __FILE__);

} // end if;

/**
 * Require core file dependencies
 */
require_once __DIR__ . '/constants.php';

require_once __DIR__ . '/dependencies/autoload.php';

require_once __DIR__ . '/inc/class-autoloader.php';

require_once __DIR__ . '/inc/action-scheduler/action-scheduler.php';

require_once __DIR__ . '/inc/traits/trait-singleton.php';

/**
 * Setup autoloader
 */
WP_Ultimo\Autoloader::init();

/**
 * Setup activation/deactivation hooks
 */
WP_Ultimo\Hooks::init();

/**
 * Initializes the WP Ultimo class
 *
 * This function returns the WP_Ultimo class singleton, and
 * should be used to avoid declaring globals.
 *
 * @since 2.0.0
 * @return WP_Ultimo
 */
function WP_Ultimo() { // phpcs:ignore

	return WP_Ultimo::get_instance();

} // end WP_Ultimo;

// Initialize and set to global for back-compat
$GLOBALS['WP_Ultimo'] = WP_Ultimo();
