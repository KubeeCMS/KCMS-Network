<?php
/**
 * WP Ultimo User_Switching
 *
 * Log string messages to a file with a timestamp. Useful for debugging.
 *
 * @package WP_Ultimo
 * @subpackage User_Switching
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo User_Switching
 *
 * @since 2.0.0
 */
class User_Switching {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Constructor for the User_Switching.
	 */
	public function __construct() {

		add_action('plugins_loaded', array($this, 'register_forms'));

	} // end __construct;

	/**
	 * Check if Plugin User Switching is activated
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function check_user_switching_is_activated() {

		return class_exists('user_switching');

	} // end check_user_switching_is_activated;

	/**
	 * Register forms
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function register_forms() {

		wu_register_form('install_user_switching', array(
			'render' => array($this, 'render_install_user_switching'),
		));

	} // end register_forms;

	/**
	 * Create Install Form of User Switching
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function render_install_user_switching() {

		$fields = array(
			'title' => array(
				'type'          => 'text-display',
				'title'         => '',
				'display_value' => __('This feature requires the plugin <strong>User Switching</strong> to be installed and active.', 'wp-ultimo'),
				'tooltip'       => '',
			),
			'link'  => array(
				'type'            => 'link',
				'display_value'   => __('Install User Switching', 'wp-ultimo'),
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end wu-text-center wu-bg-gray-100',
				'html_attr'       => array(
					'href' => add_query_arg(array(
						's'    => 'user-switching',
						'tab'  => 'search',
						'type' => 'tag'
					), network_admin_url('plugin-install.php')
					),
				),
			),
		);

		$form = new \WP_Ultimo\UI\Form('install_user_switching', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(),
		));

		$form->render();

	} // end render_install_user_switching;

	/**
	 * This function return should return the correct url
	 *
	 * @since 2.0.0
	 *
	 * @param int $user_id User Id.
	 *
	 * @return string
	 */
	public function render($user_id) {

		$user = new \WP_User($user_id);

		if (!$this->check_user_switching_is_activated()) {

			return wu_get_form_url('install_user_switching');

		} else {

			$link = \user_switching::switch_to_url($user);

			return $link;

		} // end if;

	}  // end render;

} // end class User_Switching;
