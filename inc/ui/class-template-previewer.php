<?php
/**
 * Adds the Template Previewer code.
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
 * Adds the Template_Previewer UI to the Admin Panel.
 *
 * @since 2.0.0
 */
class Template_Previewer {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the settings key fopr the top-bar.
	 */
	const KEY = 'top_bar_settings';

	/**
	 * Initializes the class.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wp_ultimo_load', array($this, 'hooks'));

	} // end init;

	/**
	 * Hooks into WordPress to add the template preview.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks() {

		if ($this->is_preview()) {
			/*
			 * Remove admin bar from logged users.
			 */
			add_filter('show_admin_bar', '__return_false');

			add_filter('wu_is_jumper_enabled', '__return_false');

			add_filter('wu_is_toolbox_enabled', '__return_false');

			add_filter('home_url', array($this, 'append_preview_parameter'));

			return;

		} // end if;

		if ($this->is_template_previewer()) {

			add_action('init', array($this, 'template_previewer'));

			add_action('wp_enqueue_scripts', array($this, 'register_scripts'));

			add_action('wp_print_styles', array($this, 'remove_unecessary_scripts'), 0);

		} // end if;

	} // end hooks;

	/**
	 * Register the necessary scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		global $current_site;

		$settings = $this->get_settings();

		$bg_color         = wu_color($settings['bg_color']);
		$button_bg_color  = wu_color($settings['button_bg_color']);
		$button_bg_darker = wu_color($button_bg_color->darken(4));

		wp_register_script('wu-template-previewer', wu_get_asset('template-previewer.js', 'js'), array('jquery'), wu_get_version());

		wp_localize_script('wu-template-previewer', 'wu_template_previewer', array(
			'domain' => str_replace('www.', '', $current_site->domain),
		));

		wp_enqueue_script('wu-template-previewer');

		wp_enqueue_style('wu-template-previewer', wu_get_asset('template-previewer.css', 'css'), false, wu_get_version());

		wp_add_inline_style('wu-template-previewer', wu_get_template_contents('dynamic-styles/template-previewer', array(
			'bg_color'        => $bg_color,
			'button_bg_color' => $button_bg_color,
		)));

		wp_enqueue_style('dashicons');

	} // end register_scripts;

	/**
	 * Remove the unecessary scripts added by themes and other plugins.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function remove_unecessary_scripts() {

		global $wp_styles;

		$wp_styles->queue = array(
			'wu-admin',
			'wu-template-previewer',
			'dashicons',
		);

	} // end remove_unecessary_scripts;

	/**
	 * Append preview parameter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $url The URL.
	 * @return string
	 */
	public function append_preview_parameter($url) {

		return add_query_arg('preview', 'true', $url);

	} // end append_preview_parameter;

	/**
	 * Returns the preview URL for the template previewer.
	 *
	 * @since 2.0.0
	 *
	 * @param int $site_id The ID of the template site.
	 * @return string
	 */
	public function get_preview_url($site_id) {

		return add_query_arg($this->get_preview_parameter(), $site_id);

	} // end get_preview_url;

	/**
	 * Template Previewer code
	 *
	 * @since 1.5.5
	 * @return void
	 */
	public function template_previewer() {

		global $current_site;

		$template_value = wu_request($this->get_preview_parameter(), false);

		$selected_template = wu_get_site($template_value);

		/**
		 * Check if this is a site template
		 */
		if (!$selected_template->get_type() === 'site-template') {

			/**
			 * We need to check to see if this is a user checking out his own site
			 *
			 * @since 1.7.4
			 */
			$subscription = wu_get_current_subscription();

			if (!$subscription || !in_array($template_value, $subscription->get_sites_ids(), true)) {

				wp_die(__('This template is not available', 'wp-ultimo'));

			} // end if;

		} // end if;

		$categories = array();

		$settings = $this->get_settings();

		$render_parameters = array(
			'current_site'      => $current_site,
			'templates'         => wu_get_site_templates(),
			'categories'        => $categories,
			'selected_template' => $selected_template,
			'tp'                => $this,
		);

		$render_parameters = array_merge($render_parameters, $settings);

		wu_get_template('ui/template-previewer', $render_parameters);

		exit;

	} // end template_previewer;

	/**
	 * Returns the preview parameter, so admins can change it.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_preview_parameter() {

		$slug = $this->get_setting('preview_url_parameter', 'template-preview');

		return apply_filters('wu_get_template_preview_slug', $slug);

	} // end get_preview_parameter;

	/**
	 * Checks if this is a template previewer window.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_template_previewer() {

		$slug = $this->get_preview_parameter();

		return wu_request($slug);

	} // end is_template_previewer;

	/**
	 * Check if the frame is a preview.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_preview() {

		return !empty(wu_request('preview'));

	} // end is_preview;

	/**
	 * Returns the settings.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_settings() {

		// Fix to issue on wp_get_attachment_url() inside core.
		// @todo report it.
		$GLOBALS['pagenow'] = '';

		$default_settings = array(
			'bg_color'                    => '#f9f9f9',
			'button_bg_color'             => '#00a1ff',
			'logo_url'                    => wu_get_network_logo(),
			'button_text'                 => __('Use this Template', 'wp-ultimo'),
			'preview_url_parameter'       => 'template-preview',
			'display_responsive_controls' => true,
			'use_custom_logo'             => false,
			'custom_logo'                 => false,
			'enabled'                     => true,
		);

		$saved_settings = WP_Ultimo()->helper->get_option(Template_Previewer::KEY, array());

		$default_settings = array_merge($default_settings, $saved_settings);

		$parsed_args = wp_parse_args($_REQUEST, $default_settings);

		$parsed_args['display_responsive_controls'] = wu_string_to_bool($parsed_args['display_responsive_controls']);
		$parsed_args['use_custom_logo']             = wu_string_to_bool($parsed_args['use_custom_logo']);

		return $parsed_args;

	} // end get_settings;

	/**
	 * Gets a particular setting.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $setting The setting key.
	 * @param boolean $default Default value, if it is not found.
	 * @return mixed
	 */
	public function get_setting($setting, $default = false) {

		return wu_get_isset($this->get_settings(), $setting, $default);

	} // end get_setting;

	/**
	 * Save settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings_to_save List of settings to save.
	 * @return boolean
	 */
	public function save_settings($settings_to_save) {

		$settings = $this->get_settings();

		foreach ($settings as $setting => $value) {

			if ($setting === 'logo_url') {

				$settings['logo_url'] = wu_get_network_logo();

				continue;

			} // end if;

			$settings[$setting] = wu_get_isset($settings_to_save, $setting, false);

		} // end foreach;

		return WP_Ultimo()->helper->save_option(Template_Previewer::KEY, $settings);

	} // end save_settings;

} // end class Template_Previewer;
