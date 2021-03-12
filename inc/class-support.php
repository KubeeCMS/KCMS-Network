<?php
/**
 * WP Ultimo Support
 *
 * @package WP_Ultimo
 * @subpackage Support
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo: WP_Ultimo Support
 */
class Support {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Api Key of Intercom
	 *
	 * @var string
	 */
	public $app_id = 'k6j07tqb';

	/**
	 * Get things started
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function init() {

		add_action('init', array($this, 'hooks'));

	} // end init;

	/**
	 * The hooks to be added if the features are available.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks() {

		if (License::get_instance()->allowed() && current_user_can('wu_support')) {

			add_action('admin_footer', array($this, 'load_scripts'));

			add_action('wu_footer_left', array($this, 'add_link_wu_header'));

		} // end if;

	} // end hooks;

	/**
	 * Load Assets for support.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function load_scripts() {

		$current_user = wp_get_current_user();

		wp_register_script('wu-support', wu_get_asset('support.js', 'js'), array('jquery'), true);

		wp_localize_script('wu-support', 'wu_support_vars', array(
			'app_id'       => $this->app_id,
			'display_name' => $current_user->display_name,
			'user_email'   => $current_user->user_email,
			'license_key'  => \WP_Ultimo\License::get_instance()->get_license_key(),
		));

		wp_enqueue_script('wu-support');

	} // end load_scripts;

	/**
	 * Display a link on wu header
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function add_link_wu_header() {

		$markup = '
      <li class="wu-inline-block wu-mx-1">
        <a href="#" class="wu-text-gray-500 hover:wu-text-gray-600 wu-trigger-support">
          %s
        </a>
      </li>
    ';

		echo sprintf($markup, __('Live Chat', 'wp-ultimo'));

	} // end add_link_wu_header;

} // end class Support;
