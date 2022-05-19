<?php
/**
 * WP Ultimo Rollback Plugin Upgrader
 *
 * Class that extends the WP Core Plugin_Upgrader found in core to do rollbacks.
 * Modified to fit WP Ultimo needs.
 *
 * @package WP_Ultimo
 * @subpackage Rollback
 * @since 2.0.0
 */

namespace WP_Ultimo\Rollback;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Rollback Plugin Upgrader
 *
 * @since 2.0.0
 */
class Rollback_Plugin_Upgrader extends \Plugin_Upgrader {

	/**
	 * Holds messages.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $messages = array();

	/**
	 * Plugin rollback.
	 *
	 * @param string $plugin Plugin Slug.
	 * @param array  $args Arguments.
	 *
	 * @return array|bool|\WP_Error
	 */
	public function rollback($plugin, $args = array()) {

		$defaults = array(
			'clear_update_cache' => true,
			'url'                => '',
		);

		$parsed_args = wp_parse_args($args, $defaults);

		$this->init();

		$this->upgrade_strings();

		// TODO: Add final check to make sure plugin exists
		if (0) {

			$this->skin->before();
			$this->skin->set_result(false);
			$this->skin->error('up_to_date');
			$this->skin->after();

			return false;

		} // end if;

		$plugin_slug = $this->skin->plugin;

		$plugin_version = $this->skin->options['version'];

		$url = $args['url'];

		add_filter('upgrader_pre_install', array($this, 'deactivate_plugin_before_upgrade'), 10, 2);

		add_filter('upgrader_clear_destination', array($this, 'delete_old_plugin'), 10, 4);

		$this->run(array(
			'package'           => $url,
			'destination'       => WP_PLUGIN_DIR,
			'clear_destination' => true,
			'clear_working'     => true,
			'hook_extra'        => array(
				'plugin' => $plugin,
				'type'   => 'plugin',
				'action' => 'update',
			),
		));

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter('upgrader_pre_install', array($this, 'deactivate_plugin_before_upgrade'));

		remove_filter('upgrader_clear_destination', array($this, 'delete_old_plugin'));

		if (!$this->result || is_wp_error($this->result)) {

			return false;

		} // end if;

		// Force refresh of plugin update information.
		wp_clean_plugins_cache($parsed_args['clear_update_cache']);

		return true;

	} // end rollback;

} // end class Rollback_Plugin_Upgrader;
