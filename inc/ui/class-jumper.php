<?php
/**
 * Adds the Jumper UI to the Admin Panel.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use WP_Ultimo\Logger;
use WP_Ultimo\UI\Base_Element;
use WP_Ultimo\License;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds the Jumper UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Jumper {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * GET slug to force the jumper menu reset/fetching.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $reset_slug = 'wu-rebuild-jumper';

	/**
	 * Key to save the menu list on the transient database.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $transient_key = 'wu-jumper-menu-list';

	/**
	 * Element construct.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		add_action('wp_ultimo_load', array($this, 'add_settings'), 20);

		add_action('init', array($this, 'load_jumper'));

	} // end __construct;

	/**
	 * Checks if we should add the jumper or not.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	protected function is_jumper_enabled() {

		return apply_filters('wu_is_jumper_enabled', License::get_instance()->allowed() && current_user_can('manage_network'));

	} // end is_jumper_enabled;

	/**
	 * Adds the Jumper trigger to the admin top pages.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Admin_Pages\Base_Admin_Page $page The current page.
	 * @return void
	 */
	public function add_jumper_trigger($page) {

		wu_get_template('ui/jumper-trigger', array(
			'page'   => $page,
			'jumper' => $this,
		));

	} // end add_jumper_trigger;

	/**
	 * Loads the necessary elements to display the Jumper.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_jumper() {

		if ($this->is_jumper_enabled() && is_admin()) {

			add_action('wu_header_right', array($this, 'add_jumper_trigger'));

			add_action('admin_init', array($this, 'rebuild_menu'));

			add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

			add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

			add_action('admin_footer', array($this, 'output'));

			add_filter('update_footer', array($this, 'add_jumper_footer_message'), 200);

			add_action('wu_after_save_settings', array($this, 'clear_jump_cache_on_save'));

			add_filter('wu_link_list', array($this, 'add_wp_ultimo_extra_links'));

			add_filter('wu_link_list', array($this, 'add_user_custom_links'));

		} // end if;

	} // end load_jumper;

	/**
	 * Clear the jumper menu cache on settings save
	 *
	 * We need to do this to make sure that we clear the menu when the admin
	 * adds a new custom menu item.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings Settings being saved.
	 * @return void
	 */
	public function clear_jump_cache_on_save($settings) {

		if (isset($settings['jumper_custom_links'])) {

			delete_site_transient($this->transient_key);

		} // end if;

	} // end clear_jump_cache_on_save;

	/**
	 * Rebuilds the jumper menu via a trigger URL.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function rebuild_menu() {

		if (isset($_GET[$this->reset_slug]) && current_user_can('manage_network')) {

			delete_site_transient($this->transient_key);

			wp_redirect(network_admin_url());

			exit;

		} // end if;

	} // end rebuild_menu;

	/**
	 * Retrieves the custom links added by the super admin
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_user_custom_links() {

		$treated_lines = array();

		$saved_links = wu_get_setting('jumper_custom_links');

		$lines = explode(PHP_EOL, $saved_links);

		foreach ($lines as $line) {

			$link_elements = explode(':', $line, 2);

			if (count($link_elements) === 2) {

				$title = trim($link_elements[1]);

				$treated_lines[$title] = trim($link_elements[0]);

			} // end if;

		} // end foreach;

		return $treated_lines;

	} // end get_user_custom_links;

	/**
	 * Add the custom links to the Jumper menu
	 *
	 * @since 2.0.0
	 *
	 * @param array $links Jumper links already saved.
	 * @return array
	 */
	public function add_user_custom_links($links) {

		$custom_links = $this->get_user_custom_links();

		if (!empty($custom_links)) {

			$links[__('Custom Links', 'wp-ultimo')] = $custom_links;

		} // end if;

		return $links;

	} // end add_user_custom_links;

	/**
	 * Add WP Ultimo settings links to the Jumper menu.
	 *
	 * @since 2.0.0
	 *
	 * @param array $links WP Ultimo settings array.
	 * @return array
	 */
	public function add_wp_ultimo_extra_links($links) {

		if (isset($links['WP Ultimo'])) {

			$settings_tabs = array(
				'general'        => __('General', 'wp-ultimo'),
				'network'        => __('Network Settings', 'wp-ultimo'),
				'gateways'       => __('Payment Gateways', 'wp-ultimo'),
				'domain_mapping' => __('Domain Mapping & SSL', 'wp-ultimo'),
				'emails'         => __('Emails', 'wp-ultimo'),
				'styling'        => __('Styling', 'wp-ultimo'),
				'tools'          => __('Tools', 'wp-ultimo'),
				'advanced'       => __('Advanced', 'wp-ultimo'),
				'activation'     => __('Activation & Support', 'wp-ultimo'),
			);

			foreach ($settings_tabs as $tab => $tab_label) {

				$url = network_admin_url('admin.php?page=wp-ultimo&wu-tab=' . $tab);

				// translators: The placeholder represents the title of the Settings tab.
				$links['WP Ultimo'][$url] = sprintf(__('Settings: %s', 'wp-ultimo'), $tab_label);

			} // end foreach;

			$links['WP Ultimo'][ network_admin_url('admin.php?page=wp-ultimo&wu-tab=tools') ] = __('Settings: Webhooks', 'wp-ultimo');

			$links['WP Ultimo'][ network_admin_url('admin.php?page=wp-ultimo-system-info&wu-tab=logs') ] = __('System Info: Logs', 'wp-ultimo');

			/**
			 * Adds Main Site Dashboard
			 */
			if (isset($links[__('Sites')])) {

				$main_site_url = get_admin_url(get_current_site()->blog_id);

				$links[__('Sites')][$main_site_url] = __('Main Site Dashboard', 'wp-ultimo');

			} // end if;

		} // end if;

		return $links;

	} // end add_wp_ultimo_extra_links;

	/**
	 * Get the trigger key defined by the user.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	function get_defined_trigger_key() {

		return substr(wu_get_setting('jumper_key', 'g'), 0, 1);

	} // end get_defined_trigger_key;

	/**
	 * Get the trigger key combination depending on the OS
	 *
	 * - For Win & Linux: ctrl + alt + key defined by user;
	 * - For Mac: command + option + key defined by user.
	 *
	 * @since 2.0.0
	 *
	 * @param string $os OS to get the key combination for. Options: win or osx.
	 * @return array
	 */
	function get_keys($os = 'win') {

		$trigger_key = $this->get_defined_trigger_key();

		$keys = array(
			'win' => array('ctrl', 'alt', $trigger_key),
			'osx' => array('command', 'option', $trigger_key),
		);

		return isset($keys[$os]) ? $keys[$os] : $keys['win'];

	} // end get_keys;

	/**
	 * Changes the helper footer message about the Jumper and its trigger
	 *
	 * @since 2.0.0
	 *
	 * @param string $text The default WordPress right footer message.
	 * @return string
	 */
	public function add_jumper_footer_message($text) {

		if (!wu_get_setting('jumper_display_tip', true)) {

			return $text;

		} // end if;

		$os = stristr($_SERVER['HTTP_USER_AGENT'], 'mac') ? 'osx' : 'win';

		$keys = $this->get_keys($os);

		$html = '';

		foreach ($keys as $key) {

			$html .= '<span class="wu-keys-key">' . $key . '</span>+';

		} // end foreach;

		$html = trim($html, '+');

		// translators: the %s placeholder is the key combination to trigger the Jumper.
		return '<span class="wu-keys">' . sprintf(__('<strong>Quick Tip:</strong> Use %s to jump between pages.', 'wp-ultimo'), $html) . '</span>' . $text;

	} // end add_jumper_footer_message;

	/**
	 * Enqueues the JavaScript files necessary to make the jumper work.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_scripts() {

		wp_register_script('wu-mousetrap', wu_get_asset('mousetrap.js', 'js/lib'), array('jquery'), wu_get_version(), true);

		wp_register_script('wu-jumper', wu_get_asset('jumper.js', 'js'), array('jquery', 'wu-selectize', 'wu-mousetrap', 'underscore'), wu_get_version(), true);

		wp_localize_script('wu-jumper', 'wu_jumper_vars', array(
			'not_found_message' => __('Nothing found for', 'wp-ultimo'),
			'trigger_key'       => $this->get_defined_trigger_key(),
			'network_base_url'  => network_admin_url(),
			'base_url'          => get_admin_url(get_current_site()->blog_id),
		));

		wp_enqueue_script('wu-jumper');

		wp_enqueue_style('wu-admin');

	} // end enqueue_scripts;

	/**
	 * Enqueues the CSS files necessary to make the jumper work.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_styles() {

		wp_enqueue_style('wu-jumper', wu_get_asset('jumper.css', 'css'), array(), wu_get_version());

	} // end enqueue_styles;

	/**
	 * Outputs the actual HTML markup of the Jumper.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output() {

		WP_Ultimo()->helper->render('ui/jumper', array(
			'menu_groups' => $this->get_link_list(),
		));

	} // end output;

	/**
	 * Get the full page URL for admin pages.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url URL of the menu item.
	 * @return string
	 */
	public function get_menu_page_url($url) {

		$final_url = menu_page_url($url, false);

		return str_replace(admin_url(), network_admin_url(), $final_url);

	} // end get_menu_page_url;

	/**
	 * Returns the URL of a jumper menu item
	 *
	 * If the URL is an absolute URL, returns the full-url.
	 * If the URL is relative, we return the full URL using WordPress url functions.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url URL of the menu item.
	 * @return string
	 */
	public function get_target_url($url) {

		if (strpos($url, 'http') !== false) {

			return $url;

		} // end if;

		if (strpos($url, '.php') !== false) {

			return network_admin_url($url);

		} // end if;

		return $this->get_menu_page_url($url);

	} // end get_target_url;

	/**
	 * Builds the list of links based on the $menu and $submenu globals.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function build_link_list() {

		return Logger::track_time('jumper', __('Regenerating Jumper menu items', 'wp-ultimo'), function() {

			global $menu, $submenu;

			// This variable is going to carry our options
			$choices = array();

			// Prevent first run bug
			if (!is_array($menu) || !is_array($submenu)) {

				return array();

			} // end if;

			// Loop all submenus so que can get our final
			foreach ($submenu as $menu_name => $submenu_items) {

				$title = $this->search_recursive($menu_name, $menu);

				$string = wu_get_isset($title, 0, '');

				$title = preg_replace('/[0-9]+/', '', strip_tags($string));

				// If parent does not exists, skip
				if (!empty($title) && is_array($submenu_items)) {

					// We have to loop now each submenu
					foreach ($submenu_items as $submenu_item) {

						$url = $this->get_target_url($submenu_item[2]);

						// Add to our choices the admin urls
						$choices[$title][$url] = preg_replace('/[0-9]+/', '', strip_tags($submenu_item[0]));

					} // end foreach;

				} // end if;

			} // end foreach;

			$choices = apply_filters('wu_link_list', $choices);

			set_site_transient($this->transient_key, $choices, 10 * MINUTE_IN_SECONDS);

			return $choices;

		});

	} // end build_link_list;

	/**
	 * Gets the cached menu list saved.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_saved_menu() {

		$saved_menu = get_site_transient($this->transient_key);

		return $saved_menu ? $saved_menu : array();

	} // end get_saved_menu;

	/**
	 * Returns the link list.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_link_list() {

		$should_rebuild_menu = !get_site_transient($this->transient_key);

		return $should_rebuild_menu && is_network_admin() ? $this->build_link_list() : $this->get_saved_menu();

	} // end get_link_list;

	/**
	 * Filter the WP Ultimo settings to add Jumper options
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function add_settings() {

		wu_register_settings_section('tools', array(
			'title' => __('Tools', 'wp-ultimo'),
			'desc'  => __('Tools', 'wp-ultimo'),
			'icon'  => 'dashicons-wu-tools',
		));

		wu_register_settings_field('tools', 'tools_header', array(
			'title' => __('Jumper', 'wp-ultimo'),
			'desc'  => __('Spotlight-like search bar that allows you to easily access everything on your network.', 'wp-ultimo'),
			'type'  => 'header',
		));

		wu_register_settings_field('tools', 'jumper_key', array(
			'title'   => __('Trigger Key', 'wp-ultimo'),
			'desc'    => __('Change the keyboard key used in conjunction with ctrl + alt (or cmd + option), to trigger the Jumper box.', 'wp-ultimo'),
			'type'    => 'text',
			'default' => 'g',
		));

		wu_register_settings_field('tools', 'jumper_custom_links', array(
			'title'       => __('Custom Links', 'wp-ultimo'),
			'desc'        => __('Use this textarea to add custom links to the Jumper. Add one per line, with the format "Title : url".', 'wp-ultimo'),
			'placeholder' => __('Tile of Custom Link : http://link.com', 'wp-ultimo'),
			'type'        => 'textarea',
			'html_attr'   => array(
				'rows' => 4,
			),
		));

	} // end add_settings;

	/**
	 * Helper function to recursively seach an array.
	 *
	 * @since 2.0.0
	 *
	 * @param string $needle String to seach recursively.
	 * @param array  $haystack Array to search.
	 * @return mixed
	 */
	public function search_recursive($needle, $haystack) {

		foreach ($haystack as $key => $value) {

			$current_key = $key;

			if ($needle === $value || (is_array($value) && $this->search_recursive($needle, $value) !== false)) {

				return $value;

			} // end if;

		} // end foreach;

		return false;

	} // end search_recursive;

} // end class Jumper;
