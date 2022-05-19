<?php
/**
 * Handles the Admin Notices added by WP Ultimo.
 *
 * @package WP_Ultimo
 * @subpackage Admin_Notices
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles Admin Notices.
 *
 * @since 2.0.0
 */
class Admin_Notices {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds the notices added by WP Ultimo.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $notices = array(
		'admin'         => array(),
		'network-admin' => array(),
		'user'          => array(),
	);

	/**
	 * Loads the hooks we need for dismissing notices
	 *
	 * @since 2.0.0
	 */
	public function init() {

		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

		add_action('in_admin_header', array($this, 'display_notices'));

		add_action('wp_ajax_wu_dismiss_admin_notice', array($this, 'ajax_dismiss_admin_notices'));

	} // end init;

	/**
	 * Get the notices the current user has dismissed
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_dismissed_notices() {

		$dismissed = get_user_meta(get_current_user_id(), 'wu_dismissed_admin_notices', true);

		$dismissed = $dismissed ? $dismissed : array();

		return $dismissed;

	} // end get_dismissed_notices;

	/**
	 * Adds a new admin notice
	 *
	 * An Admin Notice is a massage displayed at the top of the admin panel, alerting
	 * users of something wrong, successful or informational.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $notice The message to display on the notice block. Supports HTML.
	 * @param string  $type Type of notice. Can be info, warning, error or success.
	 * @param string  $panel Panel on which to show this notice. Can be network-admin, admin or user.
	 * @param boolean $dismissible_key Key to keep track if this notice was already displayed and dismissed.
	 * @param array   $actions List of buttons to add to the notification block.
	 * @return void
	 */
	public function add($notice, $type = 'success', $panel = 'admin', $dismissible_key = false, $actions = array()) {

		$id = $dismissible_key ? $dismissible_key : md5($notice);

		$this->notices[$panel][$id] = array(
			'type'            => $type,
			'message'         => $notice,
			'dismissible_key' => is_string($dismissible_key) ? $dismissible_key : false,
			'actions'         => $actions,
		);

	}  // end add;

	/**
	 * Returns the list of notices added for a particular panel.
	 *
	 * @since 2.0.0
	 *
	 * @param string  $panel Panel to retrieve the notices. Defaults to admin.
	 * @param boolean $filter Wether or not to removed notices already dismissed or not. Defaults to true.
	 * @return array
	 */
	public function get_notices($panel = 'admin', $filter = true) {

		$notices = isset($this->notices[$panel]) ? $this->notices[$panel] : array();

		$dismissed_messages = $this->get_dismissed_notices();

		if ($filter && $notices) {

			$notices = array_filter($notices, function($notice) use ($dismissed_messages) {

				return !$notice['dismissible_key'] || !in_array($notice['dismissible_key'], $dismissed_messages, true);

			});

		} // end if;

		/**
		 * Allow developers to filter admin notices added by WP Ultimo.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $notices List of notices for that particular panel.
		 * @param array  $all_notices List of notices added, segregated by panel.
		 * @param string $panel Panel to retrieve the notices.
		 * @param string $filter If the dismissable notices have been filtered out.
		 * @param array  $dismissed_messages List of dismissed notice keys.
	   * @return array
		 */
		return apply_filters('wu_admin_notices', $notices, $this->notices, $panel, $filter, $dismissed_messages);

	} // end get_notices;

	/**
	 * Enqueues the JavaScript code that sends the dismiss call to the ajax endpoint.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function enqueue_scripts() {

		wp_enqueue_script('wu-admin-notices', wu_get_asset('admin-notices.js', 'js'), array('jquery'), wu_get_version());

	} // end enqueue_scripts;

	/**
	 * Gets the current panel the user is viewing
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_current_panel() {

		$panel = 'admin';

		if (is_user_admin()) {

			$panel = 'user';

		} // end if;

		if (is_network_admin()) {

			$panel = 'network-admin';

		} // end if;

		return $panel;

	} // end get_current_panel;

	/**
	 * Retrieves the admin notices for the current panel and displays them.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_notices() {

		$panel = $this->get_current_panel();

		$notices = $this->get_notices($panel);

		wu_get_template('admin-notices', array(
			'notices' => $notices,
			'nonce'   => wp_create_nonce('wu-dismiss-admin-notice'),
		));

	} // end display_notices;

	/**
	 * Adds an ajax endpoint to dismiss admin notices
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function ajax_dismiss_admin_notices() {

		if (!wp_verify_nonce($_POST['nonce'], 'wu-dismiss-admin-notice')) {

			die('-1');

		} // end if;

		$dismissed = $this->get_dismissed_notices();

		if (!in_array($_POST['notice_id'], $dismissed, true)) {

			$dismissed[] = $_POST['notice_id'];

			update_user_meta(get_current_user_id(), 'wu_dismissed_admin_notices', $dismissed);

			die('1');

		} // end if;

		die('0');

	} // end ajax_dismiss_admin_notices;

} // end class Admin_Notices;
