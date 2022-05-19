<?php
/**
 * Base admin page class.
 *
 * Abstract class that makes it easy to create new admin pages.
 *
 * Most of WP Ultimo pages are implemented using this class, which means that the filters and hooks
 * listed below can be used to append content to all of our pages at once.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Abstract class that makes it easy to create new admin pages.
 */
abstract class Edit_Admin_Page extends Base_Admin_Page {

	/**
	 * Checks if we are adding a new object or if we are editing one
	 *
	 * @since 1.8.2
	 * @var boolean
	 */
	public $edit = false;

	/**
	 * The id/name/slug of the object being edited/created. e.g: plan
	 *
	 * @since 1.8.2
	 * @var string
	 */
	public $object_id;

	/**
	 * The object being edited.
	 *
	 * @since 1.8.2
	 * @var object
	 */
	public $object;

	/**
	 * Holds validations errors on edition.
	 *
	 * @since 2.0.0
	 * @var null|\WP_Error
	 */
	protected $errors;

	/**
	 * Returns the errors, if any.
	 *
	 * @since 2.0.0
	 * @return \WP_Error
	 */
	public function get_errors() {

		if ($this->errors === null) {

			$this->errors = new \WP_Error;

		} // end if;

		return $this->errors;

	} // end get_errors;

	/**
	 * Register additional hooks to page load such as the action links and the save processing.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function page_loaded() {

		/**
		 * Setups the object
		 */
		$this->object = $this->get_object();

		$this->edit = $this->object->exists();

		/**
		 * Deals with lock statuses.
		 */
		$this->add_lock_notices();

		if (wu_request('submit_button') === 'delete') {

			$this->process_delete();

		} elseif (wu_request('remove-lock')) {

			$this->remove_lock();

		} else {
			/*
			 * Process save, if necessary
			 */
			$this->process_save();

		} // end if;

	} // end page_loaded;

	/**
	 * Add some other necessary hooks.
	 *
	 * @return void
	 */
	public function hooks() {

		parent::hooks();

		add_filter('removable_query_args', array($this, 'removable_query_args'));

	} // end hooks;

	/**
	 * Adds the wu-new-model to the list of removable query args of WordPress.
	 *
	 * @since 2.0.0
	 *
	 * @param array $removable_query_args Existing list of removable query args.
	 * @return array
	 */
	public function removable_query_args($removable_query_args) {

		$removable_query_args[] = 'wu-new-model';

		return $removable_query_args;

	} // end removable_query_args;

	/**
	 * Displays lock notices, if necessary.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function add_lock_notices() {

		$locked = $this->get_object()->is_locked();

		if ($locked && $this->edit) {

			// translators: %s is the date, using the site format options
			$message = sprintf(__('This item is locked from editions.<br />This is probably due to a background action being performed (like a transfer between different accounts, for example). You can manually unlock it, but be careful. The lock should be released automatically in %s seconds.', 'wp-ultimo'), wu_get_next_queue_run() + 10);

			$actions = array(
				'preview' => array(
					'title' => __('Unlock', 'wp-ultimo'),
					'url'   => add_query_arg(array(
						'remove-lock'           => 1,
						'unlock_wpultimo_nonce' => wp_create_nonce(sprintf('unlocking_%s', $this->object_id)),
					)),
				),
			);

			WP_Ultimo()->notices->add($message, 'warning', 'network-admin', false, $actions);

		} // end if;

	} // end add_lock_notices;

	/**
	 * Remove the lock from the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function remove_lock() {

		$unlock_tag = "unlocking_{$this->object_id}";

		if (isset($_REQUEST['remove-lock'])) {

			check_admin_referer($unlock_tag, 'unlock_wpultimo_nonce');

			/**
			 * Allow plugin developers to add actions to the unlocking process.
			 *
			 * @since 1.8.2
			 */
			do_action("wu_unlock_{$this->object_id}");

			/**
			 * Unlocks and redirects.
			 */
			$this->get_object()->unlock();

			wp_redirect(remove_query_arg(array(
				'remove-lock',
				'unlock_wpultimo_nonce',
			)));

			exit;

		} // end if;

	} // end remove_lock;

	/**
	 * Handles saves, after verifying nonces and such. Should not be rewritten by child classes.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	final public function process_save() {

		$saving_tag = "saving_{$this->object_id}";

		if (isset($_REQUEST[$saving_tag])) {

			check_admin_referer($saving_tag, '_wpultimo_nonce');

			/**
			 * Allow plugin developers to add actions to the saving process
			 *
			 * @since 1.8.2
			 */
			do_action("wu_save_{$this->object_id}", $this);

			/**
			 * Calls the saving function
			 */
			$status = $this->handle_save();

			if ($status) {

				exit;

			} // end if;

		} // end if;

	} // end process_save;

	/**
	 * Handles delete, after verifying nonces and such. Should not be rewritten by child classes.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	final public function process_delete() {

		$deleting_tag = "deleting_{$this->object_id}";

		if (isset($_REQUEST[$deleting_tag])) {

			check_admin_referer($deleting_tag, 'delete_wpultimo_nonce');

			/**
			 * Allow plugin developers to add actions to the deleting process
			 *
			 * @since 1.8.2
			 */
			do_action("wu_delete_{$this->object_id}");

			/**
			 * Calls the deleting function
			 */
			$this->handle_delete();

		} // end if;

	} // end process_delete;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		$default_labels = array(
			'edit_label'          => __('Edit Object', 'wp-ultimo'),
			'add_new_label'       => __('Add New Object', 'wp-ultimo'),
			'updated_message'     => __('Object updated with success!', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Object Name', 'wp-ultimo'),
			'title_description'   => '',
			'save_button_label'   => __('Save', 'wp-ultimo'),
			'save_description'    => '',
			'delete_button_label' => __('Delete', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

		return apply_filters('wu_edit_admin_page_labels', $default_labels);

	} // end get_labels;

	/**
	 * Allow child classes to register scripts and styles that can be loaded on the output function, for example.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		/*
		 * Enqueue the base Dashboard Scripts
		 */
		wp_enqueue_script('dashboard');

		/*
		 * Adds Vue.
		 */
		wp_enqueue_script('wu-vue-apps');

		wp_enqueue_script('wu-fields');

		wp_enqueue_style('wp-color-picker');

		wp_enqueue_script('wu-selectizer');

	} // end register_scripts;

	/**
	 * Registers widgets to the edit page.
	 *
	 * This implementation register the default save widget.
	 * Child classes that wish to inherit that widget while registering other,
	 * can do such by adding a parent::register_widgets() to their own register_widgets() method.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_widgets() {

		$screen = get_current_screen();

		$this->add_info_widget('info', array(
			'title'    => __('Timestamps', 'wp-ultimo'),
			'position' => 'side-bottom',
		));

		if ($this->edit) {

			$this->add_delete_widget('delete', array());

		} // end if;

	} // end register_widgets;

	/**
	 * Adds a basic widget with info (and fields) to be shown.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Unique ID for the widget, since we can have more than one per page.
	 * @param array  $atts Array containing the attributes to be passed to the widget.
	 * @return void
	 */
	protected function add_info_widget($id, $atts = array()) {

		$created_key = 'date_created';

		if (method_exists($this->get_object(), 'get_date_registered')) {

			$created_key = 'date_registered';

		} // end if;

		$created_value = call_user_func(array($this->get_object(), "get_$created_key"));

		$atts['fields'][$created_key] = array(
			'title'         => __('Created at', 'wp-ultimo'),
			'type'          => 'text-display',
			'date'          => true,
			'display_value' => $this->edit ? $created_value : false,
			'value'         => $created_value,
			'placeholder'   => '2020-04-04 12:00:00',
			'html_attr'     => array(
				'wu-datepicker'   => 'true',
				'data-format'     => 'Y-m-d H:i:S',
				'data-allow-time' => 'true',
			),
		);

		$show_modified = wu_get_isset($atts, 'modified', true);

		if ($this->edit && $show_modified === true) {

			$atts['fields']['date_modified'] = array(
				'title'         => __('Last Modified at', 'wp-ultimo'),
				'type'          => 'text-display',
				'date'          => true,
				'display_value' => $this->edit ? $this->get_object()->get_date_modified() : __('No date', 'wp-ultimo'),
				'value'         => $this->get_object()->get_date_modified(),
				'placeholder'   => '2020-04-04 12:00:00',
				'html_attr'     => array(
					'wu-datepicker'   => 'true',
					'data-format'     => 'Y-m-d H:i:S',
					'data-allow-time' => 'true',
				),
			);

		} // end if;

		$this->add_fields_widget($id, $atts);

	} // end add_info_widget;

	/**
	 * Adds a basic widget to display list tables.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Unique ID for the widget, since we can have more than one per page.
	 * @param array  $atts Array containing the attributes to be passed to the widget.
	 * @return void
	 */
	protected function add_list_table_widget($id, $atts = array()) {

		$atts = wp_parse_args($atts, array(
			'widget_id'    => $id,
			'before'       => '',
			'after'        => '',
			'title'        => __('List Table', 'wp-ultimo'),
			'position'     => 'advanced',
			'screen'       => get_current_screen(),
			'page'         => $this,
			'labels'       => $this->get_labels(),
			'object'       => $this->get_object(),
			'edit'         => true,
			'table'        => false,
			'query_filter' => false,
		));

		$atts['table']->set_context('widget');

		$table_name = $atts['table']->get_table_id();

		if (is_callable($atts['query_filter'])) {

			add_filter("wu_{$table_name}_get_items", $atts['query_filter']);

		} // end if;

		add_filter('wu_events_list_table_get_columns', function($columns) {

			unset($columns['object_type']);

			unset($columns['code']);

			return $columns;

		});

		add_meta_box("wp-ultimo-list-table-{$id}", $atts['title'], function() use ($atts) {

			wp_enqueue_script('wu-ajax-list-table');

			wu_get_template('base/edit/widget-list-table', $atts);

		}, $atts['screen']->id, $atts['position'], null);

	} // end add_list_table_widget;

	/**
	 * Adds field widgets to edit pages with the same Form/Field APIs used elsewhere.
	 *
	 * @see Take a look at /inc/ui/form and inc/ui/field for reference.
	 * @since 2.0.0
	 *
	 * @param string $id ID of the widget.
	 * @param array  $atts Array of attributes to pass to the form.
	 * @return void
	 */
	protected function add_fields_widget($id, $atts = array()) {

		$atts = wp_parse_args($atts, array(
			'widget_id'             => $id,
			'before'                => '',
			'after'                 => '',
			'title'                 => __('Fields', 'wp-ultimo'),
			'position'              => 'side',
			'screen'                => get_current_screen(),
			'fields'                => array(),
			'html_attr'             => array(),
			'classes'               => '',
			'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
		));

		add_meta_box("wp-ultimo-{$id}-widget", $atts['title'], function() use ($atts) {

			if (wu_get_isset($atts['html_attr'], 'data-wu-app')) {

				$atts['fields']['loading'] = array(
					'type'              => 'note',
					'desc'              => sprintf('<div class="wu-block wu-text-center wu-blinking-animation wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold">%s</div>', __('Loading...', 'wp-ultimo')),
					'wrapper_html_attr' => array(
						'v-if' => 0,
					),
				);

			} // end if;

			/**
			 * Instantiate the form for the order details.
			 *
			 * @since 2.0.0
			 */
			$form = new \WP_Ultimo\UI\Form($atts['widget_id'], $atts['fields'], array(
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-widget-list wu-striped wu-m-0 wu--mt-2 wu--mb-3 wu--mx-3 ' . $atts['classes'],
				'field_wrapper_classes' => $atts['field_wrapper_classes'],
				'html_attr'             => $atts['html_attr'],
				'before'                => $atts['before'],
				'after'                 => $atts['after'],
			));

			$form->render();

		}, $atts['screen']->id, $atts['position'], null);

	} // end add_fields_widget;

	/**
	 * Adds field widgets to edit pages with the same Form/Field APIs used elsewhere.
	 *
	 * @see Take a look at /inc/ui/form and inc/ui/field for reference.
	 * @since 2.0.0
	 *
	 * @param string $id ID of the widget.
	 * @param array  $atts Array of attributes to pass to the form.
	 * @return void
	 */
	protected function add_tabs_widget($id, $atts = array()) {

		$atts = wp_parse_args($atts, array(
			'widget_id' => $id,
			'before'    => '',
			'after'     => '',
			'title'     => __('Tabs', 'wp-ultimo'),
			'position'  => 'advanced',
			'screen'    => get_current_screen(),
			'sections'  => array(),
			'html_attr' => array(),
		));

		$current_section = wu_request($id, current(array_keys($atts['sections'])));

		$atts['html_attr']['data-wu-app'] = $id;

		$atts['html_attr']['data-state'] = array(
			'section'     => $current_section,
			'display_all' => false,
		);

		add_meta_box("wp-ultimo-{$id}-widget", $atts['title'], function() use ($atts) {

			foreach ($atts['sections'] as $section_id => &$section) {

				$section = wp_parse_args($section, array(
					'form'                  => '',
					'before'                => '',
					'after'                 => '',
					'v-show'                => '1',
					'fields'                => array(),
					'html_attr'             => array(),
					'state'                 => array(),
					'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-p-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
				));

				/**
				 * Move state ont step up
				 */
				$atts['html_attr']['data-state'] = array_merge($atts['html_attr']['data-state'], $section['state']);

				$section['html_attr'] = array(
					'v-cloak' => 1,
					'v-show'  => "(section == '{$section_id}' || display_all) && " . $section['v-show'],
				);

				/**
				 * Adds a header field
				 */
				$section['fields'] = array_merge(array(
					$section_id => array(
						'title'             => $section['title'],
						'desc'              => $section['desc'],
						'type'              => 'header',
						'wrapper_html_attr' => array(
							'v-show' => 'display_all',
						),
					)
				), $section['fields']);

				/**
				 * Instantiate the form for the order details.
				 *
				 * @since 2.0.0
				 */
				$section['form'] = new \WP_Ultimo\UI\Form($section_id, $section['fields'], array(
					'views'                 => 'admin-pages/fields',
					'classes'               => 'wu-widget-list wu-striped wu-m-0 wu-border-solid wu-border-gray-300 wu-border-0 wu-border-b',
					'field_wrapper_classes' => $section['field_wrapper_classes'],
					'html_attr'             => $section['html_attr'],
					'before'                => $section['before'],
					'after'                 => $section['after'],
				));

			} // end foreach;

			wu_get_template('base/edit/widget-tabs', array(
				'sections'  => $atts['sections'],
				'html_attr' => $atts['html_attr'],
				'before'    => $atts['before'],
				'after'     => $atts['after'],
			));

		}, $atts['screen']->id, $atts['position'], null);

	} // end add_tabs_widget;

	/**
	 * Adds a generic widget to the admin page.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id ID of the widget.
	 * @param array  $atts Widget parameters.
	 * @return void
	 */
	protected function add_widget($id, $atts = array()) {

		$atts = wp_parse_args($atts, array(
			'widget_id' => $id,
			'before'    => '',
			'after'     => '',
			'title'     => __('Fields', 'wp-ultimo'),
			'screen'    => get_current_screen(),
			'position'  => 'side',
			'display'   => '__return_empty_string',
		));

		add_meta_box("wp-ultimo-{$id}-widget", $atts['title'], $atts['display'], $atts['screen']->id, $atts['position'], null);

	} // end add_widget;

	/**
	 * Adds a basic save widget.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Unique ID for the widget, since we can have more than one per page.
	 * @param array  $atts Array containing the attributes to be passed to the widget.
	 * @return void
	 */
	protected function add_save_widget($id, $atts = array()) {

		$labels = $this->get_labels();

		$atts['title'] = __('Save', 'wp-ultimo');

		/**
		 * Adds Submit Button
		 */
		$atts['fields']['submit_save'] = array(
			'type'              => 'submit',
			'title'             => $labels['save_button_label'],
			'placeholder'       => $labels['save_button_label'],
			'value'             => 'save',
			'classes'           => 'button button-primary wu-w-full',
			'html_attr'         => array(),
			'wrapper_html_attr' => array(),
		);

		if (isset($atts['html_attr']['data-wu-app'])) {

			$atts['fields']['submit_save']['wrapper_html_attr']['v-cloak'] = 1;

		} // end if;

		if ($this->get_object() && $this->edit && $this->get_object()->is_locked()) {

			$atts['fields']['submit_save']['title']                 = __('Locked', 'wp-ultimo');
			$atts['fields']['submit_save']['value']                 = 'none';
			$atts['fields']['submit_save']['html_attr']['disabled'] = 'disabled';

		} // end if;

		$this->add_fields_widget('save', $atts);

	} // end add_save_widget;

	/**
	 * Adds a basic delete widget.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id Unique ID for the widget, since we can have more than one per page.
	 * @param array  $atts Array containing the attributes to be passed to the widget.
	 * @return void
	 */
	protected function add_delete_widget($id, $atts = array()) {

		$labels = $this->get_labels();

		$atts_default = array(
			'title'    => __('Delete', 'wp-ultimo'),
			'position' => 'side-bottom',
		);
		$atts         = array_merge($atts_default, $atts);

		/**
		 * Adds Note
		 */
		$atts['fields']['note'] = array(
			'type' => 'note',
			'desc' => $labels['delete_description'],
		);

		/**
		 * Adds Submit Button
		 */
		$default_delete_field_settings = array(
			'type'            => 'link',
			'title'           => $labels['delete_button_label'],
			'display_value'   => $labels['delete_button_label'],
			'placeholder'     => $labels['delete_button_label'],
			'value'           => 'delete',
			'classes'         => 'button wubox wu-w-full wu-text-center',
			'wrapper_classes' => 'wu-bg-gray-100',
			'html_attr'       => array(
				'title' => $labels['delete_button_label'],
				'href'  => wu_get_form_url(
					'delete_modal',
					array(
						'id'    => $this->get_object()->get_id(),
						'model' => $this->get_object()->model
					)
				),
			),
			'title'           => ''
		);

		$custom_delete_field_settings = wu_get_isset($atts['fields'], 'delete', array());

		$atts['fields']['delete'] = array_merge($default_delete_field_settings, $custom_delete_field_settings);

		$this->add_fields_widget('delete', $atts);

	}  // end add_delete_widget;

	/**
	 * Displays the contents of the edit page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output() {
		/*
		 * Renders the base edit page layout, with the columns and everything else =)
		 */
		wu_get_template('base/edit', array(
			'screen' => get_current_screen(),
			'page'   => $this,
			'labels' => $this->get_labels(),
			'object' => $this->get_object(),
		));

	} // end output;

	/**
	 * Wether or not this pages should have a title field.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_title() {

		return false;

	} // end has_title;

	/**
	 * Wether or not this pages should have an editor field.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function has_editor() {

		return false;

	} // end has_editor;

	/**
	 * Should return the object being edited, or false.
	 *
	 * Child classes need to implement this method, returning an object to be edited,
	 * such as a WP_Ultimo\Model, or false, in case this is a 'Add New' page.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Models\Base_Model
	 */
	abstract public function get_object(); // end get_object;

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function handle_save() {

		$object = $this->get_object();

		/*
		 * Active fix
		 */
		$_POST['active'] = (bool) wu_request('active', false);

		$object->attributes($_POST);

		if (method_exists($object, 'handle_limitations')) {

			$object->handle_limitations($_POST);

		} // end if;

		$save = $object->save();

		if (is_wp_error($save)) {

			$errors = implode('<br>', $save->get_error_messages());

			WP_Ultimo()->notices->add($errors, 'error', 'network-admin');

			return;

		} else {

			$array_params = array(
				'updated' => 1,
			);

			if ($this->edit === false) {

				$array_params['id'] = $object->get_id();

				$array_params['wu-new-model'] = true;

			} // end if;

			$url = add_query_arg($array_params);

			wp_redirect($url);

			return true;

		} // end if;

		return false;

	} // end handle_save;

	/**
	 * Should implement the processes necessary to delete  the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_delete() {

		$object = $this->get_object();

		$saved = $object->delete();

		if (is_wp_error($saved)) {

			$errors = implode('<br>', $saved->get_error_messages());

			WP_Ultimo()->notices->add($errors, 'error', 'network-admin');

			return;
		} // end if;

		$url = str_replace('_', '-', $object->model);
		$url = wu_network_admin_url("wp-ultimo-{$url}s");

		wp_redirect($url);

		exit;

	} // end handle_delete;

} // end class Edit_Admin_Page;
