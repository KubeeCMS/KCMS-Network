<?php
/**
 * Base customer facing admin page class.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Admin_Pages\Base_Admin_Page;

/**
 * Abstract class that adds customizability to customer facing pages.
 */
abstract class Base_Customer_Facing_Admin_Page extends Base_Admin_Page {

	/**
	 * The capability required to be able to activate the customize mode.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $edit_capability = 'manage_network';

	/**
	 * The current editing status of this page.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $editing = false;

	/**
	 * Holds the original parameters before we change them.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $original_parameters = array();

	/**
	 * If this customer facing page has menu settings.
	 *
	 * @since 2.0.9
	 * @var boolean
	 */
	protected $menu_settings = true;

	/**
	 * Allow child classes to add further initializations.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function init() {

		$this->change_parameters();

		parent::init();

		$this->editing = wu_request('customize');

		add_action('wu_enqueue_extra_hooks', array($this, 'additional_hooks'));

		add_action('updated_user_meta', array($this, 'save_settings'), 10, 4);

		wu_register_form("edit_admin_page_$this->id", array(
			'render'     => array($this, 'render_edit_page'),
			'handler'    => array($this, 'handle_edit_page'),
			'capability' => 'exist',
		));

		$this->register_page_settings();

	} // end init;

	/**
	 * Saves the original parameters and change them with the settings saved.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function change_parameters() {

		$this->original_parameters = array(
			'title'     => $this->get_title(),
			'position'  => $this->position,
			'menu_icon' => $this->menu_icon,
		);

		$new_parameters = $this->get_page_settings();

		$this->title      = wu_get_isset($new_parameters, 'title', $this->original_parameters['title']);
		$this->menu_title = wu_get_isset($new_parameters, 'title', $this->original_parameters['title']);
		$this->position   = wu_get_isset($new_parameters, 'position', $this->original_parameters['position']);
		$this->menu_icon  = str_replace('dashicons-before', '', wu_get_isset($new_parameters, 'menu_icon', $this->original_parameters['menu_icon']));

	} // end change_parameters;

	/**
	 * Renders the edit page form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_edit_page() {

		$settings = $this->get_page_settings();

		$fields = array();

		$fields['title'] = array(
			'type'    => 'text',
			'title'   => __('Page & Menu Title', 'wp-ultimo'),
			'value'   => wu_get_isset($settings, 'title', ''),
			'tooltip' => '',
		);

		if ($this->menu_settings) {

			$fields['position'] = array(
				'type'    => 'number',
				'title'   => __('Menu', 'wp-ultimo'),
				'value'   => wu_get_isset($settings, 'position', ''),
				'tooltip' => '',
			);

			$fields['menu_icon'] = array(
				'type'    => 'dashicon',
				'title'   => __('Menu Icon', 'wp-ultimo'),
				'value'   => wu_get_isset($settings, 'menu_icon', ''),
				'tooltip' => '',
			);

		} // end if;

		$fields['save_line'] = array(
			'type'            => 'group',
			'classes'         => 'wu-justify-between',
			'wrapper_classes' => 'wu-bg-gray-100',
			'fields'          => array(
				'reset'  => array(
					'type'            => 'submit',
					'title'           => __('Reset Settings', 'wp-ultimo'),
					'value'           => 'edit',
					'classes'         => 'button',
					'wrapper_classes' => 'wu-mb-0',
				),
				'submit' => array(
					'type'            => 'submit',
					'title'           => __('Save Changes', 'wp-ultimo'),
					'value'           => 'edit',
					'classes'         => 'button button-primary',
					'wrapper_classes' => 'wu-mb-0',
				),
			),
		);

		$fields = apply_filters("wu_customer_facing_page_{$this->id}_fields", $fields);

		$form = new \WP_Ultimo\UI\Form('edit_page', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => "{$this->id}_page_customize",
				'data-state'  => wu_convert_to_state(),
			),
		));

		echo '<div class="wu-styling">';

		$form->render();

		echo '</div>';

	} // end render_edit_page;

	/**
	 * Handles the edit page form and saved changes.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_edit_page() {

		$settings_to_save = wu_request('submit') !== 'restore' ? $_POST : array(); // don't worry, this gets cleaned later on.

		$this->save_page_settings($settings_to_save);

		wp_send_json_success(array(
			'redirect_url' => add_query_arg('updated', 1, $_SERVER['HTTP_REFERER']),
		));

	} // end handle_edit_page;

	/**
	 * Generates a unique id for each page based on the class name.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_page_unique_id() {

		$class_name_array = explode('\\', get_class($this));

		$class_name = array_pop($class_name_array);

		return wu_replace_dashes(strtolower($class_name));

	} // end get_page_unique_id;

	/**
	 * Grabs the original page parameters.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_defaults() {

		return $this->original_parameters;

	} // end get_defaults;

	/**
	 * Register the default setting on the core section.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_page_settings() {

		wu_register_settings_field('core', $this->get_page_unique_id() . '_settings', array(
			'raw' => true,
		));

	} // end register_page_settings;

	/**
	 * Get the page settings saved.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_page_settings() {

		$atts = wu_get_setting($this->get_page_unique_id() . '_settings', array());

		return wp_parse_args($atts, $this->get_defaults());

	} // end get_page_settings;

	/**
	 * Saves the page settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings List of page settings.
	 * @return boolean
	 */
	public function save_page_settings($settings) {

		$atts = shortcode_atts($this->get_defaults(), $settings); // Use shortcode atts to remove unauthorized params.

		return wu_save_setting($this->get_page_unique_id() . '_settings', $atts);

	} // end save_page_settings;

	/**
	 * Adds additional hooks using the right hook on the page lifecycle.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function additional_hooks() {

		add_action("load-$this->page_hook", array($this, 'register_additional_scripts'));

		add_action("load-$this->page_hook", array($this, 'add_additional_body_classes'));

		add_action("load-$this->page_hook", array($this, 'additional_on_page_load'));

	} // end additional_hooks;

	/**
	 * Registers additional hooks for the page load.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function additional_on_page_load() {

		add_filter('wu_element_display_super_admin_notice', array($this, 'is_edit_mode'));

		add_action("get_user_option_meta-box-order_{$this->page_hook}", array($this, 'get_settings'), 10, 3);

		add_action("get_user_option_screen_layout_{$this->page_hook}", array($this, 'get_settings'), 10, 3);

		/**
		 * 'Hack-y' solution for the customer facing title problem... but good enough for now.
		 *
		 * @todo review when possible.
		 */
		add_filter('wp_ultimo_render_vars', function($vars) {

			$vars['page_title'] = $this->title;

			return $vars;

		}, 15);

	} // end additional_on_page_load;

	/**
	 * Adds additional body classes for styling control purposes.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function add_additional_body_classes() {

		add_action('admin_body_class', function($classes) {

			$additional_class = $this->is_edit_mode() ? 'wu-customize-admin-screen' : '';

			return $additional_class;

		});

	} // end add_additional_body_classes;

	/**
	 * Registers and enqueues additional scripts and styles required.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_additional_scripts() {

		\WP_Ultimo\Scripts::get_instance()->register_style('wu-admin-screen', wu_get_asset('admin-screen.css', 'css'));

		wp_enqueue_style('wu-admin-screen');

		if ($this->is_edit_mode()) {

			wp_enqueue_script('dashboard');

		} // end if;

		if (current_user_can($this->edit_capability)) {

			\WP_Ultimo\Scripts::get_instance()->register_script('wu-admin-screen', wu_get_asset('admin-screen.js', 'js'), array('jquery', 'wu-fonticonpicker'));

			wp_localize_script('wu-admin-screen', 'wu_admin_screen', array(
				'page_customize_link' => wu_get_form_url("edit_admin_page_$this->id"),
				'customize_link'      => add_query_arg('customize', 1),
				'close_link'          => remove_query_arg('customize'),
				'i18n'                => array(
					'page_customize_label' => __('Customize Page', 'wp-ultimo'),
					'customize_label'      => __('Customize Elements', 'wp-ultimo'),
					'close_label'          => __('Exit Customize Mode', 'wp-ultimo'),
				),
			));

			wp_enqueue_script('wu-admin-screen');

		} // end if;

	} // end register_additional_scripts;

	/**
	 * Filters the order and columns of the widgets to return a globally saved value.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $result Original value of the settings being changed.
	 * @param string $option The name of the option/setting being fetched.
	 * @param int    $user The user ID.
	 * @return array
	 */
	public function get_settings($result, $option, $user) {

		$option = wu_replace_dashes($option);

		$saved = wu_get_setting($option);

		return empty($saved) ? $result : $saved;

	} // end get_settings;

	/**
	 * Save the settings globally for columns and order of the widgets.
	 *
	 * @since 2.0.0
	 *
	 * @param int    $meta_id The id of the user meta being saved.
	 * @param int    $user_id The user id.
	 * @param string $meta_key The name of the option/setting being saved.
	 * @param mixed  $_meta_value The original saved value.
	 * @return void
	 */
	public function save_settings($meta_id, $user_id, $meta_key, $_meta_value) {

		if (wu_request('action') !== 'meta-box-order') {

			return;

		} // end if;

		$is_this_page = strpos(wu_request('page'), $this->id) !== false;

		if (!$is_this_page) {

			return;

		} // end if;

		if (!user_can($user_id, $this->edit_capability)) {

			return;

		} // end if;

		$meta_key = wu_replace_dashes($meta_key);

		wu_save_setting($meta_key, $_meta_value);

	} // end save_settings;

	/**
	 * Get the value of editing.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_edit_mode() {

		return $this->editing && current_user_can($this->edit_capability);

	} // end is_edit_mode;

	/**
	 * Adds top-level admin page.
	 *
	 * @since 1.8.2
	 * @return string Page hook generated by WordPress.
	 */
	public function add_toplevel_menu_page() {

		if (wu_request('id')) {

			$this->edit = true;

		} // end if;

		return add_menu_page(
			$this->title,
			$this->title . '&nbsp;' . $this->get_badge(),
			$this->get_capability(),
			$this->id,
			array($this, 'display'),
			$this->menu_icon,
			$this->position
		);

	} // end add_toplevel_menu_page;

} // end class Base_Customer_Facing_Admin_Page;
