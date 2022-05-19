<?php
/**
 * WP Ultimo System Info Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo System Info Admin Page.
 */
class System_Info_Admin_Page extends Base_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-system-info';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * If this is a submenu, we need a parent menu to attach this to
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * Allows us to highlight another menu page, if this page has no parent page at all.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $highlight_menu_slug = 'wp-ultimo-settings';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * To add a page to the regular admin (wp-admin/), use: 'admin_menu' => 'capability_here'
	 * To add a page to the network admin (wp-admin/network), use: 'network_admin_menu' => 'capability_here'
	 * To add a page to the user (wp-admin/user) admin, use: 'user_admin_menu' => 'capability_here'
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $supported_panels = array(
		'network_admin_menu' => 'manage_network',
	);

	/**
	 * Allow child classes to add further initializations.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function init() {

		add_action('wp_ajax_wu_generate_text_file_system_info', array($this, 'generate_text_file_system_info'));

	} // end init;

	/**
	 * Allow child classes to register scripts and styles that can be loaded on the output function, for example.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_scripts() {

		wp_enqueue_script('dashboard');

		wp_enqueue_script('clipboard');

	} // end register_scripts;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		$screen = get_current_screen();

		foreach ($this->get_data() as $name_type => $data) {

			add_meta_box('wp-table-system-info-' . sanitize_title($name_type), $name_type, function() use ($data) {

				$this->output_table_system_info($data);

			}, $screen->id, 'normal', null);

		} // end foreach;

	} // end register_widgets;

	/**
	 * Display system info Table
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Data.
	 * @return void
	 */
	public function output_table_system_info($data) {

		$screen = get_current_screen();

		wu_get_template('system-info/system-info-table', array(
			'data'   => $data,
			'screen' => $screen,
		));

	}  // end output_table_system_info;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('System Info', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('System Info', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Allows admins to rename the sub-menu (first item) for a top-level page.
	 *
	 * @since 2.0.0
	 * @return string False to use the title menu or string with sub-menu title.
	 */
	public function get_submenu_title() {

		return __('Dashboard', 'wp-ultimo');

	} // end get_submenu_title;

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output() {

		$screen = get_current_screen();

		wu_get_template('system-info/system-info', array(
			'data'   => $this->get_data(),
			'screen' => $screen,
		));

	} // end output;

	/**
	 * Get data for system info
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_data() {

		global $wp_filesystem, $wpdb;

		$pad_spaces = 45;

		$theme   = wp_get_theme();
		$browser = $this->get_browser();

		$plugins                  = $this->get_all_plugins();
		$active_plugins           = $this->get_active_plugins();
		$active_plugins_main_site = $this->get_active_plugins_on_main_site();

		$memory_limit       = (int) str_replace('M', '', ini_get('memory_limit'));
		$memory_usage       = $this->get_memory_usage();
		$max_execution_time = sprintf(__('%s seconds', 'wp-ultimo'), ini_get('max_execution_time'));

		$all_options            = $this->get_all_options();
		$all_options_serialized = serialize($all_options);
		$all_options_bytes      = round(mb_strlen($all_options_serialized, '8bit') / 1024, 2);
		$all_options_transients = $this->get_transients_in_options($all_options);

		$array_active_plugins = array();

		$array_constants_options = array(
			'SAVEQUERIES',
			'WP_DEBUG',
			'WP_DEBUG_DISPLAY',
			'WP_DEBUG_LOG',
			'WP_DISABLE_FATAL_ERROR_HANDLER',
			'SCRIPT_DEBUG',
			'WP_ENV',
		);

		$array_constants = array();

		foreach ($array_constants_options as $constant) {

			$array_constants[] = array(
				'tooltip' => '',
				'title'   => $constant,
				'value'   => defined($constant) ? (is_bool(constant($constant)) ? __('Enabled', 'wp-ultimo') : constant($constant)) : __('Disabled', 'wp-ultimo'),
			);

		} // end foreach;

		foreach ($plugins as $plugin_path => $plugin) {

			if (in_array($plugin_path, array_keys($active_plugins))) {

				$plugin_uri = '';

				if (isset($plugin['PluginURI'])) {

					$plugin_uri = ' (' . $plugin['PluginURI'] . ')';

				} // end if;

				$array_active_plugins[] = array(
					'tooltip' => '',
					'title'   => $plugin['Name'],
					'value'   => $plugin['Version'] . $plugin_uri
				);

			} // end if;

		} // end foreach;

		$array_active_plugins_main_site = array();

		foreach ($plugins as $plugin_path => $plugin) {

			if (in_array($plugin_path, $active_plugins_main_site)) {

				$plugin_uri = '';

				if (isset($plugin['PluginURI'])) {

					$plugin_uri = ' (' . $plugin['PluginURI'] . ')';

				} // end if;

				$array_active_plugins_main_site[] = array(
					'tooltip' => '',
					'title'   => $plugin['Name'],
					'value'   => $plugin['Version'] . $plugin_uri
				);

			} // end if;

		} // end foreach;

		$wpultimo_settings = array();

		foreach ($this->get_all_wp_ultimo_settings() as $setting => $value) {

			if (is_array($value)) {

				continue;

			} // end if;

			$wpultimo_settings[$setting] = array(
				'tooltip' => '',
				'title'   => $setting,
				'value'   => $value
			);

		} // end foreach;

		global $wpdb;

		$array_wu_tables = array();

		foreach ($wpdb->ms_global_tables as $key => $value) {

			if (substr($value, 0, 3) === 'wu_' && !array_key_exists($value, $array_wu_tables)) {

				$array_wu_tables[$value] = array(
					'tooltip' => '',
					'title'   => $value,
					'value'   => get_network_option(null, "wpdb_{$value}_version")
				);

			} // end if;

		} // end foreach;

		return apply_filters('wu_system_info_data', array(
			'WordPress and System Settings' => array(
				'wp-ultimo-version'      => array(
					'tooltip' => 'WP Ultimo current version installed locally',
					'title'   => 'WP Ultimo Version',
					'value'   => WP_Ultimo()->version
				),
				'wordpress-version'      => array(
					'tooltip' => '',
					'title'   => 'WordPress Version',
					'value'   => get_bloginfo('version')
				),
				'php-version'            => array(
					'tooltip' => '',
					'title'   => 'PHP Version',
					'value'   => PHP_VERSION
				),
				'mysql-version'          => array(
					'tooltip' => '',
					'title'   => 'MySQL Version ',
					'value'   => $wpdb->db_version()
				),
				'web-server'             => array(
					'tooltip' => '',
					'title'   => 'Web Server',
					'value'   => $_SERVER['SERVER_SOFTWARE']
				),
				'wordpress-url'          => array(
					'tooltip' => '',
					'title'   => 'WordPress URL',
					'value'   => get_bloginfo('wpurl')
				),
				'home-url'               => array(
					'tooltip' => '',
					'title'   => 'Home URL',
					'value'   => get_bloginfo('url')
				),
				'content-directory'      => array(
					'tooltip' => '',
					'title'   => 'Content Directory',
					'value'   => WP_CONTENT_DIR
				),
				'content-url'            => array(
					'tooltip' => '',
					'title'   => 'Content URL',
					'value'   => WP_CONTENT_URL
				),
				'plugins-directory'      => array(
					'tooltip' => '',
					'title'   => 'Plugins Directory',
					'value'   => WP_PLUGIN_DIR
				),
				'pluguins-url'           => array(
					'tooltip' => '',
					'title'   => 'Plugins URL',
					'value'   => WP_PLUGIN_URL
				),
				'uploads-directory'      => array(
					'tooltip' => '',
					'title'   => 'Uploads Directory',
					'value'   => (defined('UPLOADS') ? UPLOADS : WP_CONTENT_DIR . '/uploads')
				),
				'pluguins-url'           => array(
					'tooltip' => '',
					'title'   => 'Cookie Domain',
					'value'   => defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN ? COOKIE_DOMAIN : __('Disabled', 'wp-ultimo') : __('Not set', 'wp-ultimo')
				),
				'multisite-active'       => array(
					'tooltip' => '',
					'title'   => 'Multi-Site Active',
					'value'   => is_multisite() ? __('Yes', 'wp-ultimo') : __('No', 'wp-ultimo')
				),
				'php-current-time-gmt'       => array(
					'tooltip' => '',
					'title'   => 'PHP Current Time - GMT',
					'value'   => wu_get_current_time('mysql', true),
				),
				'timezone'       => array(
					'tooltip' => '',
					'title'   => 'Timezone',
					'value'   => wp_timezone_string(),
				),
				'php-current-time'       => array(
					'tooltip' => '',
					'title'   => 'PHP Current Time - with Timezone',
					'value'   => wu_get_current_time()
				),
				'database-current-time'  => array(
					'tooltip' => '',
					'title'   => 'Database Current Time',
					'value'   => gmdate('Y-m-d H:i:s', strtotime($wpdb->get_row('SELECT NOW() as time;')->time))
				),
				'php-curl-support'       => array(
					'tooltip' => '',
					'title'   => 'PHP cURL Support',
					'value'   => function_exists('curl_init') ? __('Yes', 'wp-ultimo') : __('No', 'wp-ultimo')
				),
				'php-gd-time'            => array(
					'tooltip' => '',
					'title'   => 'PHP GD Support',
					'value'   => function_exists('gd_info') ? __('Yes', 'wp-ultimo') : __('No', 'wp-ultimo')
				),
				'php-memory-limit'       => array(
					'tooltip' => '',
					'title'   => 'PHP Memory Limit',
					'value'   => $memory_limit . 'M'
				),
				'php-memory-usage'       => array(
					'tooltip' => '',
					'title'   => 'PHP Memory Usage',
					'value'   => $memory_usage . 'M (' . round($memory_usage / $memory_limit * $pad_spaces, 0) . '%)'
				),
				'php-post-max-size'      => array(
					'tooltip' => '',
					'title'   => 'PHP Post Max Size',
					'value'   => ini_get('post_max_size')
				),
				'php-upload-max-size'    => array(
					'tooltip' => '',
					'title'   => 'PHP Upload Max Size',
					'value'   => ini_get('upload_max_filesize')
				),
				'php-max-execution-time' => array(
					'tooltip' => '',
					'title'   => 'PHP Max Execution Time',
					'value'   => $max_execution_time
				),
				'php-allow-url-fopen'    => array(
					'tooltip' => '',
					'title'   => 'PHP Allow URL Fopen',
					'value'   => ini_get('allow_url_fopen')
				),
				'php-max-file-uploads'   => array(
					'tooltip' => '',
					'title'   => 'PHP Max File Uploads',
					'value'   => ini_get('max_file_uploads')
				),
				'wp-options-count'       => array(
					'tooltip' => '',
					'title'   => 'WP Options Count',
					'value'   => count($all_options)
				),
				'wp-options-size'        => array(
					'tooltip' => '',
					'title'   => 'WP Options Size',
					'value'   => $all_options_bytes . 'kb'
				),
				'wp-options-transients'  => array(
					'tooltip' => '',
					'title'   => 'WP Options Transients',
					'value'   => count($all_options_transients)
				),
				'wp-debug'               => array(
					'tooltip' => '',
					'title'   => 'WP Options Transients',
					'value'   => defined('WP_DEBUG') ? WP_DEBUG ? __('Enabled', 'wp-ultimo') : __('Disabled', 'wp-ultimo') : __('Not set', 'wp-ultimo')
				),
				'script-debug'           => array(
					'tooltip' => '',
					'title'   => 'WP Options Transients',
					'value'   => defined('SCRIPT_DEBUG') ? SCRIPT_DEBUG ? __('Enabled', 'wp-ultimo') : __('Disabled', 'wp-ultimo') : __('Not set', 'wp-ultimo')
				),
				'save-queries'           => array(
					'tooltip' => '',
					'title'   => 'WP Options Transients',
					'value'   => defined('SAVEQUERIES') ? SAVEQUERIES ? __('Enabled', 'wp-ultimo') : __('Disabled', 'wp-ultimo') : __('Not set', 'wp-ultimo')
				),
				'autosave-interval'      => array(
					'tooltip' => '',
					'title'   => 'WP Options Transients',
					'value'   => defined('AUTOSAVE_INTERVAL') ? AUTOSAVE_INTERVAL ? AUTOSAVE_INTERVAL : __('Disabled', 'wp-ultimo') : __('Not set', 'wp-ultimo')
				),
				'wp_post_revisions'      => array(
					'tooltip' => '',
					'title'   => 'WP Options Transients',
					'value'   => defined('WP_POST_REVISIONS') ? WP_POST_REVISIONS ? WP_POST_REVISIONS : __('Disabled', 'wp-ultimo') : __('Not set', 'wp-ultimo')
				),
				'disable_wp_cron'        => array(
					'tooltip' => '',
					'title'   => 'DISABLE_WP_CRON',
					'value'   => defined('DISABLE_WP_CRON') ? DISABLE_WP_CRON ? DISABLE_WP_CRON : __('Yes', 'wp-ultimo') : __('No', 'wp-ultimo')
				),
				'wp_lang'                => array(
					'tooltip' => '',
					'title'   => 'WPLANG',
					'value'   => defined('WPLANG') ? WPLANG ? WPLANG : __('Yes', 'wp-ultimo') : __('No', 'wp-ultimo')
				),
				'wp_memory_limit'        => array(
					'tooltip' => '',
					'title'   => 'WP_MEMORY_LIMIT',
					'value'   => (defined('WP_MEMORY_LIMIT') && WP_MEMORY_LIMIT) ? WP_MEMORY_LIMIT : __('Not set', 'wp-ultimo')
				),
				'wp_max_memory_limit'    => array(
					'tooltip' => '',
					'title'   => 'WP_MAX_MEMORY_LIMIT',
					'value'   => (defined('WP_MAX_MEMORY_LIMIT') && WP_MAX_MEMORY_LIMIT) ? WP_MAX_MEMORY_LIMIT : __('Not set', 'wp-ultimo')
				),
				'operating-system'       => array(
					'tooltip' => '',
					'title'   => 'Operating System',
					'value'   => $browser['platform']
				),
				'browser'                => array(
					'tooltip' => '',
					'title'   => 'Browser',
					'value'   => $browser['name'] . ' ' . $browser['version']
				),
				'user-agent'             => array(
					'tooltip' => '',
					'title'   => 'User Agent',
					'value'   => $browser['user_agent']
				),

			),

			'Active Theme'                  => array(
				'active-theme' => array(
					'tooltip' => '',
					'title'   => 'Active Theme',
					'value'   => $theme->get('Name') . ' - ' . $theme->get('Version') . '(' . $theme->get('ThemeURI') . ')'
				),
			),

			'Active Plugins'                => $array_active_plugins,
			'Active Plugins on Main Site'   => $array_active_plugins_main_site,

			'WP Ultimo Database Status'     => $array_wu_tables,

			'WP Ultimo Core Settings'       => array_merge(
				array(
					'logs-directory' => array(
						'tooltip' => '',
						'title'   => 'Logs Directory',
						'value'   => is_writable(Logger::get_logs_folder()) ? __('Writable', 'wp-ultimo') : __('Not Writable', 'wp-ultimo')
					),
				),
				$wpultimo_settings),

			'Defined Constants'             => $array_constants,

		)
		);

	} // end get_data;

	/**
	 * Generate text file of system info data
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function generate_text_file_system_info() {

		$file_name = sprintf("$this->id-%s.txt", gmdate('Y-m-d'));
		$txt       = fopen($file_name, 'w') or die('Unable to open file!');

		foreach ($this->get_data() as $type) {

			foreach ($type as $item) {

				fwrite($txt, $item['title'] . ': ' . $item['value'] . PHP_EOL);

			} // end foreach;

		} // end foreach;

		fclose($txt);

		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . basename($file_name));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file_name));
		header('Content-Type: text/plain');
		readfile($file_name);

		die;

	}  // end generate_text_file_system_info;

	/**
	 * Get browser data
	 *
	 * @since 1.1.5
	 * @return array
	 */
	public function get_browser() {

		// http://www.php.net/manual/en/function.get-browser.php#101125.
		// Cleaned up a bit, but overall it's the same.

		$user_agent   = $_SERVER['HTTP_USER_AGENT'];
		$browser_name = 'Unknown';
		$platform     = 'Unknown';
		$version      = '';

		// First get the platform
		if (preg_match('/linux/i', $user_agent)) {

			$platform = 'Linux';

		} elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {

			$platform = 'Mac';

		} elseif (preg_match('/windows|win32/i', $user_agent)) {

			$platform = 'Windows';

		} // end if;

		// Next get the name of the user agent yes seperately and for good reason
		if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {

			$browser_name       = 'Internet Explorer';
			$browser_name_short = 'MSIE';

		} elseif (preg_match('/Firefox/i', $user_agent)) {

			$browser_name       = 'Mozilla Firefox';
			$browser_name_short = 'Firefox';

		} elseif (preg_match('/Chrome/i', $user_agent)) {

			$browser_name       = 'Google Chrome';
			$browser_name_short = 'Chrome';

		} elseif (preg_match('/Safari/i', $user_agent)) {

			$browser_name       = 'Apple Safari';
			$browser_name_short = 'Safari';

		} elseif (preg_match('/Opera/i', $user_agent)) {

			$browser_name       = 'Opera';
			$browser_name_short = 'Opera';

		} elseif (preg_match('/Netscape/i', $user_agent)) {

			$browser_name       = 'Netscape';
			$browser_name_short = 'Netscape';

		} // end if;

		// Finally get the correct version number
		$known   = array('Version', $browser_name_short, 'other');
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

		if (!preg_match_all($pattern, $user_agent, $matches)) {
			// We have no matching number just continue
		} // end if;

		// See how many we have
		$i = count($matches['browser']);

		if ($i != 1) {

			// We will have two since we are not using 'other' argument yet
			// See if version is before or after the name
			if (strripos($user_agent, 'Version') < strripos($user_agent, $browser_name_short)) {

				$version = $matches['version'][0];

			} else {

				$version = $matches['version'][1];

			} // end if;

		} else {

			$version = $matches['version'][0];

		} // end if;

		// Check if we have a number
		if ($version == null || $version == '') {

			$version = '?';

		} // end if;

		return array(
			'user_agent' => $user_agent,
			'name'       => $browser_name,
			'version'    => $version,
			'platform'   => $platform,
			'pattern'    => $pattern
		);

	} // end get_browser;

	/**
	 * Get list of all the plugins
	 *
	 * @return array
	 */
	public function get_all_plugins() {

		return get_plugins();

	} // end get_all_plugins;

	/**
	 * Get only the active plugins
	 *
	 * @return array
	 */
	public function get_active_plugins() {

		return (array) get_site_option( 'active_sitewide_plugins', array() );

	} // end get_active_plugins;

	/**
	 * Get only the active plugins on main site
	 *
	 * @return array
	 */
	public function get_active_plugins_on_main_site() {

		return (array) get_option('active_plugins', array());

	}  // end get_active_plugins_on_main_site;

	/**
	 * Get memory usage
	 *
	 * @return int
	 */
	public function get_memory_usage() {

		return round(memory_get_usage() / 1024 / 1024, 2);

	} // end get_memory_usage;

	/**
	 * Get all the ioptions
	 *
	 * @return array
	 */
	public function get_all_options() {

		// Not to be confused with the core deprecated get_alloptions
		return wp_load_alloptions();

	}  // end get_all_options;

	/**
	 * Return all the desired WP Ultimo Settings
	 *
	 * @since 1.1.5
	 * @return array
	 */
	public function get_all_wp_ultimo_settings() {

		$exclude = array(
			'email',
			'logo',
			'color',
			'from_name',
			'paypal',
			'stripe',
			'terms_content',
			'wu-',
			'license_key',
			'api-',
			'manual_payment_instructions',
		);

		$include = array('enable');

		$return_settings = array();

		$settings = new \WP_Ultimo\Settings;

		foreach ($settings->get_all() as $setting => $value) {

			$add = true;

			foreach ($exclude as $ex) {

				if (stristr($setting, $ex) !== false) {

					$add = false;

					break;

				} // end if;

			} // end foreach;

			if ($add) {

				$return_settings[$setting] = $value;

			} // end if;

		} // end foreach;

		return $return_settings;

	} // end get_all_wp_ultimo_settings;

	/**
	 * Get the transients om the options
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public function get_transients_in_options($options) {

		$transients = array();

		foreach ($options as $name => $value) {

			if (stristr($name, 'transient')) {

				$transients[$name] = $value;

			} // end if;

		} // end foreach;

		return $transients;

	} // end get_transients_in_options;

} // end class System_Info_Admin_Page;
