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

		add_action('init', array($this, 'generate_checkout_form_preview'), 9);

		add_action('wp_ajax_wu_save_editor_session', array($this, 'save_editor_session'));

		add_action('load-admin_page_wp-ultimo-edit-checkout-form', array($this, 'add_width_control_script'));

	} // end init;

	/**
	 * Adds the script that controls
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function add_width_control_script() {

		wp_enqueue_script('wu-checkout-form-edit-modal', wu_get_asset('checkout-form-editor-modal.js', 'js'), false, wu_get_version());

	} // end add_width_control_script;

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

		if (wu_request('action') === 'wu_generate_checkout_form_preview') {

			// disable unnecessary filters
			add_filter('show_admin_bar', '__return_false');
			add_filter('wu_is_jumper_enabled', '__return_false');
			add_filter('wu_is_toolbox_enabled', '__return_false');

			add_action('wp', array($this, 'content_checkout_form_by_settings'));

		} // end if;

	} // end generate_checkout_form_preview;

	/**
	 * Filter the content to render checkout form by settings.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function content_checkout_form_by_settings() {

		$checkout_form = wu_get_checkout_form(wu_request('form_id'));

		if (!$checkout_form) {

			return __('Something wrong happened.', 'wp-ultimo');

		} // end if;

		$content = '';

		$key = sprintf('checkout_form_%d', $checkout_form->get_id());

		$session = wu_get_session($key);

		$settings_session = $session->get('settings');

		if (!empty($settings_session)) {

			$checkout_form->set_settings($settings_session);

		} // end if;

		$settings = $checkout_form->get_settings();

		$preview_type = wu_request('type', 'user');

		if ($preview_type === 'visitor') {

			global $current_user;

			$current_user = wp_set_current_user(0);

		} // end if;

		$count = count($settings);

		foreach ($settings as $index => $step) {

			$final_fields = wu_create_checkout_fields($step['fields']);

			$content .= wu_get_template_contents('checkout/form', array(
				'step'               => $step,
				'step_name'          => $step['id'],
				'final_fields'       => $final_fields,
				'checkout_form_name' => '',
				'password_strength'  => false,
				'apply_styles'       => true,
				'display_title'      => true,
			));

			if ($index < $count - 1) {

				$content .= sprintf('<hr class="sm:wu-bg-transparent wu-hr-text wu-font-semibold wu-my-4 wu-mt-6 wu-text-gray-600 wu-text-sm" data-content="%s">', __('Step Separator', 'wp-ultimo'));

			} // end if;

		} // end foreach;

		wp_enqueue_scripts();

		wp_print_head_scripts();

		echo sprintf('<body %s>', 'class="' . esc_attr(implode(' ', get_body_class('wu-styling'))) . '"');

		echo '<div class="wu-p-6">';

		echo $content;

		wp_print_footer_scripts();

		echo '</div></body>';

		exit;

	} // end content_checkout_form_by_settings;

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
			'capability' => 'wu_edit_checkout_forms',
		));

		/*
		 * Add new Field
		 */
		wu_register_form('add_new_form_field', array(
			'render'     => array($this, 'render_add_new_form_field_modal'),
			'handler'    => array($this, 'handle_add_new_form_field_modal'),
			'capability' => 'wu_edit_checkout_forms',
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

		$fields = array_map(function($class_name) {

			$field = new $class_name;

			/*
			 * Remove the hidden fields.
			 */
			if ($field->is_hidden()) {

				return null;

			} // end if;

			return $field->get_field_as_type_option();

		}, $field_type_objects);

		return array_filter($fields);

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
			'tab'                     => array(
				'type'              => 'tab-select',
				'value'             => 'field',
				'order'             => 0,
				'html_attr'         => array(
					'v-model' => 'tab',
				),
				'options'           => array(
					'content'  => __('Field', 'wp-ultimo'),
					'advanced' => __('Additional Settings', 'wp-ultimo'),
					'style'    => __('Style', 'wp-ultimo'),
				),
				'wrapper_html_attr' => array(
					'v-show' => 'type',
				),
			),

			// Content Tab
			'type'                    => array(
				'type'              => 'select-icon',
				'title'             => __('Field Type', 'wp-ultimo'),
				'desc'              => __('Select the type of field you want to add to the checkout form.', 'wp-ultimo'),
				'placeholder'       => '',
				'tooltip'           => '',
				'value'             => '',
				'classes'           => 'wu-w-1/4',
				'options'           => $field_types,
				'html_attr'         => array(
					'v-model' => 'type',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type == ""',
					'v-cloak' => 1,
				),
			),
			'type_note'               => array(
				'type'              => 'note',
				'order'             => 0,
				'desc'              => sprintf('<a href="#" class="wu-no-underline wu-mt-1 wu-uppercase wu-text-2xs wu-font-semibold wu-text-gray-600" v-on:click.prevent="type = \'\'">%s</a>', __('&larr; Back to Field Type Selection', 'wp-ultimo')),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && (!saved && !name)',
					'v-cloak' => '1',
				),
			),
			'step'                    => array(
				'type'  => 'hidden',
				'value' => wu_request('step'),
			),
			'checkout_form'           => array(
				'type'  => 'hidden',
				'value' => wu_request('checkout_form'),
			),

			// Advanced Tab
			'from_request'            => array(
				'type'              => 'toggle',
				'value'             => 'always',
				'title'             => __('Pre-fill from Request', 'wp-ultimo'),
				'tooltip'           => __('The key is the field slug. If your field has the slug "my-color" for example, adding ?my-color=blue will pre-fill this field with the value "blue".', 'wp-ultimo'),
				'desc'              => __('Enable this to allow this field to be pre-filled based on the request parameters.', 'wp-ultimo'),
				'value'             => 1,
				'order'             => 100,
				'html_attr'         => array(
					'v-model'                  => 'from_request',
					'v-initempty:from_request' => 'true',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && require("tab", "advanced")',
					'v-cloak' => 1,
				),
			),

			'logged'                  => array(
				'type'              => 'select',
				'value'             => 'always',
				'title'             => __('Field Visibility', 'wp-ultimo'),
				'desc'              => __('Select the visibility of this field.', 'wp-ultimo'),
				'options'           => array(
					'always'      => __('Always show', 'wp-ultimo'),
					'logged_only' => __('Only show for logged in users', 'wp-ultimo'),
					'guests_only' => __('Only show for guests', 'wp-ultimo'),
				),
				'html_attr'         => array(
					'v-model' => 'logged',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && require("tab", "advanced")',
					'v-cloak' => 1,
				),
			),

			'original_id'             => array(
				'type'      => 'hidden',
				'value'     => wu_request('id', ''),
				'html_attr' => array(
					'v-bind:value' => 'original_id',
				),
			),

			// Style Tab
			'width'                   => array(
				'type'              => 'number',
				'title'             => __('Wrapper Width', 'wp-ultimo'),
				'placeholder'       => __('100', 'wp-ultimo'),
				'desc'              => __('Set the width of this field wrapper (in %).', 'wp-ultimo'),
				'min'               => 0,
				'max'               => 100,
				'value'             => 100,
				'order'             => 52,
				'html_attr'         => array(
					'v-model' => 'width',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && require("tab", "style")',
					'v-cloak' => 1,
				),
			),
			'wrapper_element_classes' => array(
				'type'              => 'text',
				'title'             => __('Wrapper CSS Classes', 'wp-ultimo'),
				'placeholder'       => __('e.g. custom-field example-class', 'wp-ultimo'),
				'desc'              => __('You can enter multiple CSS classes separated by spaces. These will be applied to the field wrapper element.', 'wp-ultimo'),
				'value'             => '',
				'order'             => 54,
				'html_attr'         => array(
					'v-model' => 'wrapper_element_classes',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'type && require("tab", "style")',
					'v-cloak' => 1,
				),
			),
			'element_classes'         => array(
				'type'              => 'text',
				'title'             => __('Field CSS Classes', 'wp-ultimo'),
				'placeholder'       => __('e.g. custom-field example-class', 'wp-ultimo'),
				'desc'              => __('You can enter multiple CSS classes separated by spaces. These will be applied to the field element itself, when possible.', 'wp-ultimo'),
				'value'             => '',
				'order'             => 56,
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

		$index = 0;

		foreach ($default_fields as $default_field_slug => &$default_field) {

			$default_field['order'] = $index + 10;

			$index++;

			$reqs = $this->get_required_list($default_field_slug, $field_types);

			$tab = wu_get_isset($default_field, 'tab', 'content');

			$default_field['wrapper_html_attr'] = array_merge(wu_get_isset($default_field, 'wrapper_html_attr', array()), array(
				'v-if'    => sprintf('type && require("type", %s) && require("tab", "%s")', json_encode($reqs), $tab),
				'v-cloak' => '1',
			));

			if ($default_field_slug === 'name' || $default_field_slug === 'id' || $default_field_slug === 'default_value') {

				unset($default_field['wrapper_html_attr']['v-if']);

				$default_field['wrapper_html_attr']['v-show'] = sprintf('type && require("type", %s) && require("tab", "%s")', json_encode($reqs), $tab);

			} // end if;

			if ($default_field_slug === 'id') {

				$default_field['html_attr']['v-bind:required'] = sprintf('type && require("type", %s) && require("tab", "content")', json_encode($reqs));

			} // end if;

		} // end foreach;

		$fields = array_merge($fields, $default_fields, $additional_fields, array(
			'submit_button' => array(
				'type'              => 'submit',
				'title'             => empty($attributes) ? __('Add Field', 'wp-ultimo') : __('Save Field', 'wp-ultimo'),
				'value'             => 'save',
				'order'             => 100,
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

		if (!$field) {

			$field = array();

			$field['saved'] = false;

		} else {

			$field['saved'] = true;

		} // end if;

		$key = sprintf('checkout_form_%d', $checkout_form->get_id());

		$session = wu_get_session($key);

		$settings = $session->get('settings');

		if (!empty($settings)) {

			$checkout_form->set_settings($settings);

			$new_field = $checkout_form->get_field($step_name, $field_name);

			if (is_array($new_field)) {

				$field = array_merge($field, $new_field);

			} // end if;

		} // end if;

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

		$state = array_map(function($field) {

			$value = wu_get_isset($field, 'value', wu_get_isset($field, 'default', ''));

			return $value;

		}, $edit_fields);

		if ($_field) {

			$state = array_merge($state, $_field);

		} // end if;

		$state = array_map(function($value) {

			if ($value === 'false' || $value === 'true') {

				$value = (int) wu_string_to_bool($value);

			} // end if;

			return $value;

		}, $state);

		$state['tab'] = 'content';

		if (!wu_get_isset($state, 'logged', false)) {

			$state['logged'] = 'always';

		} // end if;

		if (!wu_get_isset($state, 'period_options', false)) {

			$state['period_options'] = array(
				array(
					'duration'      => 1,
					'duration_unit' => 'month',
					'label'         => __('Monthly'),
				),
			);

		} // end if;

		if (!wu_get_isset($state, 'options', false)) {

			$state['options'] = array();

		} // end if;

		if (!wu_get_isset($state, 'save_as', false)) {

			$state['save_as'] = 'customer_meta';

		} // end if;

		if (!wu_get_isset($state, 'auto_generate', false)) {

			$state['auto_generate'] = 0;

		} // end if;

		if (!wu_get_isset($state, 'original_id', false)) {

			$state['original_id'] = wu_get_isset($state, 'id', '');

		} // end if;

		$state['from_request'] = wu_string_to_bool(wu_get_isset($state, 'from_request', true));

		uasort($edit_fields, 'wu_sort_by_order');

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
			'id'           => wu_request('id', ''),
			'original_id'  => wu_request('original_id', ''),
			'step'         => wu_request('step'),
			'name'         => wu_request('label', ''),
			'type'         => wu_request('type', 'text'),
			'from_request' => wu_request('from_request', false),
		);

		$type = wu_request('type', 'text');

		$field_types = $this->field_types();

		$all_attributes_list = array_combine($field_types[$type]['all_attributes'], $field_types[$type]['all_attributes']);

		$data = array_merge(
			$data,
			$field_types[$type]['force_attributes'],
			array_map(function($item) use ($data) {

				return wu_request($item, wu_get_isset($data, $item, ''));

			}, $all_attributes_list)
		);

		/**
		 * Auto-assign ID if none is set
		 */
		if (wu_get_isset($data, 'id', '') === '') {

			$data['id'] = wu_get_isset($data, 'type', 'field') . '-' . uniqid();

		} // end if;

		/*
		 * Allow developers to change the id of the fields.
		 */
		$data['id'] = apply_filters("wu_checkout_form_field_{$type}_id", $data['id'], $data, $checkout_form, $type);

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

		$_step = $this->get_step($checkout_form, $step_name);

		$fields = array(
			'tab'           => array(
				'type'      => 'tab-select',
				'value'     => 'content',
				'order'     => 0,
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
				'title'             => __('Step ID', 'wp-ultimo'),
				'placeholder'       => __('e.g. step-name', 'wp-ultimo'),
				'desc'              => __('This will be used on the URL. Only alpha-numeric and hyphens allowed.', 'wp-ultimo'),
				'value'             => '',
				'html_attr'         => array(
					'required'     => 'required',
					'v-on:input'   => 'id = $event.target.value.toLowerCase().replace(/[^a-z0-9-_]+/g, "")',
					'v-bind:value' => 'id',
					'required'     => 'require("tab", "content")',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "content")',
					'v-cloak' => 1,
				),
			),
			'name'          => array(
				'type'              => 'text',
				'title'             => __('Step Title', 'wp-ultimo'),
				'placeholder'       => __('e.g. My Extra Step', 'wp-ultimo'),
				'desc'              => __('Mostly used internally, but made available for templates.', 'wp-ultimo'),
				'tooltip'           => '',
				'value'             => '',
				'html_attr'         => array(
					'v-model'  => 'name',
					'required' => 'require("tab", "content")',
				),
				'wrapper_html_attr' => array(
					'v-show'  => 'require("tab", "content")',
					'v-cloak' => 1,
				),
			),
			'desc'          => array(
				'type'              => 'textarea',
				'title'             => __('Step Description', 'wp-ultimo'),
				'placeholder'       => __('e.g. This is the last step!', 'wp-ultimo'),
				'desc'              => __('Mostly used internally, but made available for templates.', 'wp-ultimo'),
				'tooltip'           => '',
				'value'             => '',
				'html_attr'         => array(
					'v-model' => 'desc',
					'rows'    => 3,
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
				'desc'              => __('Select the visibility of this step.', 'wp-ultimo'),
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
				'desc'              => __('A custom ID to be added to the form element. Do not add the # symbol.', 'wp-ultimo'),
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
				'desc'              => __('You can enter multiple CSS classes separated by spaces.', 'wp-ultimo'),
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
				'title'             => empty($_step) ? __('Add Step', 'wp-ultimo') : __('Save Step', 'wp-ultimo'),
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
			'name'       => wu_request('name', ''),
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

		WP_Ultimo()->scripts->register_script('wu-checkout-form-editor', wu_get_asset('checkout-forms-editor.js', 'js'), array('jquery'));

		$index = 0;

		$steps = $this->get_object()->get_settings();

		wp_localize_script('wu-checkout-form-editor', 'wu_checkout_form', array(
			'form_id'       => $this->get_object()->get_id(),
			'checkout_form' => $this->get_object()->get_slug(),
			'register_page' => wu_get_registration_url(),
			'steps'         => $steps,
			'headers'       => array(
				'order' => __('Order', 'wp-ultimo'),
				'name'  => __('Label', 'wp-ultimo'),
				'type'  => __('Type', 'wp-ultimo'),
				'slug'  => __('Slug', 'wp-ultimo'),
				'move'  => '',
			),
		));

		wp_enqueue_script('wu-checkout-form-editor');

		wp_enqueue_script('wu-vue-sortable', '//cdn.jsdelivr.net/npm/sortablejs@1.8.4/Sortable.min.js', array(), wu_get_version());
		wp_enqueue_script('wu-vue-draggable', '//cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.20.0/vuedraggable.umd.min.js', array(), wu_get_version());

		wp_enqueue_style('wu-checkout-form-editor', wu_get_asset('checkout-editor.css', 'css'));

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
			'type'  => 'code-editor',
			'title' => __('Conversion Snippets', 'wp-ultimo'),
			'desc'  => __('Add custom snippets in HTML (with javascript support) to add conversion tracking pixels and such. This code is only run on the successful Thank You step.<br> Available placeholders are: %%MEMBERSHIP_DURATION%%, %%MEMBERSHIP_PLAN%%, %%ORDER_CURRENCY%%, %%ORDER_PRODUCTS%% and %%ORDER_AMOUNT%%', 'wp-ultimo'),
			'value' => $this->get_object()->get_conversion_snippets(),
			'lang'  => 'htmlmixed',
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
							'type'  => 'code-editor',
							'title' => __('Custom CSS', 'wp-ultimo'),
							'desc'  => __('Add custom CSS code to your checkout form. SCSS syntax is supported.', 'wp-ultimo'),
							'value' => $this->get_object()->get_custom_css(),
							'lang'  => 'css',
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
				'slug'             => array(
					'type'              => 'text',
					'title'             => __('Checkout Form Slug', 'wp-ultimo'),
					'desc'              => __('This is used to create shortcodes and more.', 'wp-ultimo'),
					'value'             => $this->get_object()->get_slug(),
					'wrapper_html_attr' => array(
						'v-cloak' => '1',
					),
					'html_attr'         => array(
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
			'title'  => __('Active', 'wp-ultimo'),
			'fields' => array(
				'active' => array(
					'type'  => 'toggle',
					'title' => __('Active', 'wp-ultimo'),
					'desc'  => __('Use this option to manually enable or disable this checkout form.', 'wp-ultimo'),
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
			'object_id'   => absint($this->get_object()->get_id()),
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
