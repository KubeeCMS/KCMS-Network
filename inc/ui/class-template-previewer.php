<?php
/**
 * Adds the Template Previewer code.
 *
 * @package WP_Ultimo
 * @subpackage UI
 * @since 2.0.0
 */

namespace WP_Ultimo\UI;

use WP_Ultimo\Database\Sites\Site_Type;

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
	 * Keeps a list of the available templates for the products selected.
	 *
	 * @since 2.0.11
	 * @var null|array
	 */
	protected $available_templates = null;

	/**
	 * Keeps the settings key for the top-bar.
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

			add_filter('home_url', array($this, 'append_preview_parameter'), 9999, 4);

			add_action('send_headers', array($this, 'send_cross_origin_headers'), 1000);

			return;

		} // end if;

		if ($this->is_template_previewer()) {

			add_action('init', array($this, 'template_previewer'));

			add_action('wp_enqueue_scripts', array($this, 'register_scripts'));

			add_action('wp_print_styles', array($this, 'remove_unnecessary_styles'), 0);

			/**
			 * Runs when inside the template previewer context.
			 *
			 * @since 2.0.4
			 * @param self $template_previewer Instance of the current class.
			 */
			do_action('wu_template_previewer', $this);

		} // end if;

	} // end hooks;

	/**
	 * Send the cross origin headers to allow iframes to be loaded.
	 *
	 * @since 2.0.9
	 * @return void
	 */
	public function send_cross_origin_headers() {

		global $current_site;

		$_SERVER['HTTP_ORIGIN'] = set_url_scheme("http://{$current_site->domain}");

		send_origin_headers();

	} // end send_cross_origin_headers;

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

		wp_register_script('wu-template-previewer', wu_get_asset('template-previewer.js', 'js'), array('jquery', 'wu-cookie-helpers'), wu_get_version());

		wp_localize_script('wu-template-previewer', 'wu_template_previewer', array(
			'domain'           => str_replace('www.', '', $current_site->domain),
			'current_template' => wu_request($this->get_preview_parameter(), false),
			'current_url'      => wu_get_current_url(),
			'query_parameter'  => $this->get_preview_parameter(),
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
	 * Remove the unnecessary styles added by themes and other plugins.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function remove_unnecessary_styles() {

		global $wp_styles;

		$wp_styles->queue = array(
			'wu-admin',
			'wu-template-previewer',
			'dashicons',
		);

	} // end remove_unnecessary_styles;

	/**
	 * Append preview parameter.
	 *
	 * @since 2.0.0
	 *
	 * @param string      $url The URL.
	 * @param string      $path        Path relative to the home URL. Blank string if no path is specified.
	 * @param string|null $orig_scheme Scheme to give the home URL context. Accepts 'http', 'https',
	 *                                 'relative', 'rest', or null.
	 * @param int|null    $blog_id     Site ID, or null for the current site.
	 * @return string
	 */
	public function append_preview_parameter($url, $path, $orig_scheme, $blog_id) {

		$allowed_schemes = array(
			null,
			'http',
			'https',
		);

		if (in_array($orig_scheme, $allowed_schemes, true) === false) {

			return $url;

		} // end if;

		if (apply_filters('wu_append_preview_parameter', true, $this) === false) {

			return $url;

		} // end if;

		return add_query_arg('wu-preview', 1, $url);

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

		$args = array(
			$this->get_preview_parameter() => $site_id,
		);

		if (wu_request('open')) {

			$args['open'] = 1;

		} // end if;

		return add_query_arg($args, home_url());

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
		if ($selected_template->get_type() !== Site_Type::SITE_TEMPLATE && !wu_request('customizer')) {

			wp_die(__('This template is not available', 'wp-ultimo'));

		} // end if;

		$categories = array();

		$settings = $this->get_settings();

		$render_parameters = array(
			'current_site'      => $current_site,
			'categories'        => $categories,
			'selected_template' => $selected_template,
			'tp'                => $this,
		);

		$products_ids = isset($_COOKIE['wu_selected_products']) ? explode( ',', $_COOKIE['wu_selected_products']) : array();

		$products = array_map('wu_get_product', $products_ids);

		// clear array
		$products = array_filter($products);

		if (!empty($products)) {

			$limits = new \WP_Ultimo\Objects\Limitations();

			list($plan, $additional_products) = wu_segregate_products($products);

			$products = array_merge(array($plan), $additional_products);

			foreach ($products as $product) {

				$limits = $limits->merge($product->get_limitations());

			} // end foreach;

			if ($limits->site_templates->get_mode() !== 'default') {

				$site_ids = $limits->site_templates->get_available_site_templates();

				$render_parameters['templates'] = array_map('wu_get_site', $site_ids);

				/**
				 * Check if the current site is a member of
				 * the list of available templates
				 */
				if (!in_array($selected_template->get_id(), $site_ids, false)) {

					$redirect_to = wu_get_current_url();

					$redirect_to = add_query_arg($this->get_preview_parameter(), current($site_ids), $redirect_to);

					wp_redirect($redirect_to);

					exit;

				} // end if;

			} // end if;

		} // end if;

		if (!isset($render_parameters['templates'])) {

			$render_parameters['templates'] = wu_get_site_templates();

		} // end if;

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

		return !empty(wu_request('wu-preview'));

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

		$saved_settings = wu_get_option(Template_Previewer::KEY, array());

		$default_settings = array_merge($default_settings, $saved_settings);

		$server_request = $_REQUEST;

		// Ensure that templates key does not change with request
		if (isset($server_request['templates'])) {

			unset($server_request['templates']);

		} // end if;

		$parsed_args = wp_parse_args($server_request, $default_settings);

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

		return wu_save_option(Template_Previewer::KEY, $settings);

	} // end save_settings;

} // end class Template_Previewer;
