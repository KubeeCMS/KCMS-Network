<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on Closte.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Closte
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class Closte_Host_Provider extends Base_Host_Provider {

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'closte';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'Closte';

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
		'autossl',
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
		'CLOSTE_CLIENT_API_KEY',
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

		return defined('CLOSTE_CLIENT_API_KEY') && CLOSTE_CLIENT_API_KEY;

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

		$this->send_closte_api_request('/adddomainalias', array(
			'domain'   => $domain,
			'wildcard' => strpos($domain, '*.') === 0
		));

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

		$this->send_closte_api_request('/deletedomainalias', array(
			'domain'   => $domain,
			'wildcard' => strpos($domain, '*.') === 0
		));

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
	 * Tests the connection with the API.
	 *
	 * Needs to be implemented by integrations.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection() {

		$response = $this->send_closte_api_request('/adddomainalias', array());

		if (wu_get_isset($response, 'error') === 'Invalid or empty domain: ') {

			wp_send_json_success(array(
				'message' => __('Access Authorized'),
			));

		} // end if;

		$error = new \WP_Error('not-auth', __('Something went wrong', 'wp-ultimo'));

		wp_send_json_error($error);

	} // end test_connection;

	/**
	 * Sends a request to Closte, with the right API key.
	 *
	 * @since  1.7.3
	 * @param  string $endpoint Endpoint to send the call to.
	 * @param  array  $data     Array containing the params to the call.
	 * @return object
	 */
	public function send_closte_api_request($endpoint, $data) {

		if (defined('CLOSTE_CLIENT_API_KEY') === false) {

			return (object) array(
				'success' => false,
				'error'   => 'Closte API Key not found.',
			);

		} // end if;

		$post_fields = array(
			'blocking' => true,
			'timeout'  => 45,
			'method'   => 'POST',
			'body'     => array_merge(array(
				'apikey' => CLOSTE_CLIENT_API_KEY,
			), $data)
		);

		$response = wp_remote_post('https://app.closte.com/api/client' . $endpoint, $post_fields);

		wu_log_add('integration-closte', wp_remote_retrieve_body($response));

		if (!is_wp_error($response)) {

			$body = json_decode(wp_remote_retrieve_body($response), true);

			if (json_last_error() === JSON_ERROR_NONE) {

				return $body;

			} // end if;

			return (object) array(
				'success' => false,
				'error'   => 'unknown'
			);

		} // end if;

		return $response;

	} // end send_closte_api_request;

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Closte is not just another web hosting who advertise their services as a cloud hosting while still provides fixed plans like in 1995.', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('closte.svg', 'img/hosts');

	} // end get_logo;

}  // end class Closte_Host_Provider;
