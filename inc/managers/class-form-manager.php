<?php
/**
 * Gateway Manager
 *
 * Manages the registering and activation of gateways.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Gateway
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles the ajax form registering, rendering, and permissions checking.
 *
 * @since 2.0.0
 */
class Form_Manager extends Base_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the registered forms.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $registered_forms = array();

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('wu_ajax_wu_form_display', array($this, 'display_form'));

		add_action('wu_ajax_wu_form_handler', array($this, 'handle_form'));

		add_action('wu_register_forms', array($this, 'register_action_forms'));

		add_action('wu_page_load', 'add_wubox');

		do_action('wu_register_forms');

	} // end init;

	/**
	 * Displays the form unavailable message.
	 *
	 * This is returned when the form doesn't exist, or the
	 * logged user doesn't have the required permissions to see the form.
	 *
	 * @since 2.0.0
	 * @param \WP_Error|false $error Error message, if applicable.
	 * @return void
	 */
	public function display_form_unavailable($error = false) {

		$message = __('Form not available', 'wp-ultimo');

		if (is_wp_error($error)) {

			$message = $error->get_error_message();

		} // end if;

		echo sprintf('
      <div class="wu-modal-form wu-h-full wu-flex wu-items-center wu-justify-center wu-bg-gray-200 wu-m-0 wu-mt-0 wu--mb-3">
        <div>
          <span class="dashicons dashicons-warning wu-h-8 wu-w-8 wu-mx-auto wu-text-center wu-text-4xl wu-block"></span>
          <span class="wu-block wu-text-sm">%s</span>
        </div>
      </div>
    ', $message);

		do_action('wu_form_scripts', false);

		die;

	} // end display_form_unavailable;

	/**
	 * Renders a registered form, when requested.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_form() {

		$this->security_checks();

		$form = $this->get_form(wu_request('form'));

		echo sprintf("<form class='wu_form wu-styling' id='%s' action='%s' method='post'>",
		$form['id'],
		$this->get_form_url($form['id'], array(
			'action' => 'wu_form_handler',
		)));

		echo sprintf('
		<div v-cloak data-wu-app="%s" data-state="%s">
			<ul class="wu-p-4 wu-bg-red-200 wu-m-0 wu-list-none wu-p-0" v-if="errors.length">
				<li class="wu-m-0 wu-p-0" v-for="error in errors">{{ error.message }}</li>
			</ul>
		</div>', $form['id'] . '_errors', htmlspecialchars(json_encode(array('errors' => array()))));

		call_user_func($form['render']);

		echo '<input type="hidden" name="action" value="wu_form_handler">';

		wp_nonce_field('wu_form_' . $form['id']);

		echo '</form>';

		do_action('wu_form_scripts', $form);

		exit;

	} // end display_form;

	/**
	 * Handles the submission of a registered form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_form() {

		$this->security_checks();

		$form = $this->get_form(wu_request('form'));

		if (!wp_verify_nonce(wu_request('_wpnonce'), 'wu_form_' . $form['id'])) {

			wp_send_json_error();

		} // end if;

		/**
		 * The handler is supposed to send a wp_json message back.
		 * However, if it returns a WP_Error object, we know
		 * something went wrong and that we should display the error message.
		 */
		$check = call_user_func($form['handler']);

		if (is_wp_error($check)) {

			$this->display_form_unavailable($check);

		} // end if;

		exit;

	} // end handle_form;

	/**
	 * Checks that the form exists and that the user has permission to see it.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function security_checks() {
		/*
		 * We only want ajax requests.
		 */
		if ((empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')) {

			wp_die(0);

		} // end if;

		$form = $this->get_form(wu_request('form'));

		if (!$form) {

			return $this->display_form_unavailable();

		} // end if;

		if (!current_user_can($form['capability'])) {

			return $this->display_form_unavailable();

		} // end if;

	}  // end security_checks;

	/**
	 * Returns a list of all the registered gateways.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_registered_forms() {

		return $this->registered_forms;

	} // end get_registered_forms;

	/**
	 * Checks if a form is already registered.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id The id of the form.
	 * @return boolean
	 */
	public function is_form_registered($id) {

		return is_array($this->registered_forms) && isset($this->registered_forms[$id]);

	} // end is_form_registered;

	/**
	 * Returns a registered form.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id The id of the form to return.
	 * @return array
	 */
	public function get_form($id) {

		return $this->is_form_registered($id) ? $this->registered_forms[$id] : false;

	} // end get_form;

	/**
	 * Registers a new Ajax Form.
	 *
	 * Ajax forms are forms that get loaded via an ajax call using thickbox (or rather our fork).
	 * This is useful for displaying inline edit forms that support Vue and our
	 * Form/Fields API.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Form id.
	 * @param array  $atts Form attributes, check wp_parse_atts call below.
	 * @return void
	 */
	public function register_form($id, $atts = array()) {

		$atts = wp_parse_args($atts, array(
			'id'         => $id,
			'form'       => '',
			'capability' => 'manage_network',
			'handler'    => '__return_false',
			'render'     => '__return_empty_string',
		));

		// Checks if gateway was already added
		if ($this->is_form_registered($id)) {

			return;

		} // end if;

		$this->registered_forms[$id] = $atts;

		return true;

	}  // end register_form;

	/**
	 * Returns the ajax URL for a given form.
	 *
	 * @since 2.0.0
	 *
	 * @param string $form_id The id of the form to return.
	 * @param array  $atts List of parameters, check wp_parse_args below.
	 * @return string
	 */
	public function get_form_url($form_id, $atts = array()) {

		$atts = wp_parse_args($atts, array(
			'form'   => $form_id,
			'action' => 'wu_form_display',
			'width'  => '400',
			'height' => '360',
		));

		return add_query_arg($atts, wu_ajax_url('init'));

	} // end get_form_url;

	/**
	 * Register the confirmation modal form to delete a customer.
	 *
	 * @since 2.0.0
	 */
	public function register_action_forms() {

		wu_register_form('delete_modal', array(
			'render'  => array($this, 'render_model_delete_form'),
			'handler' => array($this, 'handle_model_delete_form'),
		));

		wu_register_form('bulk_actions', array(
			'render'  => array($this, 'render_bulk_action_form'),
			'handler' => array($this, 'handle_bulk_action_form'),
		));

		add_action('wu_handle_bulk_action_form', array($this, 'default_bulk_action_handler'), 100, 3);

	} // end register_action_forms;

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_model_delete_form() {

		$model = wu_request('model');

		$id = wu_request('id');

		$meta_key = false;

		if ($model) {
			/*
			 * Handle metadata elements passed as model
			 */
			if (strpos($model, '_meta_') !== false) {

				$elements = explode('_meta_', $model);

				$model = $elements[0];

				$meta_key = $elements[1];

			} // end if;

			try {

				$object = call_user_func("wu_get_{$model}", $id);

			} catch (\Throwable $th) {

				// No need to do anything, but cool to stop fatal errors.

			} // end try;

			$object = apply_filters("wu_delete_form_get_object_{$model}", $object, $id, $model);

			if (!$object) {

				$this->display_form_unavailable(new \WP_Error('not-found', __('Object not found.', 'wp-ultimo')));

				return;

			} // end if;

			$fields = apply_filters(
				"wu_form_fields_delete_{$model}_modal",
				array(
					'confirm'       => array(
						'type'      => 'toggle',
						'title'     => __('Confirm Deletion', 'wp-ultimo'),
						'desc'      => __('This action can not be undone.', 'wp-ultimo'),
						'html_attr' => array(
							'v-model' => 'confirmed',
						),
					),
					'submit_button' => array(
						'type'            => 'submit',
						'title'           => __('Delete', 'wp-ultimo'),
						'placeholder'     => __('Delete', 'wp-ultimo'),
						'value'           => 'save',
						'classes'         => 'button button-primary wu-w-full',
						'wrapper_classes' => 'wu-items-end',
						'html_attr'       => array(
							'v-bind:disabled' => '!confirmed',
						),
					),
					'id'            => array(
						'type'  => 'hidden',
						'value' => $object->get_id(),
					),
					'meta_key'      => array(
						'type'  => 'hidden',
						'value' => $meta_key,
					),
					'redirect_to'   => array(
						'type'  => 'hidden',
						'value' => wu_request('redirect_to'),
					),
					'model'         => array(
						'type'  => 'hidden',
						'value' => $model,
					),
				),
				$object
			);

			$form_attributes = apply_filters("wu_form_attributes_delete_{$model}_modal", array(
				'title'                 => 'Delete',
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				'html_attr'             => array(
					'data-wu-app' => 'true',
					'data-state'  => json_encode(array(
						'confirmed' => false,
					)),
				),
			));

			$form = new \WP_Ultimo\UI\Form('total-actions', $fields, $form_attributes);

			do_action("wu_before_render_delete_{$model}_modal", $form);

			$form->render();

		} // end if;

	} // end render_model_delete_form;

	/**
	 * Handles the deletion of customer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_model_delete_form() {

		global $wpdb;

		$model = wu_request('model');

		$id = wu_request('id');

		$meta_key = wu_request('meta_key');

		$redirect_to = wu_request('redirect_to', wp_get_referer());

		$plural_name = str_replace('_', '-', $model) . 's';

		if ($model) {
			/*
			 * Handle meta key deletion
			 */
			if ($meta_key) {

				$status = delete_metadata('wu_membership', wu_request('id'), 'pending_site');

				$data_json_success = array(
					'redirect_url' => add_query_arg('deleted', 1, $redirect_to),
				);

				wp_send_json_success($data_json_success);

				exit;

			} // end if;

			try {

				$object = call_user_func("wu_get_{$model}", $id);

			} catch (\Throwable $th) {

				// No need to do anything, but cool to stop fatal errors.

			} // end try;

			$object = apply_filters("wu_delete_form_get_object_{$model}", $object, $id, $model);

			if (!$object) {

				wp_send_json_error(new \WP_Error('not-found', __('Object not found.', 'wp-ultimo')));

			} // end if;

			/*
			 * Handle objects (default state)
			 */
			do_action("wu_before_delete_{$model}_modal", $object);

			$saved = $object->delete();

			if (is_wp_error($saved)) {

				wp_send_json_error($saved);

			} // end if;

			do_action("wu_after_delete_{$model}_modal", $object);

			$data_json_success = apply_filters("wu_data_json_success_delete_{$model}_modal", array(
				'redirect_url' => wu_network_admin_url("wp-ultimo-{$plural_name}", array('deleted' => 1))
			));

			wp_send_json_success($data_json_success);

		} else {

			wp_send_json_error(new \WP_Error('model-not-found', __('Something went wrong.', 'wp-ultimo')));

		} // end if;

	} // end handle_model_delete_form;

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_bulk_action_form() {

		$action = wu_request('bulk_action');

		$model = wu_request('model');

		$fields = apply_filters("wu_bulk_actions_{$model}_{$action}", array(
			'confirm'       => array(
				'type'      => 'toggle',
				'title'     => __('Confirm Action', 'wp-ultimo'),
				'desc'      => __('Review this action carefully.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_button' => array(
				'type'            => 'submit',
				'title'           => wu_slug_to_name($action),
				'placeholder'     => wu_slug_to_name($action),
				'value'           => 'save',
				'classes'         => 'button button-primary wu-w-full',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
			'model'         => array(
				'type'  => 'hidden',
				'value' => $model,
			),
			'bulk_action'   => array(
				'type'  => 'hidden',
				'value' => wu_request('bulk_action'),
			),
			'ids'           => array(
				'type'  => 'hidden',
				'value' => implode(',', wu_request('bulk-delete', '')),
			),
		));

		$form_attributes = apply_filters("wu_bulk_actions_{$action}_form", array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'true',
				'data-state'  => json_encode(array(
					'confirmed' => false,
				)),
			),
		));

		$form = new \WP_Ultimo\UI\Form('total-actions', $fields, $form_attributes);

		$form->render();

	} // end render_bulk_action_form;

	/**
	 * Handles the deletion of customer.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_bulk_action_form() {

		global $wpdb;

		$action = wu_request('bulk_action');

		$model = wu_request('model');

		$ids = explode(',', wu_request('ids', ''));

		do_action("wu_handle_bulk_action_form_{$model}_{$action}", $action, $model, $ids);

		do_action('wu_handle_bulk_action_form', $action, $model, $ids);

	} // end handle_bulk_action_form;

	/**
	 * Default handler for bulk actions.
	 *
	 * @since 2.0.0
	 *
	 * @param string $action The action.
	 * @param string $model The model.
	 * @param array  $ids The ids list.
	 * @return void
	 */
	public function default_bulk_action_handler($action, $model, $ids) {

		$status = \WP_Ultimo\List_Tables\Base_List_Table::process_bulk_action();

		if (is_wp_error($status)) {

			wp_send_json_error($status);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => add_query_arg($action, count($ids), wu_get_current_url()),
		));

	} // end default_bulk_action_handler;

} // end class Form_Manager;
