<?php
/**
 * WP Ultimo System Info Admin Page.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Pages
 * @since 2.0.0
 */

namespace WP_Ultimo\Admin_Pages;

use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo System Info Admin Page.
 */
class View_Logs_Admin_Page extends Edit_Admin_Page {

	/**
	 * Holds the ID for this page, this is also used as the page slug.
	 *
	 * @var string
	 */
	protected $id = 'wp-ultimo-view-logs';

	/**
	 * Is this a top-level menu or a submenu?
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $type = 'submenu';

	/**
	 * If this is a submenu, we need a parent menu to attach this to
	 *
	 * @since 1.8.2
	 * @var string
	 */
	protected $parent = 'none';

	/**
	 * Allows us to highlight another menu page, if this page has no parent page at all.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $highlight_menu_slug = 'wp-ultimo-events';

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
		'network_admin_menu' => 'manage_network',
	);

	/**
	 * Allow child classes to add further initializations.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function init() {

		add_action('wp_ajax_wu_handle_view_logs', array($this, 'handle_view_logs'));

	} // end init;

	/**
	 * Registers extra scripts needed for this page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_scripts() {

		parent::register_scripts();

		\WP_Ultimo\Scripts::get_instance()->register_script('wu-view-log', wu_get_asset('view-logs.js', 'js'), array('jquery'));

		wp_localize_script('wu-view-log', 'wu_view_logs', array(
			'i18n' => array(
				'copied' => __('Copied!', 'wp-ultimo'),
			),
		));

		wp_enqueue_script('wu-view-log');

		wp_enqueue_script('clipboard');

	} // end register_scripts;

	/**
	 * Returns the title of the page.
	 *
	 * @since 2.0.0
	 * @return string Title of the page.
	 */
	public function get_title() {

		return __('View Log', 'wp-ultimo');

	} // end get_title;

	/**
	 * Returns the title of menu for this page.
	 *
	 * @since 2.0.0
	 * @return string Menu label of the page.
	 */
	public function get_menu_title() {

		return __('View Log', 'wp-ultimo');

	} // end get_menu_title;

	/**
	 * Handles the actions for the logs and system info.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function handle_view_logs() {

		$logs_list = list_files(Logger::get_logs_folder(), 2, array(
			'index.html',
		));

		$logs_list = array_combine(array_values($logs_list), array_map(function($file) {

			return str_replace(Logger::get_logs_folder(), '', $file);

		}, $logs_list));

		if (empty($logs_list)) {

			$logs_list[''] = __('No log files found', 'wp-ultimo');

		} // end if;

		$file = wu_request('file');

		$file_name = '';

		$contents = '';

		// Security check
		if ($file && !stristr($file, Logger::get_logs_folder())) {

			wp_die(__('You can see files that are not WP Ultimo\'s logs', 'wp-ultimo'));

		} // end if;

		if (!$file && !empty($logs_list)) {

			$file = !$file && !empty($logs_list) ? current(array_keys($logs_list)) : false;

		} // end if;

		$file_name = str_replace(Logger::get_logs_folder(), '', $file);

		$default_content = wu_request('return_ascii', 'yes') === 'yes' ? wu_get_template_contents('events/ascii-badge') : __('No log entries found.', 'wp-ultimo');

		$contents = $file && file_exists($file) ? file_get_contents($file) : $default_content;

		$response = array(
			'file'      => $file,
			'file_name' => $file_name,
			'contents'  => $contents,
			'logs_list' => $logs_list,
		);

		if (wp_doing_ajax()) {

			wp_send_json_success($response);

		} else {

			return $response;

		} // end if;

	} // end handle_view_logs;

	/**
	 * Allow child classes to register widgets, if they need them.
	 *
	 * @since 1.8.2
	 * @return void
	 */
	public function register_widgets() {

		$info = $this->handle_view_logs();

		add_meta_box('wp-ultimo-log-contents', __('Log Contents', 'wp-ultimo'), array($this, 'output_default_widget_payload'), get_current_screen()->id, 'normal', null, $info);

		$this->add_fields_widget('file-selector', array(
			'title'  => __('Log Files', 'wp-ultimo'),
			'fields' => array(
				'log_file' => array(
					'type'        => 'select',
					'title'       => __('Select Log File', 'wp-ultimo'),
					'placeholder' => __('Select Log File', 'wp-ultimo'),
					'value'       => wu_request('file'),
					'tooltip'     => '',
					'options'     => $info['logs_list'],
				),
				'download' => array(
					'type'    => 'submit',
					'title'   => __('Download Log', 'wp-ultimo'),
					'value'   => 'download',
					'classes' => 'button button-primary wu-w-full',
				),
			),
		));

		$this->add_fields_widget('info', array(
			'title'    => __('Timestamps', 'wp-ultimo'),
			'position' => 'side',
			'fields'   => array(
				'date_modified' => array(
					'title'         => __('Last Modified at', 'wp-ultimo'),
					'type'          => 'text-edit',
					'date'          => true,
					'value'         => date_i18n('Y-m-d H:i:s', filemtime($info['file'])),
					'display_value' => date_i18n('Y-m-d H:i:s', filemtime($info['file'])),
				)
			),
		));

	} // end register_widgets;

	/**
	 * Outputs the pre block that shows the content.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $unused Not sure.
	 * @param array $data Arguments passed by add_meta_box.
	 * @return void
	 */
	public function output_default_widget_payload($unused, $data) {

		wu_get_template('events/widget-payload', array(
			'title'        => __('Event Payload', 'wp-ultimo'),
			'loading_text' => __('Loading Payload', 'wp-ultimo'),
			'payload'      => $data['args']['contents'],
		));

	} // end output_default_widget_payload;

	/**
	 * Returns the labels to be used on the admin page.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_labels() {

		return array(
			'edit_label'          => __('View Log', 'wp-ultimo'),
			'add_new_label'       => __('View Log', 'wp-ultimo'),
			'title_placeholder'   => __('Enter Customer', 'wp-ultimo'),
			'title_description'   => __('Viewing file: ', 'wp-ultimo'),
			'delete_button_label' => __('Delete Log File', 'wp-ultimo'),
			'delete_description'  => __('Be careful. This action is irreversible.', 'wp-ultimo'),
		);

	} // end get_labels;

	/**
	 * Returns the object being edit at the moment.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_object() {

		return array();

	} // end get_object;

	/**
	 * Register additional hooks to page load such as the action links and the save processing.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function page_loaded() {

		/**
		 * Get the action links
		 */
		$this->action_links = $this->action_links();

		/**
		 * Process save, if necessary
		 */
		$this->process_save();

	} // end page_loaded;

	/**
	 * Should implement the processes necessary to save the changes made to the object.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handle_save() {

		$action = wu_request('submit_button', 'none');

		if ($action === 'none') {

			WP_Ultimo()->notices->add(__('Something wrong happened', 'wp-ultimo'), 'error', 'network-admin');

			return;

		} // end if;

		$file = wu_request('log_file', false);

		if (!file_exists($file)) {

			WP_Ultimo()->notices->add(__('File not found', 'wp-ultimo'), 'error', 'network-admin');

			return;

		} // end if;

		if ($action === 'download') {

			$file_name = str_replace(Logger::get_logs_folder(), '', $file);

			header('Content-Type: application/octet-stream');
			header("Content-Disposition: attachment; filename=$file_name");
			header('Pragma: no-cache');

			readfile($file);

			exit;

		} elseif ($action === 'delete') {

			$status = unlink($file);

			if (!$status) {

				WP_Ultimo()->notices->add(__('We were unable to delete file', 'wp-ultimo'), 'error', 'network-admin');

				return;

			} // end if;

		} // end if;

		$url = remove_query_arg('log_file');

		wp_redirect(add_query_arg('deleted', 1, $url));

		exit;

	} // end handle_save;

} // end class View_Logs_Admin_Page;
