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
use WP_Ultimo\Logger;

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

		add_action('wp_ajax_wu_form_display', array($this, 'display_form'));

		add_action('wp_ajax_wu_form_handler', array($this, 'handle_form'));

		add_action('wu_form_scripts', array($this, 'register_scripts'));

		add_action('wu_register_forms', array($this, 'register_model_delete_form'));

		add_action('admin_enqueue_scripts', 'add_wubox');

		add_action('admin_enqueue_scripts', 'wp_enqueue_editor');

		do_action('wu_register_forms');

	} // end init;

	/**
	 * Registers and enqueue the scripts and styles necessary for the ajax forms.
	 *
	 * @since 2.0.0
	 *
	 * @param array $form Registered form.
	 * @return void
	 */
	public function register_scripts($form) {

		wp_enqueue_script('wu-selectizer');

		wp_localize_script('wu-forms', 'wu_form', array(
			'form' => $form,
		));

		wp_enqueue_script('wu-forms');

	} // end register_scripts;

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

		do_action('admin_print_scripts'); // phpcs:ignore

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

		do_action('admin_print_scripts'); // phpcs:ignore

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
			'action' => 'wu_form_display',
			'form'   => $form_id,
			'width'  => '400',
			'height' => '360',
		));

		$url = admin_url('admin-ajax.php');

		return add_query_arg($atts, $url);

	} // end get_form_url;


	/**
	 * Register the confirmation modal form to delete a customer.
	 *
	 * @since 2.0.0
	 */
	public function register_model_delete_form() {

		wu_register_form('delete_modal', array(
			'render'  => array($this, 'render_model_delete_form'),
			'handler' => array($this, 'handle_model_delete_form'),
		));

	} // end register_model_delete_form;

	/**
	 * Renders the deletion confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_model_delete_form() {

		$model = wu_request('model');

		if ($model) {

			$object = call_user_func("wu_get_{$model}", wu_request('id'));

			if (!$object) {

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

		if ($model) {

			$object = call_user_func("wu_get_{$model}", wu_request('id'));

			if (!$object) {

				wp_send_json_error(new \WP_Error('not-found', __('Data not found.', 'wp-ultimo')));

			} // end if;

			do_action("wu_before_delete_{$model}_modal", $object);

			$saved = $object->delete();

			if (is_wp_error($saved)) {

				wp_send_json_error($saved);

			} // end if;

			do_action("wu_after_delete_{$model}_modal", $object);

			$plural_name = str_replace('_', '-', $model) . 's';

			$data_json_success = apply_filters("wu_data_json_success_delete_{$model}_modal", array(
				'redirect_url' => wu_network_admin_url("wp-ultimo-{$plural_name}", array('deleted' => 1))
			));

			wp_send_json_success($data_json_success);

		} else {

			wp_send_json_error(new \WP_Error('model-not-found', __('Something went wrong.', 'wp-ultimo')));

		} // end if;

	} // end handle_model_delete_form;

} // end class Form_Manager;
