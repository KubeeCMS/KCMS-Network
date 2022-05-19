<?php
/**
 * Basic Whitelabel
 *
 * @package WP_Ultimo
 * @subpackage Whitelabel
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles the basic white-labeling of the WordPress admin interface.
 *
 * @since 2.0.0
 */
class Whitelabel {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Checks if the cache was initiated.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $init = false;

	/**
	 * Cached allowed domains.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_domains;

	/**
	 * Array of terms to search for.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $search = array();

	/**
	 * Array of terms to replace with. Must be a 1 to 1 relationship with the search array.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $replace = array();

	/**
	 * Adds the hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wp_ultimo_load', array($this, 'add_settings'), 20);

		add_action('admin_init', array($this, 'clear_footer_texts'));

		add_action('init', array($this, 'hooks'));

		add_filter('gettext', array($this, 'replace_text'), 10, 3);

	} // end init;

	/**
	 * Add the necessary hooks when the feature is enabled.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks() {

		if (wu_get_setting('hide_wordpress_logo', true)) {

			add_action('wp_before_admin_bar_render', array($this, 'wp_logo_admin_bar_remove'), 0);

			add_action('wp_user_dashboard_setup', array($this, 'remove_dashboard_widgets'), 11);

			add_action('wp_dashboard_setup', array($this, 'remove_dashboard_widgets'), 11);

			add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

			add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

		} // end if;

		if (wu_get_setting('hide_sites_menu', true)) {

			add_action('network_admin_menu', array($this, 'remove_sites_admin_menu'));

		} // end if;

	} // end hooks;

	/**
	 * Loads the custom css file.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_styles() {

		WP_Ultimo()->scripts->register_style('wu-whitelabel', wu_get_asset('whitelabel.css', 'css'));

		wp_enqueue_style('wu-whitelabel');

	} // end enqueue_styles;

	/**
	 * Replaces the terms on the translated strings.
	 *
	 * @since 2.0.0
	 *
	 * @param string $translation The translation.
	 * @param string $text The original text before translation.
	 * @param string $domain The gettext domain.
	 * @return string
	 */
	public function replace_text($translation, $text, $domain) {

		if ($this->allowed_domains === null) {

			$this->allowed_domains = apply_filters('wu_replace_text_allowed_domains', array(
				'default',
				'wp-ultimo',
			));

		} // end if;

		if (!in_array($domain, $this->allowed_domains, true)) {

			return $translation;

		} // end if;

		if ($this->init === false) {

			$search_and_replace = array();

			$site_plural = wu_get_setting('rename_site_plural');

			if ($site_plural) {

				$search_and_replace['sites'] = strtolower($site_plural);
				$search_and_replace['Sites'] = ucfirst($site_plural);

			} // end if;

			$site_singular = wu_get_setting('rename_site_singular');

			if ($site_singular) {

				$search_and_replace['site'] = strtolower($site_singular);
				$search_and_replace['Site'] = ucfirst($site_singular);

			} // end if;

			$wordpress = wu_get_setting('rename_wordpress');

			if ($wordpress) {

				$search_and_replace['wordpress'] = strtolower($wordpress);
				$search_and_replace['WordPress'] = ucfirst($wordpress);
				$search_and_replace['Wordpress'] = ucfirst($wordpress);
				$search_and_replace['wordPress'] = ucfirst($wordpress);

			} // end if;

			if ($search_and_replace) {

				$this->search  = array_keys($search_and_replace);
				$this->replace = array_values($search_and_replace);

			} // end if;

			$this->init = true;

		} // end if;

		if (!empty($this->search)) {

			return str_replace($this->search, $this->replace, $translation);

		} // end if;

		return $translation;

	} // end replace_text;

	/**
	 * Adds the whitelabel options.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function add_settings() {

		wu_register_settings_section('whitelabel', array(
			'title' => __('Whitelabel', 'wp-ultimo'),
			'desc'  => __('Basic Whitelabel', 'wp-ultimo'),
			'icon'  => 'dashicons-wu-eye',
		));

		wu_register_settings_field('whitelabel', 'whitelabel_header', array(
			'title' => __('Whitelabel', 'wp-ultimo'),
			'desc'  => __('Hide a couple specific WordPress elements and rename others.', 'wp-ultimo'),
			'type'  => 'header',
		));

		$preview_image = wu_preview_image(wu_get_asset('settings/settings-hide-wp-logo-preview.png'));

		wu_register_settings_field('whitelabel', 'hide_wordpress_logo', array(
			'title'   => __('Hide WordPress Logo', 'wp-ultimo') . $preview_image,
			'desc'    => __('Hide the WordPress logo from the top-bar and replace the same logo on the My Sites top-bar item with a more generic icon.', 'wp-ultimo'),
			'type'    => 'toggle',
			'default' => 1,
		));

		wu_register_settings_field('whitelabel', 'hide_sites_menu', array(
			'title'   => __('Hide Sites Admin Menu', 'wp-ultimo'),
			'desc'    => __('We recommend that you manage all of your sites using the WP Ultimo &rarr; Sites page. To avoid confusion, you can hide the default "Sites" item from the WordPress admin menu by toggling this option.', 'wp-ultimo'),
			'type'    => 'toggle',
			'default' => 0,
		));

		wu_register_settings_field('whitelabel', 'rename_wordpress', array(
			'title'       => __('Replace the word "WordPress"', 'wp-ultimo'),
			'placeholder' => __('e.g. My App', 'wp-ultimo'),
			'desc'        => __('Replace all occurrences of the word "WordPress" with a different word.', 'wp-ultimo'),
			'type'        => 'text',
			'default'     => '',
		));

		wu_register_settings_field('whitelabel', 'rename_site_singular', array(
			'title'           => __('Replace the word "Site" (singular)', 'wp-ultimo'),
			'placeholder'     => __('e.g. App', 'wp-ultimo'),
			'desc'            => __('Replace all occurrences of the word "Site" with a different word.', 'wp-ultimo'),
			'type'            => 'text',
			'default'         => '',
			'wrapper_classes' => 'wu-w-1/2',
		));

		wu_register_settings_field('whitelabel', 'rename_site_plural', array(
			'title'           => __('Replace the word "Sites" (plural)', 'wp-ultimo'),
			'placeholder'     => __('e.g. Apps', 'wp-ultimo'),
			'desc'            => __('Replace all occurrences of the word "Sites" with a different word.', 'wp-ultimo'),
			'type'            => 'text',
			'default'         => '',
			'wrapper_classes' => 'wu-w-1/2',
		));

	} // end add_settings;

	/**
	 * Removes the WordPress original logo from the top-bar.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function wp_logo_admin_bar_remove() {

		global $wp_admin_bar;

		$wp_admin_bar->remove_menu('wp-logo');

	}  // end wp_logo_admin_bar_remove;

	/**
	 * Remove the default widgets from the user panel.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function remove_dashboard_widgets() {

		global $wp_meta_boxes;

		unset($wp_meta_boxes['dashboard-user']['side']['core']['dashboard_quick_press']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_incoming_links']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_right_now']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_plugins']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_recent_drafts']);
		unset($wp_meta_boxes['dashboard-user']['normal']['core']['dashboard_recent_comments']);
		unset($wp_meta_boxes['dashboard-user']['side']['core']['dashboard_primary']);
		unset($wp_meta_boxes['dashboard-user']['side']['core']['dashboard_secondary']);

	} // end remove_dashboard_widgets;

	/**
	 * Removes the WordPress credits from the admin footer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function clear_footer_texts() {

		if (current_user_can('manage_network')) {

			return;

		} // end if;

		add_filter('admin_footer_text', '__return_empty_string', 11);

		add_filter('update_footer', '__return_empty_string', 11);

	} // end clear_footer_texts;

	/**
	 * Remove the sites admin menu, if the option is selected.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function remove_sites_admin_menu() {

		global $menu;

		$index = '';

		foreach ($menu as $i => $menu_item) {

			if ($menu_item[2] === 'sites.php') {

				$index = $i;

				continue;

			} // end if;

		} // end foreach;

		unset($menu[$index]);

	} // end remove_sites_admin_menu;

} // end class Whitelabel;
