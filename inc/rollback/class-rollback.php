<?php
/**
 * WP Ultimo Rollback
 *
 * Allows users to rollback WP Ultimo to the previous stable version.
 *
 * @package WP_Ultimo
 * @subpackage Rollback
 * @since 2.0.0
 */

namespace WP_Ultimo\Rollback;

use \WP_Ultimo\License;
use \WP_Ultimo\Logger;
use \WP_Ultimo\Rollback\Rollback_Plugin_Upgrader;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Rollback.
 *
 * @since 2.0.0
 */
class Rollback {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Holds the URL for serving build files.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	public $updates_url = 'https://versions.nextpress.co/updates/?action=get_metadata&slug=wp-ultimo';

	/**
	 * Init
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function init() {

		add_filter('network_admin_plugin_action_links', array($this, 'plugin_page_action_links'), 200, 4);

		add_action('admin_init', array($this, 'handle_rollback_process'));

		add_action('plugins_loaded', array($this, 'load_admin_page'));

	} // end init;

	/**
	 * Loads the rollback admin page.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function load_admin_page() {
		/*
		 * Loads the Rollback Pages
		 */
		new \WP_Ultimo\Admin_Pages\Rollback_Admin_Page();

	} // end load_admin_page;

	/**
	 * Handle the Rollback action.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	public function handle_rollback_process() {

		if (wu_request('action') !== 'rollback-wp-ultimo') {

			return;

		} // end if;

		/*
		 * Run a couple of security checks here.
		 * First we check for a valid referer and nonce.
		 */
		check_admin_referer('wp-ultimo-rollback', '_wpnonce');

		/*
		 * Then, we check for permissions.
		 */
		if (current_user_can('manage_network') === false) {

			return;

		} // end if;

		/*
		 * We're safe. Now we can build the URL...
		 * First, reveal the API key.
		 */
		$license_key = rawurlencode(base64_decode(wu_request('n', '')));

		$rollback_url = $this->get_versions_url('download', array(
			'rollback'    => wu_request('type', 'latest-stable') === 'latest-stable',
			'version'     => wu_request('version', wu_get_version()),
			'license_key' => $license_key,
		));

			/*
			 * Unhook shutdown hooks.
			 */
		if (class_exists('ActionScheduler_QueueRunner')) {

			\ActionScheduler_QueueRunner::instance()->unhook_dispatch_async_request();

		} // end if;

		/**
		 * Disable Freemius
		 */
		$fs_updater = \FS_Plugin_Updater::instance(License::get_instance()->get_activator());

		if ($fs_updater) {

			remove_filter('pre_set_site_transient_update_plugins', array(
				&$fs_updater,
				'pre_set_site_transient_update_plugins_filter'
			));

			remove_filter('pre_set_site_transient_update_themes', array(
				&$fs_updater,
				'pre_set_site_transient_update_plugins_filter'
			));

		} // end if;

		$this->process_rollback(array(
			'url' => $rollback_url,
		));

	} // end handle_rollback_process;

	/**
	 * Adds the rollback link to the WP Ultimo plugin omn the Plugin list table.
	 *
	 * @since 2.0.0
	 *
	 * @param string $actions Current actions.
	 * @param string $plugin_file The path of the plugin file.
	 * @param array  $plugin_data Data about the plugin.
	 * @param string $context Context of the table.
	 * @return string New actions list.
	 */
	public function plugin_page_action_links($actions, $plugin_file, $plugin_data, $context) {

		if (is_multisite() && (!is_network_admin() && !is_main_site())) {

			return $actions;

		} // end if;

		if (!isset($plugin_data['Version'])) {

			return $actions;

		} // end if;

		if ($plugin_file !== 'wp-ultimo/wp-ultimo.php') {

			return $actions;

		} // end if;

		$actions['rollback'] = '<a href="' . wu_network_admin_url('wp-ultimo-rollback') . '">' . __('Rollback', 'wp-ultimo') . '</a>';

		return $actions;

	} // end plugin_page_action_links;

	/**
	 * Process the Rollback.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function process_rollback($args = array()) {

		$plugin_file = 'wp-ultimo/wp-ultimo.php';

		if (!class_exists('\Plugin_Upgrader_Skin')) {

			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			require_once wu_path('inc/rollback/class-rollback-plugin-upgrader.php');

		} // end if;

		$slug = 'wp-ultimo';

		$nonce = "upgrade-plugin_{$slug}";
		$url   = sprintf('index.php?page=wp-rollback&plugin_file=%s&action=upgrade-plugin', esc_url($plugin_file));

		Logger::add('rollback-errors', sprintf('Rollback started... Download URL: %s', $url));

		$plugin  = $slug;
		$version = wu_get_version();

		$title = '';

		try {

			$upgrader = new Rollback_Plugin_Upgrader(new Quiet_Plugin_Upgrader_Skin(compact('title', 'nonce', 'url', 'plugin', 'version')));

			$results = $upgrader->rollback('wp-ultimo', array(
				'url' => $args['url'],
			));

			if ($results) {

				wp_redirect(network_admin_url('plugins.php?activate=1'));

				exit;

			} else {

				$messages = implode('<br><br>', $upgrader->messages);

				wp_die($messages);

			} // end if;

		} catch (\Throwable $e) {

			// translators: %s is the error message captured.
			$error = new \WP_Error('maybe-error', sprintf(__('Something might have gone wrong doing the rollback. Check to see if the WP Ultimo version was downgraded or not on the plugins page. Error captured: %s.', 'wp-ultimo'), $e->getMessage()));

			Logger::add('rollback-errors', $e->getMessage());

			wp_die($error);

		} // end try;

	} // end process_rollback;

	/**
	 * Get the URLs we will need to use to rollback.
	 *
	 * @since 2.0.0
	 *
	 * @param string $action Action to add to the URL.
	 * @param array  $args Parameters to add.
	 * @return string
	 */
	public function get_versions_url($action = 'download', $args = array()) {

		$defaults = array(
			'version'      => wu_get_version(),
			'rollback'     => true,
			'beta_program' => 2,
			'license_key'  => rawurlencode(License::get_instance()->get_license_key()),
		);

		$rollback_url = add_query_arg(wp_parse_args($args, $defaults), $this->updates_url);

		$rollback_url = str_replace('get_metadata', $action, $rollback_url);

		return $rollback_url;

	} // end get_versions_url;

	/**
	 * Get the available list of versions.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_available_versions() {

		$url = $this->get_versions_url('available_versions');

		$response = wp_remote_get($url);

		if (is_wp_error($response)) {

			return $response;

		} // end if;

		$list = json_decode(wp_remote_retrieve_body($response));

		if (is_object($list) && $list->error === 'Invalid license_key') {

			return false;

		} // end if;

		/*
		 * Clean up development builds.
		 */
		$list = array_filter($list, function($item) {

			return strpos($item, 'beta') === false && strpos($item, 'rc') === false;

		});

		$response_list = array();

		$current_list = array_reverse(array_filter($list, function($item) {

			return strpos($item, '2.') === 0;

		}));

		$response_list = array_slice($current_list, 0, 10);

		if ($this->_is_legacy_network()) {

			$legacy_list = array_reverse(array_filter($list, function($item) {

				return strpos($item, '1.') === 0;

			}));

			$response_list = array_merge(array_slice($response_list, 0, 7), array_slice($legacy_list, 0, 3));

		} // end if;

		return $response_list;

	} // end get_available_versions;

	/**
	 * Check if network have a legacy install to rollback.
	 * FIXME: remove this and break Migrator::is_legacy_network in two methods.
	 *
	 * @since 2.0.11
	 * @return bool
	 */
	private function _is_legacy_network() {

		$plans = get_posts(array(
			'post_type'   => 'wpultimo_plan',
			'numberposts' => 1,
		));

		return !empty($plans);

	} // end _is_legacy_network;

} // end class Rollback;
