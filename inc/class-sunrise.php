<?php
/**
 * WP Ultimo activation and deactivation hooks
 *
 * @package WP_Ultimo
 * @subpackage Sunrise
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Development\Toolkit;

/**
 * WP Ultimo activation and deactivation hooks
 *
 * @since 2.0.0
 */
class Sunrise {

	/**
	 * Keeps the current sunrise.php version.
	 *
	 * @var string
	 */
	static $version = '2.0.0.7';

	/**
	 * Keeps the sunrise meta cached after the first read.
	 *
	 * @var null|array
	 */
	static $sunrise_meta;

	/**
	 * Initializes sunrise and loads additional elements if needed.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public static function init() {

		require_once __DIR__ . '/functions/sunrise.php';

		/**
		 * Load development tools as soon as possible.
		 */
		\WP_Ultimo\Sunrise::load_development_mode();

		/**
		 * Load the core apis we need from the start.
		 */
		require_once __DIR__ . '/functions/helper.php';

		require_once __DIR__ . '/functions/fs.php';

		require_once __DIR__ . '/functions/debug.php';

		require_once __DIR__ . '/functions/debug.php';

		/**
		 * Domain mapping needs to be loaded
		 * before anything else.
		 */
		\WP_Ultimo\Sunrise::load_domain_mapping();

		/**
		 * Enqueue the main hooks that deal with Sunrise
		 * loading and maintenance.
		 */
		add_action('ms_loaded', array('\WP_Ultimo\Sunrise', 'load'));

		add_action('ms_loaded', array('\WP_Ultimo\Sunrise', 'loaded'), 999);

		add_action('init', array('\WP_Ultimo\Sunrise', 'maybe_tap_on_init'));

		add_filter('wu_system_info_data', array('\WP_Ultimo\Sunrise', 'system_info'));

	} // end init;

	/**
	 * Checks if all the requirements for sunrise loading are in place.
	 *
	 * In order to be completely loaded, we need two
	 * criteria to be fulfilled:
	 *
	 * 1. The setup wizard must have been finalized;
	 * 2. Ultimo is active - which is determined by the sunrise meta file.
	 *
	 * @since 2.0.11
	 * @return boolean
	 */
	public static function should_startup() {

		$setup_finished = get_network_option(null, 'wu_setup_finished', false);

		$should_load_sunrise = wu_should_load_sunrise();

		return $setup_finished && $should_load_sunrise;

	} // end should_startup;

	/**
	 * Load dependencies, if we need them somewhere.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public static function load_dependencies() {

		require_once dirname(__DIR__) . '/dependencies/autoload.php';

		require_once __DIR__ . '/deprecated/early-deprecated.php';

		require_once __DIR__ . '/deprecated/mercator.php';

		require_once __DIR__ . '/class-autoloader.php';

		require_once __DIR__ . '/functions/site.php';

		require_once __DIR__ . '/functions/debug.php';

		require_once __DIR__ . '/functions/url.php';

		require_once __DIR__ . '/functions/number-helpers.php';

		require_once __DIR__ . '/functions/array-helpers.php';

		/*
		 * Setup autoloader
		 */
		\WP_Ultimo\Autoloader::init();

	} // end load_dependencies;

	/**
	 * Loads domain mapping before anything else.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public static function load_domain_mapping() {

		$should_startup = \WP_Ultimo\Sunrise::should_startup();

		if ($should_startup) {

			\WP_Ultimo\Sunrise::load_dependencies();

			\WP_Ultimo\Domain_Mapping::get_instance();

		} // end if;

	} // end load_domain_mapping;

	/**
	 * Loads the Sunrise components, if needed.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function load() {

		$should_startup = \WP_Ultimo\Sunrise::should_startup();

		if ($should_startup) {

			\WP_Ultimo\Sunrise::load_dependencies();

			/*
			 * Primary Domain capabilities
			 */
			\WP_Ultimo\Domain_Mapping\Primary_Domain::get_instance();

			/*
			 * Handles WP Ultimo core updates
			 */
			\WP_Ultimo\Core_Updates::get_instance();

			/*
			 * Adds backwards compatibility code for the domain mapping.
			 */
			\WP_Ultimo\Compat\Domain_Mapping_Compat::get_instance();

			/*
			 * Plugin Limits
			 */
			\WP_Ultimo\Limits\Plugin_Limits::get_instance();

			/*
			 * Theme Limits
			 */
			\WP_Ultimo\Limits\Theme_Limits::get_instance();

			/**
			 * Define the WP Ultimo main debug constant.
			 */
			!defined('WP_ULTIMO_DEBUG') && define('WP_ULTIMO_DEBUG', false);

		} // end if;

	} // end load;

	/**
	 * Adds an additional hook that runs after ms_loaded.
	 *
	 * This is needed since there isn't really a good hook we can use
	 * that gets triggered right after ms_loaded. The hook here
	 * only runs on a very high priority number on ms_loaded,
	 * giving other modules time to register their hooks so they
	 * can be run here.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public static function loaded() {

		do_action('wu_sunrise_loaded');

	} // end loaded;

	/**
	 * Checks if we need to upgrade the sunrise version on wp-content
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function manage_sunrise_updates() {
		/*
		 * Get current version of the sunrise.php file
		 */
		$old_version = defined('WP_ULTIMO_SUNRISE_VERSION') ? WP_ULTIMO_SUNRISE_VERSION : '0.0.1';

		if (version_compare($old_version, self::$version, '<')) {

			\WP_Ultimo\Sunrise::try_upgrade();

		} // end if;

	} // end manage_sunrise_updates;

	/**
	 * Upgrades the sunrise file, if necessary.
	 *
	 * @todo: lots of logic needs to be here to deal with other plugins' code on sunrise.php
	 * @since 2.0.0
	 * @return true|\WP_Error
	 */
	public static function try_upgrade() {

		$possible_sunrises = array(
			WP_PLUGIN_DIR . '/wp-ultimo/sunrise.php',
			WPMU_PLUGIN_DIR . '/wp-ultimo/sunrise.php',
		);

		$sunrise_found = false;

		$error = false;

		$location = WP_CONTENT_DIR . '/sunrise.php';

		foreach ($possible_sunrises as $new_file) {

			if (!file_exists($new_file)) {

				continue;

			} // end if;

			$sunrise_found = true;

			$copy_results = @copy($new_file, $location); // phpcs:ignore

			if (!$copy_results) {

				$error = error_get_last();

				continue;

			} // end if;

			wu_log_add('sunrise', __('Sunrise upgrade attempt succeeded.', 'wp-ultimo'));

			return true;

		} // end foreach;

		if ($sunrise_found === false) {

			$error = array(
				'message' => __('File not found.', 'wp-ultimo'),
			);

		} // end if;

		if (!empty($error)) {

			wu_log_add('sunrise', $error['message']);

			/* translators: the placeholder is an error message */
			return new \WP_Error('error', sprintf(__('Sunrise copy failed: %s', 'wp-ultimo'), $error['message']));

		} // end if;

	} // end try_upgrade;

	/**
	 * Reads the sunrise meta file and loads it to the static cache.
	 *
	 * It only reaches the filesystem on the first read, keeping
	 * a cache of the results on a static class property then on.
	 *
	 * @since 2.0.11
	 * @return array
	 */
	protected static function read_sunrise_meta() {

		if (is_array(\WP_Ultimo\Sunrise::$sunrise_meta)) {

			return \WP_Ultimo\Sunrise::$sunrise_meta;

		} // end if;

		$sunrise_meta = get_network_option(null, 'wu_sunrise_meta', null);

		$existing = array();

		if ($sunrise_meta) {

			$existing = $sunrise_meta;

			self::$sunrise_meta = $existing;

		} // end if;

		return $existing;

	} // end read_sunrise_meta;

	/**
	 * Method for imputing Sunrise data at wp-ultimo-system-info table.
	 *
	 * @since 2.0.11
	 * @param array $sys_info Array containing WP Ultimo installation info.
	 * @return array Returns the array, modified with the sunrise data.
	 */
	public static function system_info($sys_info) {

		$data = Sunrise::read_sunrise_meta();

		$sys_info = array_merge($sys_info,
			array(
				'Sunrise Data' => array(
					'sunrise-status'           => array(
						'tooltip' => '',
						'title'   => 'Active',
						'value'   => $data['active'] ? 'Enabled' : 'Disabled',
					),
					'sunrise-data'             => array(
						'tooltip' => '',
						'title'   => 'Version',
						'value'   => Sunrise::$version
					),
					'sunrise-created'          => array(
						'tooltip' => '',
						'title'   => 'Created',
						'value'   => gmdate('Y-m-d @ H:i:s', $data['created']),
					),
					'sunrise-last-activated'   => array(
						'tooltip' => '',
						'title'   => 'Last Activated',
						'value'   => gmdate('Y-m-d @ H:i:s', $data['last_activated']),
					),
					'sunrise-last-deactivated' => array(
						'tooltip' => '',
						'title'   => 'Last Deactivated',
						'value'   => gmdate('Y-m-d @ H:i:s', $data['last_deactivated']),
					),
					'sunrise-last-modified'    => array(
						'tooltip' => '',
						'title'   => 'Last Modified',
						'value'   => gmdate('Y-m-d @ H:i:s', $data['last_modified']),
					)
				),
			)
		);

		return $sys_info;

	} // end system_info;

	/**
	 * Checks if the sunrise extra modules need to be loaded.
	 *
	 * @since 2.0.11
	 * @return boolean
	 */
	public static function should_load_sunrise() {

		$meta = \WP_Ultimo\Sunrise::read_sunrise_meta();

		return wu_get_isset($meta, 'active', false);

	}  // end should_load_sunrise;

	/**
	 * Makes sure the meta file accurately reflects the state of the main plugin.
	 *
	 * @since 2.0.11
	 * @return bool
	 */
	public static function maybe_tap_on_init() {

		$state = function_exists('WP_Ultimo') && WP_Ultimo()->is_loaded();

		return \WP_Ultimo\Sunrise::maybe_tap($state ? 'activating' : 'deactivating');

	} // end maybe_tap_on_init;

	/**
	 * Updates the sunrise meta file, if an update is due.
	 *
	 * @since 2.0.11
	 *
	 * @param string $mode Either activating or deactivating.
	 * @return bool
	 */
	public static function maybe_tap($mode = 'activating') {

		$meta = \WP_Ultimo\Sunrise::read_sunrise_meta();

		$is_active = isset($meta['active']) && $meta['active'];

		if ($is_active && $mode === 'activating') {

			return false;

		} elseif (!$is_active && $mode === 'deactivating') {

			return false;

		} // end if;

		return (bool) \WP_Ultimo\Sunrise::tap($mode, $meta);

	} // end maybe_tap;

	/**
	 * Updates the sunrise meta file.
	 *
	 * @since 2.0.11
	 *
	 * @param string $mode Either activating or deactivating.
	 * @param array  $existing Existing meta file values.
	 * @return bool
	 */
	protected static function tap($mode = 'activating', $existing = array()) {

		$now = gmdate('U');

		$to_save = wp_parse_args($existing, array(
			'active'           => false,
			'created'          => $now,
			'last_activated'   => 'unknown',
			'last_deactivated' => 'unknown',
		));

		if ($mode === 'activating') {

			$to_save['active']         = true;
			$to_save['last_activated'] = $now;

		} elseif ($mode === 'deactivating') {

			$to_save['active']           = false;
			$to_save['last_deactivated'] = $now;

		} else {

			return;

		} // end if;

		$to_save['last_modified'] = $now;

		update_network_option(null, 'wu_sunrise_meta', $to_save);

	} // end tap;

	/**
	 * Load development modes, if we need too.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	protected static function load_development_mode() {

		$should_load_tools = wu_load_dev_tools(false);

		\WP_Ultimo\Sunrise::setup_whoops($should_load_tools);

		\WP_Ultimo\Sunrise::setup_development_hooks($should_load_tools);

	} // end load_development_mode;

	/**
	 * Adds whoops for better debugging.
	 *
	 * @since 2.0.0
	 * @param boolean $should_load_tools Path to the lib to load or false.
	 * @return void
	 */
	protected static function setup_whoops($should_load_tools = false) {

		/**
		 * Some tests require environments where whoops is not loaded.
		 * This constant makes sure we can bypass the whoops loading process.
		 */
		if (defined('WP_ULTIMO_DISABLE_WHOOPS') && WP_ULTIMO_DISABLE_WHOOPS) {

			return;

		} // end if;

		/*
		 * If the vendor folder exists, we are
		 * for sure, inside a dev environment.
		 */
		if ($should_load_tools) {

			/**
			 * Actually load the vendor dependencies.
			 */
			require_once $should_load_tools;

			error_reporting(E_ALL ^ (E_USER_NOTICE | E_USER_DEPRECATED));

			$whoops = new \Whoops\Run;

			/*
			 * Only keep errors above notice from other plugins.
			 */
			$whoops->silenceErrorsInPaths('/.*plugins\/(?!wp-ultimo).*/', E_USER_NOTICE | E_NOTICE);

			$default_handler = new \Whoops\Handler\PrettyPageHandler;

			$default_handler->setEditor('vscode');

			$whoops->pushHandler($default_handler);

			if (\Whoops\Util\Misc::isAjaxRequest()) {

				/**
				 * Exclude Freemius and WP Error deprecation notices and errors.
				 */
				$whoops->silenceErrorsInPaths('/freemius/', E_USER_NOTICE | E_NOTICE);

				$whoops->silenceErrorsInPaths('/wp-includes\/functions.php/', E_USER_NOTICE | E_NOTICE);

				$json_handler = new \Whoops\Handler\JsonResponseHandler;

				$json_handler->addTraceToOutput(true);

				$whoops->pushHandler($json_handler);

			} // end if;

			$whoops->register();

		} // end if;

	} // end setup_whoops;

	/**
	 * Load the development hooks.
	 *
	 * @since 2.0.11
	 *
	 * @param boolean $should_load_tools Path to the lib to load or false.
	 * @return void
	 */
	public static function setup_development_hooks($should_load_tools = false) {

		if ($should_load_tools) {

			add_action('ms_loaded', function() {

				class_exists(Toolkit::class) && Toolkit::get_instance();

			}, 20);

		} // end if;

	} // end setup_development_hooks;

	// phpcs:ignore
	private function __construct() {} // end __construct;

} // end class Sunrise;
