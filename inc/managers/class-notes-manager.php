<?php
/**
 * Notes Manager
 *
 * Handles processes related to notes.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Notes
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use \WP_Ultimo\Managers\Base_Manager;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to notes.
 *
 * @since 2.0.0
 */
class Notes_Manager extends Base_Manager {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'notes';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Notes';

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		add_action('plugins_loaded', array($this, 'register_forms'));

		add_filter('wu_membership_options_sections', array($this, 'add_notes_options_section'), 10, 2);

		add_filter('wu_payments_options_sections', array($this, 'add_notes_options_section'), 10, 2);

		add_filter('wu_customer_options_sections', array($this, 'add_notes_options_section'), 10, 2);

		add_filter('wu_site_options_sections', array($this, 'add_notes_options_section'), 10, 2);

	} // end init;

 	/**
	 * Register ajax forms that we use for object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_forms() {
		/*
		 * Add note
		 */
		wu_register_form('add_note', array(
			'render'     => array($this, 'render_add_note_modal'),
			'handler'    => array($this, 'handle_add_note_modal'),
			'capability' => 'edit_notes',
		));

		/*
		 * Clear notes
		 */
		wu_register_form('clear_notes', array(
			'render'     => array($this, 'render_clear_notes_modal'),
			'handler'    => array($this, 'handle_clear_notes_modal'),
			'capability' => 'delete_notes',
		));

		/*
		 * Clear notes
		 */
		wu_register_form('delete_note', array(
			'render'     => array($this, 'render_delete_note_modal'),
			'handler'    => array($this, 'handle_delete_note_modal'),
			'capability' => 'delete_notes',
		));

	} // end register_forms;

    /**
	 * Add all domain mapping settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $sections Array sections.
	 * @param object $object   The object.
	 *
	 * @return array
	 */
	public function add_notes_options_section($sections, $object) {

		if (!current_user_can('read_notes') && !current_user_can('edit_notes')) {

			return $sections;

		} // end if;

		$fields = array();

		$fields['notes_panel'] = array(
			'type'              => 'html',
			'wrapper_classes'   => 'wu-m-0 wu-p-2 wu-notes-wrapper',
			'wrapper_html_attr' => array(
				'style' => sprintf('min-height: 500px; background: url("%s");', wu_get_asset('pattern-wp-ultimo.png')),
			),
			'content'           => wu_get_template_contents('base/edit/display-notes', array(
				'notes' => $object->get_notes(),
				'model' => $object->model,
			)),
		);

		$fields_buttons = array();

		if (current_user_can('delete_notes')) {

			$fields_buttons['button_clear_notes'] = array(
				'type'            => 'link',
				'display_value'   => __('Clear Notes', 'wp-ultimo'),
				'wrapper_classes' => 'wu-mb-0',
				'classes'         => 'button wubox',
				'html_attr'       => array(
					'href'  => wu_get_form_url('clear_notes', array(
						'object_id' => $object->get_id(),
						'model'     => $object->model,
					)),
					'title' => __('Clear Notes', 'wp-ultimo'),
				),
			);

		} // end if;

		if (current_user_can('edit_notes')) {

			$fields_buttons['button_add_note'] = array(
				'type'            => 'link',
				'display_value'   => __('Add new Note', 'wp-ultimo'),
				'wrapper_classes' => 'wu-mb-0',
				'classes'         => 'button button-primary wubox wu-absolute wu-right-5',
				'html_attr'       => array(
					'href'  => wu_get_form_url('add_note', array(
						'object_id' => $object->get_id(),
						'model'     => $object->model,
						'height'    => 306,
					)),
					'title' => __('Add new Note', 'wp-ultimo'),
				),
			);

		} // end if;

		$fields['buttons'] = array(
			'type'            => 'group',
			'wrapper_classes' => 'wu-bg-white',
			'fields'          => $fields_buttons,
		);

		$sections['notes'] = array(
			'title'  => __('Notes', 'wp-ultimo'),
			'desc'   => __('Add notes to this model.', 'wp-ultimo'),
			'icon'   => 'dashicons-wu-text-document',
			'order'  => 1001,
			'fields' => $fields,
		);

		return $sections;

	} // end add_notes_options_section;

	/**
	 * Renders the notes form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_add_note_modal() {

		$fields = array(
			'content'         => array(
				'id'        => 'content',
				'type'      => 'wp-editor',
				'title'     => __('Note Content', 'wp-ultimo'),
				'desc'      => __('Basic formatting is supported.', 'wp-ultimo'),
				'settings'  => array(
					'tinymce' => array(
						'toolbar1' => 'bold,italic,strikethrough,link,unlink,undo,redo,pastetext',
					),
				),
				'html_attr' => array(
					'v-model' => 'content',
				),
			),
			'submit_add_note' => array(
				'type'            => 'submit',
				'title'           => __('Add Note', 'wp-ultimo'),
				'placeholder'     => __('Add Note', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
			),
			'object_id'       => array(
				'type'  => 'hidden',
				'value' => wu_request('object_id'),
			),
			'model'           => array(
				'type'  => 'hidden',
				'value' => wu_request('model'),
			),
		);

		$fields = apply_filters('wu_notes_options_section_fields', $fields);

		$form = new \WP_Ultimo\UI\Form('add_note', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'add_note',
				'data-state'  => wu_convert_to_state(array(
					'content' => '',
				)),
			),
		));

		$form->render();

	} // end render_add_note_modal;

	/**
	 * Handles the notes form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_add_note_modal() {

		$model         = wu_request('model');
		$function_name = "wu_get_{$model}";
		$object        = $function_name(wu_request('object_id'));

		$status = $object->add_note(array(
			'text'      => wu_remove_empty_p(wu_request('content')),
			'author_id' => get_current_user_id(),
			'note_id'   => uniqid(),
		));

		if (is_wp_error($status)) {

			wp_send_json_error($status);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url("wp-ultimo-edit-{$model}", array(
				'id'      => $object->get_id(),
				'updated' => 1,
				'options' => 'notes',
			)),
		));

	} // end handle_add_note_modal;

	/**
	 * Renders the clear notes confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_clear_notes_modal() {

		$fields = array(
			'confirm_clear_notes' => array(
				'type'      => 'toggle',
				'title'     => __('Confirm clear all notes?', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_clear_notes'  => array(
				'type'            => 'submit',
				'title'           => __('Clear Notes', 'wp-ultimo'),
				'placeholder'     => __('Clear Notes', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
			'object_id'           => array(
				'type'  => 'hidden',
				'value' => wu_request('object_id'),
			),
			'model'               => array(
				'type'  => 'hidden',
				'value' => wu_request('model'),
			),
		);

		$form = new \WP_Ultimo\UI\Form('clear_notes', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'clear_notes',
				'data-state'  => wu_convert_to_state(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_clear_notes_modal;

	/**
	 * Handles the clear notes modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_clear_notes_modal() {

		$model         = wu_request('model');
		$function_name = "wu_get_{$model}";
		$object        = $function_name(wu_request('object_id'));

		if (!$object) {

			return;

		} // end if;

		$status = $object->clear_notes();

		if (is_wp_error($status)) {

			wp_send_json_error($status);

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url("wp-ultimo-edit-{$model}", array(
				'id'      => $object->get_id(),
				'deleted' => 1,
				'options' => 'notes',
			)),
		));

	} // end handle_clear_notes_modal;

	/**
	 * Renders the delete note confirmation form.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_delete_note_modal() {

		$fields = array(
			'confirm_delete_note' => array(
				'type'      => 'toggle',
				'title'     => __('Confirm clear the note?', 'wp-ultimo'),
				'desc'      => __('This action can not be undone.', 'wp-ultimo'),
				'html_attr' => array(
					'v-model' => 'confirmed',
				),
			),
			'submit_delete_note'  => array(
				'type'            => 'submit',
				'title'           => __('Clear Note', 'wp-ultimo'),
				'placeholder'     => __('Clear Note', 'wp-ultimo'),
				'value'           => 'save',
				'classes'         => 'wu-w-full button button-primary',
				'wrapper_classes' => 'wu-items-end',
				'html_attr'       => array(
					'v-bind:disabled' => '!confirmed',
				),
			),
			'object_id'           => array(
				'type'  => 'hidden',
				'value' => wu_request('object_id'),
			),
			'model'               => array(
				'type'  => 'hidden',
				'value' => wu_request('model'),
			),
			'note_id'             => array(
				'type'  => 'hidden',
				'value' => wu_request('note_id'),
			),
		);

		$form = new \WP_Ultimo\UI\Form('delete_note', $fields, array(
			'views'                 => 'admin-pages/fields',
			'classes'               => 'wu-modal-form wu-widget-list wu-striped wu-m-0 wu-mt-0',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			'html_attr'             => array(
				'data-wu-app' => 'delete_note',
				'data-state'  => wu_convert_to_state(array(
					'confirmed' => false,
				)),
			),
		));

		$form->render();

	} // end render_delete_note_modal;

	/**
	 * Handles the delete note modal.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_delete_note_modal() {

		$model         = wu_request('model');
		$function_name = "wu_get_{$model}";
		$object        = $function_name(wu_request('object_id'));
		$note_id       = wu_request('note_id');

		if (!$object) {

			return;

		} // end if;

		$status = $object->delete_note($note_id);

		if (is_wp_error($status) || $status === false) {

			wp_send_json_error(new \WP_Error('not-found', __('Note not found', 'wp-ultimo')));

		} // end if;

		wp_send_json_success(array(
			'redirect_url' => wu_network_admin_url("wp-ultimo-edit-{$model}", array(
				'id'      => $object->get_id(),
				'deleted' => 1,
				'options' => 'notes',
			)),
		));

	} // end handle_delete_note_modal;

} // end class Notes_Manager;
