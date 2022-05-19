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
abstract class Wizard_Admin_Page extends Base_Admin_Page {

	/**
	 * Should we hide admin notices on this page?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hide_admin_notices = true;

	/**
	 * Should we force the admin menu into a folded state?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $fold_menu = true;

	/**
	 * Holds the section slug for the URLs.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $section_slug = 'step';

	/**
	 * Defines if the step links on the side are clickable or not.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $clickable_navigation = false;

	/**
	 * Defined the id to be used on the main form element.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $form_id = '';

	/**
	 * Holds the active section for the wizard.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $current_section;

	/**
	 * Register additional hooks to page load such as the action links and the save processing.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function page_loaded() {
		/*
		 * Load sections to memory.
		 */
		$sections = $this->get_sections();

		/*
		 * Sets current section for future reference.
		 */
		$this->current_section = $sections[$this->get_current_section()];

		/*
		 * Process save, if necessary
		 */
		$this->process_save();

	} // end page_loaded;

	/**
	 * Handles saves, after verifying nonces and such. Should not be rewritten by child classes.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	final public function process_save() {

		$saving_tag = sprintf('saving_%s', $this->get_current_section());

		if (isset($_REQUEST[$saving_tag])) {

			check_admin_referer($saving_tag, '_wpultimo_nonce');

			$handler = isset($this->current_section['handler']) ? $this->current_section['handler'] : array($this, 'default_handler');

			/*
			 * Calls the saving function
			 */
			call_user_func($handler);

		} // end if;

	} // end process_save;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return array(
			'edit_label'        => __('Edit Object', 'wp-ultimo'),
			'add_new_label'     => __('Add New Object', 'wp-ultimo'),
			'updated_message'   => __('Object updated with success!', 'wp-ultimo'),
			'title_placeholder' => __('Enter Object Name', 'wp-ultimo'),
			'title_description' => '',
			'save_button_label' => __('Save', 'wp-ultimo'),
			'save_description'  => '',
		);

	} // end get_labels;

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

		if (wu_get_isset($this->current_section, 'separator')) {

			return;

		} // end if;

		add_meta_box('wp-ultimo-wizard-body', wu_get_isset($this->current_section, 'title', __('Section', 'wp-ultimo')), array($this, 'output_default_widget_body'), $screen->id, 'normal', null);

	} // end register_widgets;

	/**
	 * Outputs the markup for the default Save widget.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function output_default_widget_body() {

		echo '<div class="wu-p-4">';

		$view = isset($this->current_section['view']) ? $this->current_section['view'] : array($this, 'default_view');

			/*
			 * Calls the view function.
			 */
		call_user_func($view);

		echo '</div>';

	} // end output_default_widget_body;

	/**
	 * Returns the logo to be used on the wizard.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return '';

	} // end get_logo;

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
		wu_get_template('base/wizard', array(
			'screen'               => get_current_screen(),
			'page'                 => $this,
			'logo'                 => $this->get_logo(),
			'labels'               => $this->get_labels(),
			'sections'             => $this->get_sections(),
			'current_section'      => $this->get_current_section(),
			'classes'              => 'wu-w-full wu-mx-auto sm:wu-w-11/12 xl:wu-w-8/12 wu-mt-8 sm:wu-max-w-screen-lg',
			'clickable_navigation' => $this->clickable_navigation,
			'form_id'              => $this->form_id,
		));

	} // end output;

	/**
	 * Returns the first section of the signup process
	 *
	 * @return string
	 */
	public function get_first_section() {

		$keys = array_keys($this->get_sections());

		if (isset($keys[1])) {

			return $keys[1];

		} else {

			return false;

		} // end if;

	} // end get_first_section;

	/**
	 * Get the current section
	 *
	 * @return string
	 */
	public function get_current_section() {

		$sections = $this->get_sections();

		$sections = array_filter($sections, function($item) {

			return wu_get_isset($item, 'addon') === false;

		});

		$current_section = isset($_GET[$this->section_slug]) ? sanitize_key($_GET[$this->section_slug]) : current(array_keys($sections));

		return $current_section;

	} // end get_current_section;

	/**
	 * Returns the page link for the current section.
	 *
	 * @since 2.0.0
	 *
	 * @param string $section Slug of the section. e.g. general.
	 * @return string
	 */
	public function get_section_link($section) {

		return add_query_arg($this->section_slug, $section);

	} // end get_section_link;

	/**
	 * Returns the link to the next section on the wizard.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_next_section_link() {

		$sections = $this->get_sections();

		$current_section = $this->get_current_section();

		$keys = array_keys($sections);

		return add_query_arg($this->section_slug, $keys[array_search($current_section, array_keys($sections), true) + 1]);

	} // end get_next_section_link;

	/**
	 * Returns the link to the previous section on the wizard.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_prev_section_link() {

		$sections = $this->get_sections();

		$current_section = $this->get_current_section();

		$keys = array_keys($sections);

		return add_query_arg($this->section_slug, $keys[array_search($current_section, array_keys($sections), true) - 1]);

	} // end get_prev_section_link;

	/**
	 * Default handler for step submission. Simply redirects to the next step.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function default_handler() {

		wp_redirect($this->get_next_section_link());

		exit;

	} // end default_handler;

	/**
	 * Default method for views.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function default_view() {

		$section = wp_parse_args($this->current_section, array(
			'title'       => '',
			'description' => '',
			'content'     => '',
			'fields'      => array(),
			'next_label'  => __('Continue &rarr;', 'wp-ultimo'),
			'back_label'  => __('&larr; Go Back', 'wp-ultimo'),
			'skip_label'  => __('Skip this Step', 'wp-ultimo'),
			'back'        => false,
			'skip'        => false,
			'next'        => true,
		));

		/*
		 * Check if the section has fields
		 */
		if (!empty($section['fields'])) {

			if (is_callable($section['fields'])) {

				$section['fields'] = call_user_func($section['fields']);

			} // end if;

			$form = new \WP_Ultimo\UI\Form($this->get_current_section(), $section['fields'], array(
				'views'                 => 'admin-pages/fields',
				'classes'               => 'wu-widget-list wu-striped wu-m-0 wu-mt-2 wu--mb-6 wu--mx-6',
				'field_wrapper_classes' => 'wu-w-full wu-box-border wu-items-center wu-flex wu-justify-between wu-px-6 wu-py-4 wu-m-0 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300 wu-border-solid',
			));

			ob_start();

			$form->render();

			$section['content'] = ob_get_clean();

		} // end if;

		wu_get_template('wizards/setup/default', array_merge($section, array(
			'page' => $this,
		)));

	} // end default_view;

	/**
	 * Renders the default submit box with action buttons at the bottom of the wizard.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_submit_box() {

		wu_get_template('base/wizard/submit-box', array(
			'screen' => get_current_screen(),
			'page'   => $this,
			'labels' => $this->get_labels(),
		));

	} // end render_submit_box;

	/**
	 * Wizard classes should implement a method that returns an array of sections and subsections.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	abstract public function get_sections(); // end get_sections;

} // end class Wizard_Admin_Page;
