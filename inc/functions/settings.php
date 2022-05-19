<?php
/**
 * Settings Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Loads dependencies: the option apis.
 */
require_once wu_path('inc/functions/options.php');

/**
 * Returns an array with all the WP Ultimo settings.
 *
 * @since 2.0.0
 * @return array
 */
function wu_get_all_settings() {

	return WP_Ultimo()->settings->get_all();

} // end wu_get_all_settings;

/**
 * Get a specific settings from the plugin.
 *
 * @since 2.0.0
 *
 * @param  string $setting Settings name to return.
 * @param  string $default Default value for the setting if it doesn't exist.
 * @return array The value of that setting
 */
function wu_get_setting($setting, $default = false) {

	return WP_Ultimo()->settings->get_setting($setting, $default);

} // end wu_get_setting;

/**
 * Saves a specific setting into the database.
 *
 * @since 2.0.0
 *
 * @param string $setting Option key to save.
 * @param mixed  $value   New value of the option.
 * @return boolean
 */
function wu_save_setting($setting, $value) {

	return WP_Ultimo()->settings->save_setting($setting, $value);

} // end wu_save_setting;

/**
 * Adds a new settings section.
 *
 * Sections are a way to organize correlated settings into one cohesive unit.
 * Developers should be able to add their own sections, if they need to.
 * This is the purpose of this APIs.
 *
 * @since 2.0.0
 *
 * @param string $section_slug ID of the Section. This is used to register fields to this section later.
 * @param array  $atts Section attributes such as title, description and so on.
 * @return void
 */
function wu_register_settings_section($section_slug, $atts) {

	WP_Ultimo()->settings->add_section($section_slug, $atts);

} // end wu_register_settings_section;

/**
 * Adds a new field to a settings section.
 *
 * Fields are settings that admins can actually change.
 * This API allows developers to add new fields to a given settings section.
 *
 * @since 2.0.0
 *
 * @param string $section_slug Section to which this field will be added to.
 * @param string $field_slug ID of the field. This is used to later retrieve the value saved on this setting.
 * @param array  $atts Field attributes such as title, description, tooltip, default value, etc.
 * @return void
 */
function wu_register_settings_field($section_slug, $field_slug, $atts) {

	WP_Ultimo()->settings->add_field($section_slug, $field_slug, $atts);

} // end wu_register_settings_field;

/**
 * Adds a help side-panel to the settings page.
 *
 * @since 2.0.0
 *
 * @param string $section_slug Section to which this field will be added to.
 * @param array  $atts Side-panel attributes.
 * @return void
 */
function wu_register_settings_side_panel($section_slug, $atts) {

	if (wu_request('tab', 'general') !== $section_slug && $section_slug !== 'all') {

		return;

	} // end if;

	$atts = wp_parse_args($atts, array(
		'title'  => __('Side Panel', 'wp-ultimo'),
		'render' => '__return_false',
		'show'   => '__return_true',
	));

	$callback = wu_get_isset($atts, 'show', '__return_true');

	$should_display = is_callable($callback) && call_user_func($callback);

	if (!$should_display) {

		return;

	} // end if;

	$id = sanitize_title($atts['title']);

	add_meta_box("wp-ultimo-{$id}", $atts['title'], function() use ($atts) {

		call_user_func($atts['render']);

	}, 'wu_settings_admin_page', 'side', 'low');

} // end wu_register_settings_side_panel;

/**
 * Retrieve the network custom logo.
 *
 * @param string $size The size of the logo. It could be Thumbnail, Medium, Large or Full.
 * @return string With the logo's url.
 */
function wu_get_network_logo($size = 'full') {

	switch_to_blog(wu_get_main_site_id());

		$settings_logo = wp_get_attachment_image_src(wu_get_setting('company_logo'), $size); // phpcs:ignore

	restore_current_blog();

	if ($settings_logo) {

		return $settings_logo[0];

	} // end if;

	$logo = wu_get_asset('logo.png', 'img');

	$custom_logo = wp_get_attachment_image_src(get_theme_mod('custom_logo'), $size);

	if (!empty($custom_logo)) {

		$logo = $custom_logo[0];

	} // end if;

	return apply_filters('wu_get_logo', $logo);

} // end wu_get_network_logo;

/**
 * Retrieve the network custom icon.
 *
 * @param string $size The size of the icon in pixels.
 * @return string With the logo's url.
 */
function wu_get_network_favicon($size = '48') {

	$custom_icon = get_site_icon_url($size, wu_get_asset('badge.png', 'img'), wu_get_main_site_id());

	return $custom_icon;

} // end wu_get_network_favicon;
