<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on ServerPilot.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/ServerPilot_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class ServerPilot_Host_Provider extends Base_Host_Provider {

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'serverpilot';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'ServerPilot';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://help.wpultimo.com/en/articles/2632349-configuring-automatic-domain-syncing-with-serverpilot-io-with-autossl-support';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = array(
		'autossl',
	);

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $constants = array(
		'WU_SERVER_PILOT_CLIENT_ID',
		'WU_SERVER_PILOT_API_KEY',
		'WU_SERVER_PILOT_APP_ID',
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

		return defined('WP_PLUGIN_DIR') && preg_match('/\/srv\/users\/(.+)\/apps\/(.+)/', WP_PLUGIN_DIR);

	} // end detect;

	/**
	 * This method gets called when a new domain is mapped.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_domain($domain, $site_id) {

		$current_domain_list = $this->get_server_pilot_domains();

		if ($current_domain_list && is_array($current_domain_list)) {

			$this->send_server_pilot_api_request('', array(
				'domains' => array_merge($current_domain_list, array($domain, 'www.' . $domain)),
			));

			/**
			 * Makes sure autoSSL is always on
			 */
			$this->turn_server_pilot_auto_ssl_on();

		} // end if;

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

		$current_domain_list = $this->get_server_pilot_domains();

		if ($current_domain_list && is_array($current_domain_list)) {

			/**
			 * Removes the current domain fromt he domain list
			 */
			$current_domain_list = array_filter($current_domain_list, function($remote_domain) use($domain) {

				return $remote_domain !== $domain && $remote_domain !== 'www.' . $domain;

			});

			$this->send_server_pilot_api_request('', array(
				'domains' => $current_domain_list
			));

		} // end if;

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
	public function on_add_subdomain($subdomain, $site_id) {

		$current_domain_list = $this->get_server_pilot_domains();

		if ($current_domain_list && is_array($current_domain_list)) {

			$this->send_server_pilot_api_request('', array(
				'domains' => array_merge($current_domain_list, array($subdomain)),
			));

			/**
			 * Makes sure autoSSL is always on
			 */
			$this->turn_server_pilot_auto_ssl_on();

		} // end if;

	} // end on_add_subdomain;

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
	 * Sends a request to ServerPilot, with the right API key.
	 *
	 * @since  1.7.3
	 * @param  string $endpoint Endpoint to send the call to.
	 * @param  array  $data     Array containing the params to the call.
	 * @param  string $method   HTTP Method: POST, GET, PUT, etc.
	 * @return object
	 */
	public function send_server_pilot_api_request($endpoint, $data = array(), $method = 'POST') {

		$post_fields = array(
			'timeout'  => 45,
			'blocking' => true,
			'method'   => $method,
			'body'     => $data ? json_encode($data) : array(),
			'headers'  => array(
				'Authorization' => 'Basic ' . base64_encode(WU_SERVER_PILOT_CLIENT_ID . ':' . WU_SERVER_PILOT_API_KEY),
				'Content-Type'  => 'application/json',
			),
		);

		$response = wp_remote_request('https://api.serverpilot.io/v1/apps/' . WU_SERVER_PILOT_APP_ID . $endpoint, $post_fields);

		if (!is_wp_error($response)) {

			$body = json_decode(wp_remote_retrieve_body($response), true);

			if (json_last_error() === JSON_ERROR_NONE) {
				return $body;
			} // end if;

		} // end if;

		return $response;

	} // end send_server_pilot_api_request;

	/**
	 * Makes sure ServerPilot autoSSL is always on, when possible.
	 *
	 * @since 1.7.4
	 * @return bool
	 */
	public function turn_server_pilot_auto_ssl_on() {

		return $this->send_server_pilot_api_request('/ssl', array(
			'auto' => true,
		));

	} // end turn_server_pilot_auto_ssl_on;

	/**
	 * Get the current list of domains added on Server Pilot.
	 *
	 * @since 1.7.4
	 * @return mixed
	 */
	public function get_server_pilot_domains() {

		$app_info = $this->send_server_pilot_api_request('', array(), 'GET');

		if (isset($app_info['data']['domains'])) {

			return $app_info['data']['domains'];

		} // end if;

		/*
		 * Log response so we can see what went wrong
		 */

		// translators: %s is the json_encode of the error.
		wu_log_add('integration-serverpilot', sprintf(__('An error occurred while trying to get the current list of domains: %s', 'wp-ultimo'), json_encode($app_info)));

		return false;

	} // end get_server_pilot_domains;

} // end class ServerPilot_Host_Provider;
