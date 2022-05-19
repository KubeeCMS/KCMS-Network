<?php
/**
 * WP Ultimo Dashboard Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Dashboard Admin Page.
 */
class Addons_Admin_Page extends Wizard_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-addons';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * This page has no parent, so we need to highlight another sub-menu.
	 *
	 * @since 2.0.0
	 * @var string
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
		'network_admin_menu' => 'wu_read_settings',
	);

	/**
	 * Should we hide admin notices on this page?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hide_admin_notices = false;

	/**
	 * Should we force the admin menu into a folded state?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $fold_menu = false;

	/**
	 * Holds the section slug for the URLs.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $section_slug = 'tab';

	/**
	 * Defines if the step links on the side are clickable or not.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $clickable_navigation = true;

	/**
	 * Caches the list of add-ons.
	 *
	 * @since 2.0.0
	 * @var null|array
	 */
	protected $addons;

	/**
	 * Allow child classes to add hooks to be run once the page is loaded.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
	 * @since 1.8.2
	 * @return void
	 */
	public function init() {

		parent::init();

		add_action('wp_ajax_serve_addons_list', array($this, 'serve_addons_list'));

	} // end init;

	/**
	 * Register forms
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {

		wu_register_form('addon_more_info', array(
			'render'  => array($this, 'display_more_info'),
			'handler' => array($this, 'install_addon'),
		));

	} // end register_forms;

	/**
	 * Displays the more info tab.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_more_info() {

		$addon_slug = wu_request('addon');

		$addon = wu_get_isset($this->get_addons_list(), $addon_slug);

		$upgrade_url = wu_network_admin_url('wp-ultimo-pricing', array(
			'checkout'      => 'true',
			'plan_id'       => '4675',
			'plan_name'     => 'wpultimo',
			'billing_cycle' => 'annual',
			'pricing_id'    => '3849',
			'currency'      => 'usd',
		));

		wu_get_template('base/addons/details', array(
			'upgrade_url' => $upgrade_url,
			'addon'       => (object) $addon,
			'addon_slug'  => $addon_slug,
			'license'     => \WP_Ultimo\License::get_instance(),
		));

		do_action('wu_form_scripts', false);

	} // end display_more_info;

	/**
	 * Installs a given add-on.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function install_addon() {

		if (!current_user_can('manage_network_plugins')) {

			$error = new \WP_Error('error', __('You do not have enough permissions to perform this task.', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

		$addon_slug = wu_request('addon');

		$addon = wu_get_isset($this->get_addons_list(), $addon_slug);

		$download_url = add_query_arg(array(
			'action'       => 'download',
			'slug'         => $addon_slug,
			'beta_program' => 2,
			'license_key'  => rawurlencode(\WP_Ultimo\License::get_instance()->get_license_key(true)),
		), 'https://versions.nextpress.co/updates/');

		/**
		 * We check if the URL is one of our websites
		 */
		$allowed_sites = array(
			'http://nextpress.co', 'https://nextpress.co',  // New Domain
			'http://versions.nextpress.co', 'https://versions.nextpress.co',  // New Domain
			'http://weare732.com', 'https://weare732.com'   // Old Updates Domain
		);

		if (defined('WP_DEBUG') && WP_DEBUG) {

			$allowed_sites[] = 'http://localhost';
			$allowed_sites[] = 'http://wp-ultimo.local';

		} // end if;

		$allowed = false;

		foreach ($allowed_sites as $allowed_site) {

			if (strpos($download_url, $allowed_site) === 0) {

				$allowed = true;

				break;

			} // end if;

		} // end foreach;

		if ($allowed) {

			// includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
			include_once(ABSPATH . 'wp-admin/includes/file.php');
			include_once(ABSPATH . 'wp-admin/includes/misc.php');
			include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

			$skin = new \Automatic_Upgrader_Skin(array());

			$upgrader = new \Plugin_Upgrader($skin);

			add_filter('https_ssl_verify', '__return_false', 2000);

			$results = $upgrader->install($download_url);

			remove_filter('https_ssl_verify', '__return_false', 2000);

			if (is_wp_error($results)) {

				wp_send_json_error($results);

			} // end if;

			$messages = $upgrader->skin->get_upgrade_messages();

			if (!in_array($upgrader->strings['process_success'], $messages, true)) {

				$error = new \WP_Error('error', array_pop($messages));

				wp_send_json_error($error);

			} // end if;

			wp_send_json_success(array(
				'redirect_url' => add_query_arg(array(
					's' => urlencode($addon['name']),
				), network_admin_url('plugins.php')),
			));

		} else {

			$error = new \WP_Error('insecure-url', __('You are trying to download an add-on from an insecure URL', 'wp-ultimo'));

			wp_send_json_error($error);

		} // end if;

	} // end install_addon;

	/**
	 * Enqueue the necessary scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		wp_enqueue_style('theme');

		wp_register_script('wu-addons', wu_get_asset('addons.js', 'js'), array('jquery', 'wu-vue', 'underscore'), wu_get_version(), true);

		wp_localize_script('wu-addons', 'wu_addons', array(
			'search'   => wu_request('s', ''),
			'category' => wu_request('tab', 'all'),
			'i18n'     => array(
				'all' => __('All Add-ons', 'wp-ultimo'),
			),
		));

		wp_enqueue_script('wu-addons');

	} // end register_scripts;

	/**
	 * Fetches the list of add-ons available.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_addons_list() {
		/*
		 * Checks if we have a cached version.
		 */
		if (is_array($this->addons)) {

			return $this->addons;

		} // end if;

		/*
		 * Check for local cache.
		 */
		if (!wu_is_debug()) {

			$addons_list = get_site_transient('wu-addons-list');

			if (is_array($addons_list) && !empty($addons_list)) {

				$this->addons = $addons_list;

				return $this->addons;

			} // end if;

		} // end if;

		$base_url = 'https://versions.nextpress.co/updates/';

		$remote_url = add_query_arg(array(
			'slug'              => 'wp-ultimo',
			'action'            => 'addons',
			'installed_version' => wu_get_version(),
		), $base_url);

		if (defined('WP_ULTIMO_DEVELOPER_KEY') && WP_ULTIMO_DEVELOPER_KEY) {

			$remote_url = add_query_arg('developer_key', WP_ULTIMO_DEVELOPER_KEY, $remote_url);

		} // end if;

		$response = wp_remote_get($remote_url, array(
			'sslverify' => false,
		));

		if (is_wp_error($response)) {

			return array();

		} // end if;

		$data = wp_remote_retrieve_body($response);

		$data = json_decode($data, true);

		if (!is_array($data)) {

			return array();

		} // end if;

		/*
		 * Adds missing keys
		 */
		foreach ($data as $slug => $item) {
			/*
			 * Checks if this is a free add-on.
			 */
			$item['free'] = wu_get_isset($item, 'free', false);

			/*
			 * Checks if the plugin is installed.
			 */
			$item['installed'] = $this->is_plugin_installed($slug);

			$this->addons[$slug] = $item;

		} // end foreach;

		set_transient('wu-addons-list', $this->addons, 2 * DAY_IN_SECONDS);

		return $this->addons;

	} // end get_addons_list;

	/**
	 * Checks if a given plugin is installed.
	 *
	 * @since 2.0.0
	 * @param string $plugin_slug The plugin slug to check.
	 * @return boolean
	 */
	public function is_plugin_installed($plugin_slug) {

		$plugin_keys = array_keys(get_plugins());

		$installed_plugins = implode(' - ', $plugin_keys);

		return stristr($installed_plugins, $plugin_slug) !== false;

	} // end is_plugin_installed;

	/**
	 * Gets the list of addons from the remote server.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function serve_addons_list() {

		$addons_list = $this->get_addons_list();

		wp_send_json_success($addons_list);

	} // end serve_addons_list;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('Add-ons', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Add-ons', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Every child class should implement the output method to display the contents of the page.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output() {

		$more_info_url = wu_get_form_url('addon_more_info', array(
			'width' => 768,
			'addon' => 'ADDON_SLUG',
		));

		wu_get_template('base/addons', array(
			'screen'               => get_current_screen(),
			'page'                 => $this,
			'classes'              => '',
			'sections'             => $this->get_sections(),
			'current_section'      => $this->get_current_section(),
			'clickable_navigation' => $this->clickable_navigation,
			'more_info_url'        => $more_info_url,
			'license'              => \WP_Ultimo\License::get_instance(),
		));

	} // end output;

	/**
	 * Returns the list of settings sections.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_sections() {

		return array(
			'all'           => array(
				'title' => __('All Add-ons', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-grid',
			),
			'premium'       => array(
				'title' => __('Premium', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-rocket',
			),
			'free'          => array(
				'title' => __('Free', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-pin',
			),
			'gateways'      => array(
				'title' => __('Gateways', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-credit-card',
			),
			'growth'      => array(
				'title' => __('Growth & Scaling', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-line-graph',
			),
			'integrations'  => array(
				'title' => __('Integrations', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-power-plug',
			),
			'customization' => array(
				'title' => __('Customization', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-edit',
			),
			'admin theme'   => array(
				'title' => __('Admin Themes', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-palette',
			),
			'monetization'  => array(
				'title' => __('Monetization', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-credit',
			),
			'migrators'     => array(
				'title' => __('Migrators', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-publish',
			),
			'separator' => array(
				'separator' => true,
			),
			'marketplace'   => array(
				'title' => __('Marketplace', 'wp-ultimo'),
				'icon'  => 'dashicons-wu-shop',
			),
		);

	} // end get_sections;

	/**
	 * Default handler for step submission. Simply redirects to the next step.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function default_handler() {

		WP_Ultimo()->settings->save_settings($_POST);

		wp_redirect(add_query_arg('updated', 1, wu_get_current_url()));

		exit;

	} // end default_handler;

} // end class Addons_Admin_Page;
