<?php
/**
 * WP Ultimo helper class to handle global registering of scripts and styles.
 *
 * @package WP_Ultimo
 * @subpackage Scripts
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo helper class to handle global registering of scripts and styles.
 *
 * @since 2.0.0
 */
class Scripts {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Runs when the instantiation first occurs.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('init', array($this, 'register_default_scripts'));

		add_action('init', array($this, 'register_default_styles'));

		add_action('admin_init', array($this, 'enqueue_default_admin_styles'));

		add_action('admin_init', array($this, 'enqueue_default_admin_scripts'));

		add_action('wp_ajax_wu_toggle_container', array($this, 'update_use_container'));

		add_filter('admin_body_class', array($this, 'add_body_class_container_boxed'));

	} // end init;

	/**
	 * Wrapper for the register scripts function.
	 *
	 * @since 2.0.0
	 *
	 * @param string $handle The script handle. Used to enqueue the script.
	 * @param string $src URL to the file.
	 * @param array  $deps List of dependency scripts.
	 * @return void
	 */
	public function register_script($handle, $src, $deps = array()) {

		wp_register_script($handle, $src, $deps, wu_get_version());

	} // end register_script;

	/**
	 * Wrapper for the register styles function.
	 *
	 * @since 2.0.0
	 *
	 * @param string $handle The script handle. Used to enqueue the script.
	 * @param string $src URL to the file.
	 * @param array  $deps List of dependency scripts.
	 * @return void
	 */
	public function register_style($handle, $src, $deps = array()) {

		wp_register_style($handle, $src, $deps, wu_get_version());

	} // end register_style;

	/**
	 * Registers the default WP Ultimo scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_default_scripts() {
		/*
		 * Adds Vue JS
		 */
		$this->register_script('wu-vue', wu_get_asset('lib/vue.js', 'js'));

		/*
		 * Adds Sweet Alert
		 */
		$this->register_script('wu-sweet-alert', wu_get_asset('lib/sweetalert2.all.js', 'js'));

		/*
		 * Adds Flat Picker
		 */
		$this->register_script('wu-flatpicker', wu_get_asset('lib/flatpicker.js', 'js'));

		/*
		 * Adds tipTip
		 */
		$this->register_script('wu-tiptip', wu_get_asset('lib/tiptip.js', 'js'), array('jquery-core'));

		/*
		 * Ajax list Table pagination
		 */
		$this->register_script('wu-ajax-list-table', wu_get_asset('list-tables.js', 'js'), array('jquery', 'wu-vue', 'underscore', 'wu-flatpicker'));

		/*
		 * Adds jQueryBlockUI
		 */
		$this->register_script('wu-block-ui', wu_get_asset('lib/jquery.blockUI.js', 'js'), array('jquery-core'));

		/*
		 * Adds FontIconPicker
		 */
		$this->register_script('wu-fonticonpicker', wu_get_asset('lib/jquery.fonticonpicker.js', 'js'), array('jquery'));

		/*
		 * Adds Accounting.js
		 */
		$this->register_script('wu-accounting', wu_get_asset('lib/accounting.js', 'js'), array('jquery-core'));

		/*
		 * Adds Cookie Helpers
		 */
		$this->register_script('wu-cookie-helpers', wu_get_asset('cookie-helpers.js', 'js'), array('jquery-core'));

		/*
		 * Adds Input Masking
		 */
		$this->register_script('wu-money-mask', wu_get_asset('lib/v-money.js', 'js'), array('wu-vue'));
		$this->register_script('wu-input-mask', wu_get_asset('lib/vue-the-mask.js', 'js'), array('wu-vue'));

		/*
		 * Adds General Functions
		 */
		$this->register_script('wu-functions', wu_get_asset('functions.js', 'js'), array('jquery-core', 'wu-tiptip', 'wu-flatpicker', 'wu-block-ui', 'wu-accounting', 'clipboard', 'wp-hooks'));

		wp_localize_script('wu-functions', 'wu_settings', array(
			'currency'           => wu_get_setting('currency_symbol', 'USD'),
			'currency_symbol'    => wu_get_currency_symbol(),
			'currency_position'  => wu_get_setting('currency_position'),
			'decimal_separator'  => wu_get_setting('decimal_separator'),
			'thousand_separator' => wu_get_setting('thousand_separator'),
			'precision'          => wu_get_setting('precision', 2),
			'use_container'      => get_user_setting('wu_use_container', false),
			'disable_image_zoom' => wu_get_setting('disable_image_zoom', false),
		));

		/*
		 * Adds Fields & Components
		 */
		$this->register_script('wu-fields', wu_get_asset('fields.js', 'js'), array('jquery', 'wu-vue', 'wu-selectizer', 'wp-color-picker'));

		/*
		 * Localize components
		 */
		wp_localize_script('wu-fields', 'wu_fields', array(
			'l10n' => array(
				'image_picker_title'       => __('Select an Image.', 'wp-ultimo'),
				'image_picker_button_text' => __('Use this image', 'wp-ultimo'),
			),
		));

		/*
		 * Adds Admin Script
		 */
		$this->register_script('wu-admin', wu_get_asset('admin.js', 'js'), array('jquery', 'wu-functions'));

		/*
		 * Adds Vue Apps
		 */
		$this->register_script('wu-vue-apps', wu_get_asset('vue-apps.js', 'js'), array('jquery', 'wu-functions', 'wu-vue', 'wu-money-mask', 'wu-input-mask', 'wp-hooks'));

		/*
		 * Adds Selectizer
		 */
		$this->register_script('wu-selectize', wu_get_asset('lib/selectize.js', 'js'), array('jquery'));
		$this->register_script('wu-selectizer', wu_get_asset('selectizer.js', 'js'), array('wu-selectize', 'underscore', 'wu-vue-apps'));

		/*
		 * Localize selectizer
		 */
		wp_localize_script('wu-functions', 'wu_selectizer', array(
			'ajaxurl' => wu_ajax_url(),
		));

		/*
		 * Adds Form
		 */
		$this->register_script('wu-forms', wu_get_asset('forms.js', 'js'), array());

		/*
		 * Load variables to localized it
		 */
		wp_localize_script('wu-functions', 'wu_ticker', array(
			'server_clock_offset'          => (wu_get_current_time('timestamp') - time()) / 60 / 60, // phpcs:ignore
			'moment_clock_timezone_name'   => wp_date('e'),
			'moment_clock_timezone_offset' => wp_date('Z'),
		));

		/*
		 * Adds our thickbox fork.
		 */
		$this->register_script('wubox', wu_get_asset('wubox.js', 'js/lib'), array('jquery', 'wu-functions', 'wu-forms', 'wu-vue-apps'));

		wp_localize_script('wubox', 'wuboxL10n', array(
			'next'             => __('Next &gt;'),
			'prev'             => __('&lt; Prev'),
			'image'            => __('Image'),
			'of'               => __('of'),
			'close'            => __('Close'),
			'noiframes'        => __('This feature requires inline frames. You have iframes disabled or your browser does not support them.'),
			'loadingAnimation' => includes_url('js/thickbox/loadingAnimation.gif'),
		));

		/*
		 * WordPress localizes month names and all, but
		 * does not localize anything else. We need relative
		 * times to be translated, so we need to do it ourselves.
		 */
		$this->localize_moment();

	} // end register_default_scripts;

	/**
	 * Localize moment.js relative times.
	 *
	 * @since 2.0.8
	 * @return bool
	 */
	public function localize_moment() {

		$time_format = get_option('time_format', __('g:i a'));
		$date_format = get_option('date_format', __('F j, Y'));

		$long_date_formats = array_map('wu_convert_php_date_format_to_moment_js_format', array(
			'LT'   => $time_format,
			'LTS'  => str_replace(':i', ':i:s', $time_format),
			/* translators: the day/month/year date format used by WP Ultimo. You can changed it to localize this date format to your language. the default value is d/m/Y, which is the format 31/12/2021. */
			'L'    => __('d/m/Y', 'wp-ultimo'),
			'LL'   => $date_format,
			'LLL'  => sprintf('%s %s', $date_format, $time_format),
			'LLLL' => sprintf('%s %s', $date_format, $time_format),
		));

		// phpcs:disable
		$strings = array(
			'relativeTime' => array(
				'future' => __('in %s', 'wp-ultimo'),
				'past'   => __('%s ago', 'wp-ultimo'),
				's'      => __('a few seconds', 'wp-ultimo'),
				'ss'     => __('%d seconds', 'wp-ultimo'),
				'm'      => __('a minute', 'wp-ultimo'),
				'mm'     => __('%d minutes', 'wp-ultimo'),
				'h'      => __('an hour', 'wp-ultimo'),
				'hh'     => __('%d hours', 'wp-ultimo'),
				'd'      => __('a day', 'wp-ultimo'),
				'dd'     => __('%d days', 'wp-ultimo'),
				'w'      => __('a week', 'wp-ultimo'),
				'ww'     => __('%d weeks', 'wp-ultimo'),
				'M'      => __('a month', 'wp-ultimo'),
				'MM'     => __('%d months', 'wp-ultimo'),
				'y'      => __('a year', 'wp-ultimo'),
				'yy'     => __('%d years', 'wp-ultimo'),
			),
			'longDateFormat' => $long_date_formats,
		);
		// phpcs:enable

		$inline_script = sprintf("moment.updateLocale( '%s', %s );", get_user_locale(), wp_json_encode($strings));

		return did_action('init') && wp_add_inline_script('moment', $inline_script, 'after');

	} // end localize_moment;

	/**
	 * Registers the default WP Ultimo styles.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_default_styles() {

		$this->register_style('wu-styling', wu_get_asset('framework.css', 'css'), array(), wu_get_version());

		$this->register_style('wu-admin', wu_get_asset('admin.css', 'css'), array('wu-styling'), wu_get_version());

		$this->register_style('wu-checkout', wu_get_asset('checkout.css', 'css'), array(), wu_get_version());

		$this->register_style('wu-flags', wu_get_asset('flags.css', 'css'), array(), wu_get_version());

	} // end register_default_styles;

	/**
	 * Loads the default admin styles.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_default_admin_styles() {

		wp_enqueue_style('wu-admin');

	} // end enqueue_default_admin_styles;

	/**
	 * Loads the default admin scripts.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_default_admin_scripts() {

		wp_enqueue_script('wu-admin');

	} // end enqueue_default_admin_scripts;

	/**
	 * Update the use container setting.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function update_use_container() {

		check_ajax_referer('wu_toggle_container', 'nonce');

		$new_value = (bool) !(get_user_setting('wu_use_container', false));

		set_user_setting('wu_use_container', $new_value);

		wp_die();

	} // end update_use_container;

	/**
	 * Add body classes of container boxed if user has setting.
	 *
	 * @since 2.0.0
	 *
	 * @param string $classes Body classes.
	 * @return string
	 */
	public function add_body_class_container_boxed($classes) {

		if (get_user_setting('wu_use_container', false)) {

			$classes .= ' has-wu-container ';

		} // end if;

		return $classes;

	} // end add_body_class_container_boxed;

} // end class Scripts;
