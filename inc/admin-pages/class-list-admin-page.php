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
abstract class List_Admin_Page extends Base_Admin_Page {

	/**
	 * The id/name/slug of the object being edited/created. e.g: plan
	 *
	 * @since 1.8.2
	 * @var object
	 */
	protected $object_id;

	/**
	 * Keep the labels
	 *
	 * @since 1.8.2
	 * @var array
	 */
	protected $labels = array();

	/**
	 * Holds the WP_List_Table instance to be used on the list
	 *
	 * @since 1.8.2
	 * @var WP_List_Table
	 */
	protected $table;

	/**
	 * Sets the default labels and get the object
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function page_loaded() {

		/**
		 * Loads the list table
		 */
		$this->table = $this->table();

		/**
		 * Gets the base labels
		 */
		$this->labels = $this->get_labels();

		/**
		 * Loads if we need to get the search
		 */
		$this->has_search = $this->has_search();

		/**
		 * Get the action links
		 */
		$this->action_links = $this->action_links();

		/**
		 * Adds the process for process actions
		 */
		$this->process_single_action();

	} // end page_loaded;

	/**
	 * Initializes the class
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function init() {

		/**
		 * Runs the parent init functions
		 */
		parent::init();

		add_filter('set-screen-option', array($this, 'save_screen_option'), 8, 3);

	} // end init;

	/**
	 * Process lins actions of the tables
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function process_single_action() {

		if ($this->table) {

			$this->table->process_single_action();

		} // end if;

	} // end process_single_action;

	/**
	 * Returns an array with the labels for the edit page.
	 *
	 * @since 1.8.2
	 * @return array
	 */
	public function get_labels() {

		return array(
			'deleted_message' => __('Object removed successfully.', 'wp-ultimo'),
			'search_label'    => __('Search Object', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Allow child classes to register scripts and styles that can be loaded on the output function, for example.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		wp_enqueue_script('wu-vue-apps');

		wp_enqueue_script('wu-fields');

		wp_enqueue_style('wp-color-picker');

		wp_enqueue_script('wu-selectizer');

	} // end register_scripts;

	/**
	 * Sets the default list template
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function output() {

		/**
		 * Renders the base list page layout, with the columns and everything else =)
		 */
		wu_get_template('base/list', array(
			'page'    => $this,
			'table'   => $this->get_table(),
			'classes' => $this->table->get_filters() ? 'wu-advanced-filters' : 'wu-no-advanced-filters',
		));

	} // end output;

	/**
	 * Child classes can to implement to hide the search field
	 *
	 * @since 1.8.2
	 * @return boolean
	 */
	public function has_search() {

		return true;

	} // end has_search;

	/**
	 * Set the screen options to allow users to set the pagination options of the subscriptions list
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function screen_options() {

		if ($this->table) {

			$args = array(
				'default' => 20,
				'label'   => $this->table->get_per_page_option_label(),
				'option'  => $this->table->get_per_page_option_name(),
			);

			add_screen_option('per_page', $args);

		} // end if;

	} // end screen_options;

	/**
	 * Tells WordPress we want to save screen options on our pages.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed  $value Value being saved.
	 * @param string $option Name of the option. This is usually a per_page.
	 * @param string $other_value Not sure, haha.
	 * @return bool
	 */
	public function save_screen_option($value, $option, $other_value) {

		return $value === false && is_numeric($other_value) ? (int) $other_value : $value;

	} // end save_screen_option;

	/**
	 * Dumb function. Child classes need to implement this to set the table that WP Ultimo will use
	 *
	 * @since 1.8.2
	 * @return WP_List_Table
	 */
	public function get_table() {

		return $this->table;

	} // end get_table;

	/**
	 * Loads the list table for this particular page.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\List_Tables\Base_List_Table
	 */
	abstract function table();

} // end class List_Admin_Page;
