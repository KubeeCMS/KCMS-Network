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
		$this->register_script('wu-tiptip', wu_get_asset('lib/tiptip.js', 'js'));

		/*
		* Adds copy to clipboard.js
		*/
		$this->register_script('wu-clipboard', wu_get_asset('lib/clipboard.js', 'js'));

		/*
		 * Ajax list Table pagination
		 */
		$this->register_script('wu-ajax-list-table', wu_get_asset('list-tables.js', 'js'), array('wu-vue', 'underscore', 'wu-flatpicker'));

		/*
		 * Adds jQueryBlockUI
		 */
		$this->register_script('wu-block-ui', wu_get_asset('lib/jquery.blockUI.js', 'js'), array('jquery'));

		/*
		 * Adds FontIconPicker
		 */
		$this->register_script('wu-fonticonpicker', wu_get_asset('lib/jquery.fonticonpicker.js', 'js'), array('jquery'));

		/*
		 * Adds Accounting.js
		 */
		$this->register_script('wu-accounting', wu_get_asset('lib/accounting.js', 'js'), array('jquery'));

		/*
		 * Adds Input Masking
		 */
		$this->register_script('wu-cleave', wu_get_asset('lib/cleave.js', 'js'), false);

		/*
		 * Adds General Functions
		 */
		$this->register_script('wu-functions', wu_get_asset('functions.js', 'js'), array('jquery', 'wu-tiptip', 'wu-flatpicker', 'wu-block-ui', 'wu-accounting', 'wu-cleave', 'wu-clipboard', 'wu-fonticonpicker'));

		wp_localize_script('wu-functions', 'wu_settings', array(
			'currency_symbol'    => wu_get_currency_symbol(),
			'currency_position'  => wu_get_setting('currency_position'),
			'decimal_separator'  => wu_get_setting('decimal_separator'),
			'thousand_separator' => wu_get_setting('thousand_separator'),
			'precision'          => wu_get_setting('precision', 2),
		));

		/*
		 * Adds Fields & Components
		 */
		$this->register_script('wu-fields', wu_get_asset('fields.js', 'js'), array('wu-vue'));

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
		 * Adds admin
		 */
		$this->register_script('wu-admin', wu_get_asset('admin.js', 'js'), array('jquery', 'wu-functions', 'wu-fields'));

		/*
		 * Adds admin
		 */
		$this->register_script('wu-selectize', wu_get_asset('lib/selectize.js', 'js'), array('jquery'));
		$this->register_script('wu-selectizer', wu_get_asset('selectizer.js', 'js'), array('jquery', 'wu-selectize', 'underscore'));

		/*
		 * Adds moment
		 */
		$this->register_script('wu-moment', wu_get_asset('lib/moment-with-locales.js', 'js'), false);

		/*
		 * Adds Form
		 */
		$this->register_script('wu-forms', wu_get_asset('forms.js', 'js'), false);

		/*
		 * Load variables to localized it
		 */
		wp_localize_script('wu-functions', 'wu_ticker', array(
			'server_clock_offset' => (current_time('timestamp') - time()) / 60 / 60,
		));

		/*
		 * Adds our thickbox fork
		 */
		$this->register_script('wubox', wu_get_asset('wubox.js', 'js/lib'), array('jquery', 'wu-functions'));

		wp_localize_script('wubox', 'wuboxL10n', array(
			'next'             => __('Next &gt;'),
			'prev'             => __('&lt; Prev'),
			'image'            => __('Image'),
			'of'               => __('of'),
			'close'            => __('Close'),
			'noiframes'        => __('This feature requires inline frames. You have iframes disabled or your browser does not support them.'),
			'loadingAnimation' => includes_url('js/thickbox/loadingAnimation.gif'),
		));

	} // end register_default_scripts;

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

} // end class Scripts;
