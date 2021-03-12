<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on WPEngine.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/WPEngine
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class WPEngine_Host_Provider extends Base_Host_Provider {

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'wpengine';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'WP Engine';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = '';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = array(
		'no-instructions',
		'no-config',
	);

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $constants = array(
		'WPE_API',
		'WPE_PLUGIN_DIR',
	);

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function detect() {

		return defined('WPE_API') && WPE_API;

	} // end detect;

	/**
	 * Can be used to load dependencies.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_dependencies() {

		// if WP Engine is not defined, then return
		if (!defined('WPE_PLUGIN_DIR') || !is_readable(WPE_PLUGIN_DIR . '/class-wpeapi.php')) {

			return;

		} // end if;

		include_once WPE_PLUGIN_DIR . '/class-wpeapi.php';

	} // end load_dependencies;

	/**
	 * This method gets called when a new domain is mapped.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_domain($domain, $site_id) {

		$api = new WPE_API();

		$api->set_arg('method', 'domain');

		$api->set_arg('domain', $domain);

		$api->get();

	} // end on_add_domain;

	/**
	 * This method gets called when a mapped domain is removed.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being removed.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_remove_domain($domain, $site_id) {

		$api = new WPE_API();

		$api->set_arg('method', 'domain-remove');

		$api->set_arg('domain', $domain);

		$api->get();

	} // end on_remove_domain;

	/**
	 * This method gets called when a new subdomain is being added.
	 *
	 * This happens every time a new site is added to a network running on subdomain mode.
	 *
	 * @since 2.0.0
	 * @param string $subdomain The subdomain being added to the network.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_subdomain($subdomain, $site_id) {} // end on_add_subdomain;

	/**
	 * This method gets called when a new subdomain is being removed.
	 *
	 * This happens every time a new site is removed to a network running on subdomain mode.
	 *
	 * @since 2.0.0
	 * @param string $subdomain The subdomain being removed to the network.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_remove_subdomain($subdomain, $site_id) {} // end on_remove_subdomain;

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('WP Engine drives your business forward faster with the first and only WordPress Digital Experience Platform. We offer the best WordPress hosting and developer experience on a proven, reliable architecture that delivers unparalleled speed, scalability, and security for your sites.', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('wpengine.svg', 'img/hosts');

	} // end get_logo;

	/**
	 * Tests the connection with the WP Engine API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection() {

		$api = new WPE_API();

		$api->set_arg('method', 'domains');

		$results = $api->get();

		if (is_wp_error($results)) {

			wp_send_json_error($results->get_error_message());

		} else {

			wp_send_json_success($results);

		} // end if;

	} // end test_connection;

} // end class WPEngine_Host_Provider;
