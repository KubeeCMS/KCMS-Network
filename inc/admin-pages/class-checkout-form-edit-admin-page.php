<?php
/**
 * WP Ultimo Checkout_Form Edit/Add New Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Checkout_Form;
use \WP_Ultimo\Managers\Checkout_Form_Manager;
use \WP_Ultimo\Managers\Signup_Fields_Manager;

/**
 * WP Ultimo Checkout_Form Edit/Add New Admin Page.
 */
class Checkout_Form_Edit_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-edit-checkout-form';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * Object ID being edited.
	 *
	 * @since 1.8.2
	 * @var string
	 */
	public $object_id = 'checkout-form';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * This page has no parent, so we need to highlight another sub-menu.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $highlight_menu_slug = 'wp-ultimo-checkout-forms';

	/**
	 * If this number is greater than 0, a badge with the number will be displayed alongside the menu title
	 *
	 * @since 1.8.2
	 * @var integer
	 */
	protected $badge_count = 0;

	/**
	 * Holds the admin panels where this page should be displayed, as well as which capability to require.
	 *
	 * To add a page to the regular admin (wp-admin/), use: 'admin_menu' => 'capability_here'
	 * To add a page to the network admin (wp-admin/network), use: 'network_admin_menu' => 'capability_here'
	 * To add a page to the user (wp-admin/user) admin, use: 'user_admin_menu' => 'capability_here'
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $supported_panels = array(
		'network_admin_menu' => 'wu_edit_checkout_forms',
	);

	/**
	 * Overrides the init method to add additional hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		parent::init();

		add_action('wp_ajax_wu_generate_checkout_form_preview', array($this, 'generate_checkout_form_preview'));

		add_action('wp_ajax_wu_save_editor_session', array($this, 'save_editor_session'));

	} // end init;

	/**
	 * Returns the action links for that page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function action_links() {

		$actions = array();

		if ($this->get_object()->exists()) {

			$url_atts = array(
				'id'    => $this->get_object()->get_id(),
				'slug'  => $this->get_object()->get_slug(),
				'model' => 'checkout_form',
			);

			$actions[] = array(
				'label'   => __('Generate Shortcode'),
				'icon'    => 'wu-copy',
				'classes' => 'wubox',
				'url'     => wu_get_form_url('shortcode_checkout', $url_atts),
			);

		} // end if;

		return $actions;

	} // end action_links;

	/**
	 * Renders the preview of a given form being edited.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function generate_checkout_form_preview() {

		$content = '';

		$settings = wu_request('settings', array());

		$preview_type = wu_request('type', 'user');

		if ($preview_type === 'visitor') {

			global $current_user;

			$current_user = wp_set_current_user(0);

		} // end if;

		$count = count($settings);

		foreach ($settings as $index => $step) {

			$content .= wu_get_template_contents('checkout/form', array(
				'step'               => $step,
				'step_name'          => $step['id'],
				'checkout_form_name' => '',
				'password_strength'  => false,
				'apply_styles'       => true,
				'display_title'      => true,
			));

			if ($index < $count - 1) {

				$content .= sprintf('<hr class="wu-hr-text wu-font-semibold wu-my-4 wu-mt-6 wu-text-gray-600" data-content="%s">', __('Step Separator', 'wp-ultimo'));

			} // end if;

		} // end foreach;

		wp_send_json_success(array(
			'content' => $content,
		));

	} // end generate_checkout_form_preview;

	/**
	 * Save the editor session.
	 *
	 * This is used to edit steps and fields that were not saved.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function save_editor_session() {

		$settings = wu_request('settings', array());

		$form = wu_get_checkout_form(wu_request('form_id'));

		if ($form && $settings) {

			$key = sprintf('checkout_form_%d', $form->get_id());

			$session = wu_get_session($key);

			$session->set('settings', $settings);

			wp_send_json_success();

		} // end if;

		wp_send_json_error();

	} // end save_editor_session;

	/**
	 * Adds hooks when the page loads.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function page_loaded() {

		parent::page_loaded();

		$screen = get_current_screen();

		add_action("wu_edit_{$screen->id}_after_normal", array($this, 'render_steps'));

		add_action('admin_footer', array($this, 'render_js_templates'));

	} // end page_loaded;

	// Forms

	/**
	 * Register ajax forms to handle adding new memberships.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Add new Section
		 */
		wu_register_form('add_new_form_step', array(
			'render'     => array($this, 'render_add_new_form_step_modal'),
			'handler'    => array($this, 'handle_add_new_form_step_modal'),
			'capability' => 'wu_add_checkout_forms',
		));

		/*
		 * Add new Field
		 */
		wu_register_form('add_new_form_field', array(
			'render'     => array($this, 'render_add_new_form_field_modal'),
			'handler'    => array($this, 'handle_add_new_form_field_modal'),
			'capability' => 'wu_add_checkout_forms',
		));

	} // end register_forms;

	/**
	 * Returns the list of available field types.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function field_types() {

		$field_type_objects = Signup_Fields_Manager::get_instance()->get_field_types();

		return array_map(function($class_name) {

			$field = new $class_name;

			return $field->get_field_as_type_option();

		}, $field_type_objects);

	} // end field_types;

	/**
	 * Returns the list of fields for the add/edit new field screen.
	 *
	 * @since 2.0.0
	 * @param array $attributes The field attributes.
	 * @return array
	 */
	public function get_create_field_fields($attributes = array()) {

		$field_types = $this->field_types();

		$fields = array(

			// Tab
			'tab'             => array(
				'type'              => 'tab-select',
				'value'             => 'content',
				'html_attr'         => array(
					'v-model' => 'tab',
				),
				'options'           => array(
					'content'    => __('Content', 'wp-ultimo'),
					'visibility' => __('Visibility', 'wp-ultimo'),
					'style'      => __('Style', 'wp-ultimo'),
				),
				'wrapper_html_attr' => array(
					'v-show' => 'type',
				),
			),

			// Content Tab
			'type'            => array(
				'type'              => 'select-icon',
				'title'             => __('Field Type', 'wp-ultimo'),
				'desc'              => __('Select the type of field you want to add to the checkout form.', 'wp-ultimo'),
				'placeholder'       => '',
				'tooltip'           => '',
				'value'             => '',
				'options'           => $field_types,
				'html_attr'         => array(
					'v-model' => 'type',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type == ""',
					'v-cloak' => 1,
				),
			),
			'type_note'       => array(
				'type'              => 'note',
				'desc'              => sprintf('<a href="#" class="wu-no-underline" v-on:click.prevent="type = \'\'">%s</a>', __('&larr; Back to Field Type Selection', 'wp-ultimo')),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && !name',
					'v-cloak' => '1',
				),
			),
			'step'            => array(
				'type'  => 'hidden',
				'value' => wu_request('step'),
			),
			'checkout_form'   => array(
				'type'  => 'hidden',
				'value' => wu_request('checkout_form'),
			),

			// Visibility Tab
			'logged'          => array(
				'type'              => 'select',
				'value'             => 'always',
				'title'             => __('Logged Status', 'wp-ultimo'),
				'options'           => array(
					'always'      => __('Always show', 'wp-ultimo'),
					'logged_only' => __('Only show for logged in users', 'wp-ultimo'),
					'guests_only' => __('Only show for guests', 'wp-ultimo'),
				),
				'html_attr'         => array(
					'v-model' => 'logged',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && require("tab", "visibility")',
					'v-cloak' => 1,
				),
			),

			// Style Tab
			'width'           => array(
				'type'              => 'text',
				'title'             => __('Width', 'wp-ultimo'),
				'placeholder'       => __('100', 'wp-ultimo'),
				'tooltip'           => __('Do not add the # symbol.', 'wp-ultimo'),
				'min'               => 0,
				'max'               => 100,
				'value'             => 100,
				'html_attr'         => array(
					'v-model'          => 'width',
					'data-cleave'      => true,
					'data-prefix-tail' => true,
					'data-prefix'      => '%',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && require("tab", "style")',
					'v-cloak' => 1,
				),
			),
			'element_id'      => array(
				'type'              => 'text',
				'title'             => __('Element ID', 'wp-ultimo'),
				'placeholder'       => __('myfield', 'wp-ultimo'),
				'tooltip'           => __('Do not add the # symbol.', 'wp-ultimo'),
				'value'             => '',
				'html_attr'         => array(
					'v-model' => 'element_id',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && require("tab", "style")',
					'v-cloak' => 1,
				),
			),
			'element_classes' => array(
				'type'              => 'text',
				'title'             => __('Extra CSS Classes', 'wp-ultimo'),
				'placeholder'       => __('custom-field example-class', 'wp-ultimo'),
				'tooltip'           => __('You can enter multiple CSS classes separated by spaces.', 'wp-ultimo'),
				'value'             => '',
				'html_attr'         => array(
					'v-model' => 'element_classes',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && require("tab", "style")',
					'v-cloak' => 1,
				),
			),

		);

		$additional_fields = array();

		foreach ($field_types as $field_type) {

			$_fields = call_user_func($field_type['fields'], $attributes);

			$additional_fields = array_merge($additional_fields, $_fields);

		} // end foreach;

		$default_fields = \WP_Ultimo\Checkout\Signup_Fields\Base_Signup_Field::fields_list();

		foreach ($default_fields as $default_field_slug => &$default_field) {

			$reqs = $this->get_required_list($default_field_slug, $field_types);

			$default_field['wrapper_html_attr'] = array(
				'v-if'    => sprintf('type && require("type", %s) && require("tab", "content")', json_encode($reqs)),
				'v-cloak' => '1',
			);

			if ($default_field_slug === 'name') {

				unset($default_field['wrapper_html_attr']['v-if']);

				$default_field['wrapper_html_attr']['v-show'] = sprintf('type && require("type", %s) && require("tab", "content")', json_encode($reqs));

			} // end if;

		} // end foreach;

		$fields = array_merge($fields, $default_fields, $additional_fields, array(
			'submit_button' => array(
				'type'              => 'submit',
				'title'             => empty($attributes) ? __('Add Field', 'wp-ultimo') : __('Save Field', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'wrapper_html_attr' => array(
					'v-show'  => 'type',
					'v-cloak' => '1',
				),
			),
		));

		return $fields;

	} // end get_create_field_fields;

	/**
	 * Gets the field from the checkout step OR from the session.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Checkout_Form $checkout_form The checkout form.
	 * @param string                          $step_name The step name.
	 * @param string                          $field_name The field name.
	 * @return array
	 */
	protected function get_field($checkout_form, $step_name, $field_name) {

		$field = $checkout_form->get_field($step_name, $field_name);

		$field['saved'] = true;

		if (!$field) {

			$field['saved'] = false;

		} // end if;

		$key = sprintf('checkout_form_%d', $checkout_form->get_id());

		$session = wu_get_session($key);

		$checkout_form->set_settings($session->get('settings'));

		$field = $checkout_form->get_field($step_name, $field_name);

		return $field;

	} // end get_field;

	/**
	 * Gets the step from the checkout OR from the session.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Checkout_Form $checkout_form The checkout form.
	 * @param string                          $step_name The step name.
	 * @return array
	 */
	protected function get_step($checkout_form, $step_name) {

		$step = $checkout_form->get_step($step_name);

		$step['saved'] = true;

		if (!$step) {

			$step['saved'] = false;

		} // end if;

		$key = sprintf('checkout_form_%d', $checkout_form->get_id());

		$session = wu_get_session($key);

		$checkout_form->set_settings($session->get('settings'));

		$step = $checkout_form->get_step($step_name);

		return $step;

	} // end get_step;

	/**
	 * Adds the modal for adding new fields.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_new_form_field_modal() {

		$checkout_form = wu_get_checkout_form_by_slug(wu_request('checkout_form'));

		if (!$checkout_form) {

			return;

		} // end if;

		$steps = $checkout_form->get_settings();

		$step_name = wu_request('step', current(array_keys($steps)));

		$field_name = wu_request('field');

		$_field = $this->get_field($checkout_form, $step_name, $field_name);

		$edit_fields = $this->get_create_field_fields($_field);

		$state = array_map('__return_empty_string', $edit_fields);

		if ($_field) {

			$state = array_merge($state, $_field);

		} // end if;

		$state['tab'] = 'content';

		if (!wu_get_isset($state, 'logged', false)) {

			$state['logged'] = 'always';

		} // end if;

		$form = new \WP_Ultimo\UI\Form('add_edit_fields_modal', $edit_fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'add_checkout_form_field',
				'data-state'  => wu_convert_to_state($state),
			),
		));

		$form->render();

	} // end render_add_new_form_field_modal;

	/**
	 * Handles the submission of a new form field modal submission.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_new_form_field_modal() {

		$checkout_form = wu_get_checkout_form_by_slug(wu_request('checkout_form'));

		if (!$checkout_form) {

			wp_send_json_error(new \WP_Error(
				'checkout-form-not-found',
				__('The checkout form could not be found.', 'wp-ultimo')
			));

		} // end if;

		$data = array(
			'step' => wu_request('step'),
			'name' => wu_request('label', ''),
			'type' => wu_request('type', 'text'),
		);

		$type = wu_request('type', 'text');

		$field_types = $this->field_types();

		$all_attributes_list = array_combine($field_types[$type]['all_attributes'], $field_types[$type]['all_attributes']);

		$data = array_merge(
			$data,
			$field_types[$type]['force_attributes'],
			array_map(function($item) {

				return wu_request($item, '');

			}, $all_attributes_list)
		);

		wp_send_json_success(array(
			'send' => array(
				'scope'         => 'wu_checkout_forms_editor_app',
				'function_name' => 'add_field',
				'data'          => $data,
			),
		));

	} // end handle_add_new_form_field_modal;

	/**
	 * Renders the content of the edit-add section modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_new_form_step_modal() {

		$checkout_form = wu_get_checkout_form_by_slug(wu_request('checkout_form'));

		if (!$checkout_form) {

			return;

		} // end if;

		$steps = $checkout_form->get_settings();

		$step_name = wu_request('step', current(array_keys($steps)));

		$fields = array(
			'tab'           => array(
				'type'      => 'tab-select',
				'value'     => 'content',
				'html_attr' => array(
					'v-model' => 'tab',
				),
				'options'   => array(
					'content'    => __('Content', 'wp-ultimo'),
					'visibility' => __('Visibility', 'wp-ultimo'),
					'style'      => __('Style', 'wp-ultimo'),
				),
			),

			// Content Tab
			'id'            => array(
				'type'              => 'text',
				'title'             => __('Step Slug', 'wp-ultimo'),
				'placeholder'       => __('step-name', 'wp-ultimo'),
				'tooltip'           => __('This will be used on the URL. Only alpha-numeric and hyphens allowed.', 'wp-ultimo'),
				'value'             => '',
				'html_attr'         => array(
					'required'     => 'required',
					'v-on:input'   => 'id = $event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, "")',
					'v-bind:value' => 'id',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "content")',
					'v-cloak' => 1,
				),
			),
			'label'         => array(
				'type'              => 'text',
				'title'             => __('Step Title', 'wp-ultimo'),
				'placeholder'       => __('Extra Step', 'wp-ultimo'),
				'tooltip'           => '',
				'value'             => '',
				'html_attr'         => array(
					'required' => 'required',
					'v-model'  => 'name',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "content")',
					'v-cloak' => 1,
				),
			),
			'desc'          => array(
				'type'              => 'textarea',
				'title'             => __('Description', 'wp-ultimo'),
				'placeholder'       => __('Step description', 'wp-ultimo'),
				'tooltip'           => '',
				'value'             => '',
				'html_attr'         => array(
					'v-model' => 'desc',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "content")',
					'v-cloak' => 1,
				),
			),

			// Visibility Tab
			'logged'        => array(
				'type'              => 'select',
				'value'             => 'always',
				'title'             => __('Logged Status', 'wp-ultimo'),
				'options'           => array(
					'always'      => __('Always show', 'wp-ultimo'),
					'logged_only' => __('Only show for logged in users', 'wp-ultimo'),
					'guests_only' => __('Only show for guests', 'wp-ultimo'),
				),
				'html_attr'         => array(
					'v-model' => 'logged',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "visibility")',
					'v-cloak' => 1,
				),
			),

			// Style Tab
			'element_id'    => array(
				'type'              => 'text',
				'title'             => __('Element ID', 'wp-ultimo'),
				'placeholder'       => __('myfield', 'wp-ultimo'),
				'tooltip'           => __('Do not add the # symbol.', 'wp-ultimo'),
				'value'             => '',
				'html_attr'         => array(
					'v-model' => 'element_id',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "style")',
					'v-cloak' => 1,
				),
			),

			'classes'       => array(
				'type'              => 'text',
				'title'             => __('Extra CSS Classes', 'wp-ultimo'),
				'placeholder'       => __('custom-field example-class', 'wp-ultimo'),
				'tooltip'           => __('You can enter multiple CSS classes separated by spaces.', 'wp-ultimo'),
				'value'             => '',
				'html_attr'         => array(
					'v-model' => 'classes',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "style")',
					'v-cloak' => 1,
				),
			),

			// Submit Button
			'submit_button' => array(
				'type'              => 'submit',
				'title'             => __('Add Step', 'wp-ultimo'),
				'value'             => 'save',
				'classes'           => 'button button-primary wu-w-full',
				'wrapper_classes'   => 'wu-items-end',
				'wrapper_html_attr' => array(),
			),
			'step'          => array(
				'type'  => 'hidden',
				'value' => wu_request('step'),
			),
			'checkout_form' => array(
				'type'  => 'hidden',
				'value' => wu_request('checkout_form'),
			),
		);

		$_step = $this->get_step($checkout_form, $step_name);

		$state = array_map('__return_empty_string', $fields);

		if ($_step) {

			$state = array_merge($state, $_step);

		} // end if;

		$state['tab'] = 'content';

		$state['logged'] = wu_get_isset($state, 'logged', 'always');

		$form = new \WP_Ultimo\UI\Form('add_new_form_step', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'add_checkout_form_field',
				'data-state'  => wu_convert_to_state($state),
			),
		));

		$form->render();

	} // end render_add_new_form_step_modal;

	/**
	 * Handles the form used to add a new step to the signup.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_new_form_step_modal() {

		$checkout_form = wu_get_checkout_form_by_slug(wu_request('checkout_form'));

		if (!$checkout_form) {

			wp_send_json_error(new \WP_Error(
				'checkout-form-not-found',
				__('The checkout form could not be found.', 'wp-ultimo')
			));

		} // end if;

		$data = array(
			'id'         => wu_request('id', ''),
			'name'       => wu_request('label', ''),
			'desc'       => wu_request('desc', ''),
			'element_id' => wu_request('element_id', ''),
			'classes'    => wu_request('classes', ''),
			'logged'     => wu_request('logged', 'always'),
			'fields'     => array(),
		);

		wp_send_json_success(array(
			'send' => array(
				'scope'         => 'wu_checkout_forms_editor_app',
				'function_name' => 'add_step',
				'data'          => $data,
			),
		));

	} // end handle_add_new_form_step_modal;

	/**
	 * Get the required fields for a given field-type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field_slug Field slug to check.
	 * @param string $field_types List of available field type.
	 * @return array
	 */
	public function get_required_list($field_slug, $field_types) {

		$fields = \WP_Ultimo\Dependencies\Arrch\Arrch::find($field_types, array(
			'sort_key' => 'order',
			'where'    => array(
				array('default_fields', '~', $field_slug),
			),
		));

		return array_keys($fields);

	} // end get_required_list;

	// Render JS Templates

	/**
	 * Render the steps to be used by Vue.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_steps() {

		wu_get_template('base/checkout-forms/steps', array(
			'checkout_form' => $this->get_object()->get_slug(),
		));

	} // end render_steps;

	/**
	 * Renders the Vue JS Templates.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_js_templates() {

		wu_get_template('base/checkout-forms/js-templates', array(
			'checkout_form' => $this->get_object()->get_slug(),
		));

	} // end render_js_templates;

	// Boilerplate

	/**
	 * Registers the necessary scripts and styles for this admin page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		wp_enqueue_code_editor(array('type' => 'text/html'));

		wp_enqueue_script('csslint');

		wp_enqueue_script('htmlhint');

		wp_register_script('wu-checkout-form-editor', wu_get_asset('checkout-forms-editor.js', 'js'), array('jquery'));

		$index = 0;

		$steps = $this->get_object()->get_settings();

		wp_localize_script('wu-checkout-form-editor', 'wu_checkout_form', array(
			'form_id'       => $this->get_object()->get_id(),
			'checkout_form' => $this->get_object()->get_slug(),
			'steps'         => $steps,
			// 'dragging'      => false,
			'headers'       => array(
				'order' => __('Order', 'wp-ultimo'),
				'name'  => __('Label', 'wp-ultimo'),
				'type'  => __('Type', 'wp-ultimo'),
				'slug'  => __('Slug', 'wp-ultimo'),
				'move'  => '',
			),
		));

		wp_enqueue_script('wu-checkout-form-editor');

		wp_enqueue_script('wu-vue-sortable', '//cdn.jsdelivr.net/npm/sortablejs@1.8.4/Sortable.min.js', array());
		wp_enqueue_script('wu-vue-draggable', '//cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.20.0/vuedraggable.umd.min.js', array());

	} // end register_scripts;

	/**
	 * Returns the array of thank you page fields, based on the element.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_thank_you_page_fields() {

		$thank_you_settings = $this->get_thank_you_settings();

		$fields = \WP_Ultimo\UI\Thank_You_Element::get_instance()->fields();

		$new_fields = array();

		foreach ($fields as $index => $field) {

			if ($field['type'] === 'header') {

				continue;

			} // end if;

			if (wu_get_isset($thank_you_settings, $index)) {

				$field['value'] = $thank_you_settings[$index];

			} // end if;

			$new_fields["meta[wu_thank_you_settings][$index]"] = $field;

		} // end foreach;

		$new_fields['conversion_snippets'] = array(
			'type'    => 'code-editor',
			'title'   => __('Conversion Snippets', 'wp-ultimo'),
			'tooltip' => __('Add custom snippets in HTML (with javascript support) to add conversion tracking pixels and such. This code is only run on the successful Thank You step.', 'wp-ultimo'),
			'value'   => $this->get_object()->get_conversion_snippets(),
			'lang'    => 'htmlmixed',
		);

		return $new_fields;

	} // end get_thank_you_page_fields;

	/**
	 * Returns the values of the thank you page settings.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_thank_you_settings() {

		$defaults = \WP_Ultimo\UI\Thank_You_Element::get_instance()->defaults();

		$settings = wp_parse_args($this->get_object()->get_meta('wu_thank_you_settings'), $defaults);

		return $settings;

	} // end get_thank_you_settings;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		parent::register_widgets();

		$this->add_tabs_widget('advanced', array(
			'title'     => __('Advanced Options', 'wp-ultimo'),
			'position'  => 'advanced',
			'html_attr' => array(
				'data-on-load' => 'wu_initialize_code_editors',
			),
			'sections'  => array(
				'thank-you'    => array(
					'title'  => __('Thank You', 'wp-ultimo'),
					'desc'   => __('Configure the Thank You page for this Checkout Form.', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-emoji-happy',
					'state'  => array(
						'enable_thank_you_page' => $this->get_object()->has_thank_you_page(),
						'thank_you_page'        => $this->get_object()->get_thank_you_page_id(),
					),
					'fields' => $this->get_thank_you_page_fields(),
				),
				'scripts'      => array(
					'title'  => __('Scripts', 'wp-ultimo'),
					'desc'   => __('Configure the Thank You page for this Checkout Form.', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-code',
					'state'  => array(
						'enable_thank_you_page' => $this->get_object()->has_thank_you_page(),
						'thank_you_page'        => $this->get_object()->get_thank_you_page_id(),
					),
					'fields' => array(
						'custom_css' => array(
							'type'    => 'code-editor',
							'title'   => __('Custom CSS', 'wp-ultimo'),
							'tooltip' => __('Add custom CSS code to your checkout form. SCSS syntax is supported.', 'wp-ultimo'),
							'value'   => $this->get_object()->get_custom_css(),
							'lang'    => 'css',
						),
					),
				),
				'restrictions' => array(
					'title'  => __('Restrictions', 'wp-ultimo'),
					'desc'   => __('Control the access to this checkout form.', 'wp-ultimo'),
					'icon'   => 'dashicons-wu-block',
					'state'  => array(
						'restrict_by_country' => $this->get_object()->has_country_lock(),
					),
					'fields' => array(
						'restrict_by_country' => array(
							'type'      => 'toggle',
							'title'     => __('Restrict by Country', 'wp-ultimo'),
							'desc'      => __('Restrict this checkout form to specific countries.', 'wp-ultimo'),
							'html_attr' => array(
								'v-model' => 'restrict_by_country',
							)
						),
						'allowed_countries'   => array(
							'type'              => 'select',
							'title'             => __('Allowed Countries', 'wp-ultimo'),
							'desc'              => __('Select the allowed countries.', 'wp-ultimo'),
							'placeholder'       => __('Type to search countries...', 'wp-ultimo'),
							'options'           => 'wu_get_countries',
							'value'             => $this->get_object()->get_allowed_countries(),
							'wrapper_html_attr' => array(
								'v-show' => 'require("restrict_by_country", true)'
							),
							'html_attr'         => array(
								'v-cloak'        => 1,
								'data-selectize' => 1,
								'multiple'       => true,
							)
						),
					),
				),
			),
		));

		$this->add_list_table_widget('events', array(
			'title'        => __('Events', 'wp-ultimo'),
			'table'        => new \WP_Ultimo\List_Tables\Inside_Events_List_Table(),
			'query_filter' => array($this, 'query_filter'),
			'position'     => 'advanced',
		));

		$this->add_save_widget('save', array(
			'html_attr' => array(
				'data-wu-app' => 'checkout-form',
				'data-state'  => wu_convert_to_state(array(
					'original_slug' => $this->get_object()->get_slug(),
					'slug'          => $this->get_object()->get_slug(),
				)),
			),
			'fields'    => array(
				'slug' => array(
					'type'              => 'text',
					'title'             => __('Checkout Form Slug', 'wp-ultimo'),
					'tooltip'           => __('This is used to create shortcodes and more.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_slug(),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
					'html_attr' => array(
						'required'     => 'required',
						'v-on:input'   => 'slug = $event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, "")',
						'v-bind:value' => 'slug',
					),
				),
				'slug_change_note' => array(
					'type'              => 'note',
					'desc'              => __('You are changing the form slug. If you save this change, all the shortcodes and blocks referencing this slug will stop working until you update them with the new slug.', 'wp-ultimo'),
					'classes'           => 'wu-p-2 wu-bg-yellow-200 wu-text-yellow-700 wu-rounded wu-w-full',
					'wrapper_html_attr' => array(
						'v-show'  => '(original_slug != slug) && slug',
						'v-cloak' => '1',
					),
				),
			),
		));

		$this->add_fields_widget('active', array(
			'title'  => __('Is Active?', 'wp-ultimo'),
			'fields' => array(
				'active' => array(
					'type'  => 'toggle',
					'title' => __('Is Active?', 'wp-ultimo'),
					'desc'  => __('Deactivate this checkout form.', 'wp-ultimo'),
					'value' => $this->get_object()->is_active(),
				),
			),
		));

		\WP_Ultimo\UI\Tours::get_instance()->create_tour('checkout-form-editor', array(
			array(
				'id'    => 'checkout-form-editor',
				'title' => __('Welcome to the Checkout Form builder!', 'wp-ultimo'),
				'text'  => array(
					__('You should be able to create registration forms in any way, shape, and form you desire. This editor allows you to do just that 😃', 'wp-ultimo'),
					__('Want a registration form with multiple steps? Check! A single step? Check! Control the visibility of certain steps and fields based on the context of the customer? Check!', 'wp-ultimo'),
				),
			),
			array(
				'id'       => 'add-new-step',
				'title'    => __('Adding new Steps', 'wp-ultimo'),
				'text'     => array(
					__('To add a new step to the registration form, use this button here.', 'wp-ultimo'),
				),
				'attachTo' => array(
					'element' => '#wp-ultimo-list-table-add-new-1 > div > div.wu-w-1\/2.wu-text-right > ul > li:nth-child(2) > a',
					'on'      => 'left',
				),
			),
			array(
				'id'       => 'add-new-field',
				'title'    => __('Adding new Fields', 'wp-ultimo'),
				'text'     => array(
					__('To add a new field to a step, use this button here. You can add fields to capture additional data from your customers and use that data to populate site templates.', 'wp-ultimo'),
					sprintf('<a class="wu-no-underline" href="%s" target="_blank">%s</a>', wu_get_documentation_url('wp-ultimo-populate-site-template'), __('You can learn more about that here &rarr;', 'wp-ultimo')),
				),
				'attachTo' => array(
					'element' => '#wp-ultimo-list-table-checkout > div.inside > div.wu-bg-gray-100.wu-px-4.wu-py-3.wu--m-3.wu-mt-3.wu-border-t.wu-border-l-0.wu-border-r-0.wu-border-b-0.wu-border-gray-400.wu-border-solid.wu-text-right > ul > li:nth-child(3) > a',
					'on'      => 'left',
				),
			),
		));

	} // end register_widgets;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return $this->edit ? __('Edit Checkout Form', 'wp-ultimo') : __('Add new Checkout Form', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('Edit Checkout_Form', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return array(
			'edit_label'          => __('Edit Checkout Form', 'wp-ultimo'),
			'add_new_label'       => __('Add new Checkout Form', 'wp-ultimo'),
			'updated_message'     => __('Checkout Form updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Checkout Form Name', 'wp-ultimo'),
			'title_description'   => __('This name is used for internal reference only.', 'wp-ultimo'),
			'save_button_label'   => __('Save Checkout Form', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete Checkout Form', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Filters the list table to return only relevant events.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query args passed to the list table.
	 * @return array Modified query args.
	 */
	public function query_filter($args) {

		$extra_args = array(
			'object_type' => 'checkout_form',
			'object_id'   => abs($this->get_object()->get_id()),
		);

		return array_merge($args, $extra_args);

	} // end query_filter;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Checkout_Form
	 */
	public function get_object() {

		if ($this->object !== null) {

			return $this->object;

		} // end if;

		$item_id = wu_request('id', 0);

		$item = wu_get_checkout_form($item_id);

		if (!$item) {

			wp_redirect(wu_network_admin_url('wp-ultimo-checkout-forms'));

			exit;

		} // end if;

		$this->object = $item;

		return $this->object;

	} // end get_object;

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save() {

		if (!wu_request('restrict_by_country')) {

			$_POST['allowed_countries'] = array();

		} // end if;

		$_POST['settings'] = json_decode(stripslashes($_POST['_settings']), true);

		/**
		 * Prevent parents redirect to perform additional checks to destroy session.
		 */
		ob_start();

		parent::handle_save();

		$object = $this->get_object();

		$key = sprintf('checkout_form_%d', $object->get_id());

		if (!is_wp_error($object->validate())) {

			$session = wu_get_session($key);

			$session->destroy();

		} // end if;

		ob_flush();

	} // end handle_save;

	/**
	 * Checkout_Forms have titles.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return true;

	} // end has_title;

} // end class Checkout_Form_Edit_Admin_Page;
