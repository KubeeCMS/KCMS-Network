<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on Cloudways.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Cloudways_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class Cloudways_Host_Provider extends Base_Host_Provider {

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'cloudways';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'Cloudways';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://help.wpultimo.com/article/294-configuring-automatic-domain-syncing-with-cloudways';

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
		'WU_CLOUDWAYS_EMAIL',
		'WU_CLOUDWAYS_API_KEY',
		'WU_CLOUDWAYS_SERVER_ID',
		'WU_CLOUDWAYS_APP_ID',
	);

	/**
	 * Constants that maybe present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $optional_constants = array(
		'WU_CLOUDWAYS_EXTRA_DOMAINS',
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

		return strpos(ABSPATH, 'cloudways') !== false;

	} // end detect;

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return array(
			'WU_CLOUDWAYS_EMAIL'         => array(
				'title'       => __('Cloudways Account Email', 'wp-ultimo'),
				'desc'        => __('Your Cloudways account email address.', 'wp-ultimo'),
				'placeholder' => __('e.g. me@gmail.com', 'wp-ultimo'),
			),
			'WU_CLOUDWAYS_API_KEY'       => array(
				'title'       => __('Cloudways API Key', 'wp-ultimo'),
				'desc'        => __('The API Key retrieved in the previous step.', 'wp-ultimo'),
				'placeholder' => __('e.g. eYP0Jo3Fzzm5SOZCi5nLR0Mki2lbYZ', 'wp-ultimo'),
			),
			'WU_CLOUDWAYS_SERVER_ID'     => array(
				'title'       => __('Cloudways Server ID', 'wp-ultimo'),
				'desc'        => __('The Server ID retrieved in the previous step.', 'wp-ultimo'),
				'placeholder' => __('e.g. 11667', 'wp-ultimo'),
			),
			'WU_CLOUDWAYS_APP_ID'        => array(
				'title'       => __('Cloudways App ID', 'wp-ultimo'),
				'desc'        => __('The App ID retrieved in the previous step.', 'wp-ultimo'),
				'placeholder' => __('e.g. 940288', 'wp-ultimo'),
			),
			'WU_CLOUDWAYS_EXTRA_DOMAINS' => array(
				'title'       => __('Cloudways Extra Domains', 'wp-ultimo'),
				'tooltip'     => __('The Cloudways API is a bit strange in that it doesn’t offer a way to add or remove just one domain, only a way to update the whole domain list. That means that WP Ultimo will replace all domains you might have there with the list of mapped domains of the network every time a new domain is added.', 'wp-ultimo'),
				'desc'        => __('Comma-separated list of additional domains to add to Cloudways.', 'wp-ultimo'),
				'placeholder' => __('e.g. *.test.com, test.com', 'wp-ultimo'),
			),
		);

	} // end get_fields;

	/**
	 * This method gets called when a new domain is mapped.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_domain($domain, $site_id) {

		$response = $this->send_cloudways_request('/app/manage/aliases', array(
			'aliases' => $this->get_domain_list(),
		));

		if (is_wp_error($response)) {

			wu_log_add('integration-cloudways', $response->get_error_message());

		} else {

			wu_log_add('integration-cloudways', wp_remote_retrieve_body($response));

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

		$response = $this->send_cloudways_request('/app/manage/aliases', array(
			'aliases' => $this->get_domain_list(),
		));

		if (is_wp_error($response)) {

			wu_log_add('integration-cloudways', $response->get_error_message());

		} else {

			wu_log_add('integration-cloudways', wp_remote_retrieve_body($response));

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
	 * Tests the connection with the Cloudways API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection() {

		$response = $this->send_cloudways_request('/app/manage/fpm_setting', array(), 'GET');

		if (is_wp_error($response) || wu_get_isset($response, 'error')) {

			wp_send_json_error($response);

		} // end if;

		wp_send_json_success($response);

	} // end test_connection;

	/**
	 * Returns an array of all the mapped domains currently on the network
	 *
	 * @since 1.6.0
	 * @return array
	 */
	public function get_all_mapped_domains() {

		global $wpdb;

		$final_domain_list = array();

    // Prepare the query
		$query = "SELECT domain FROM {$wpdb->base_prefix}wu_domain_mappings";

		// Suppress errors in case the table doesn't exist.
		$suppress = $wpdb->suppress_errors();

		$mappings = $wpdb->get_col($query, 0); // phpcs:ignore

		foreach ($mappings as $domain) {

			$final_domain_list[] = $domain;

			if (strpos($domain, 'www.') !== 0) {

				$final_domain_list[] = "www.$domain";

			} // end if;

		} // end foreach;

		$wpdb->suppress_errors($suppress);

		return $final_domain_list;

	} // end get_all_mapped_domains;

	/**
	 * Get extra domains for Cloudways
	 *
	 * @since 1.6.1
	 * @return array
	 */
	protected function get_domain_list() {

		$domain_list = $this->get_all_mapped_domains();

		$extra_domains = defined('WU_CLOUDWAYS_EXTRA_DOMAINS') && WU_CLOUDWAYS_EXTRA_DOMAINS;

		if ($extra_domains) {

			$extra_domains_list = array_filter(array_map('trim', explode(',', WU_CLOUDWAYS_EXTRA_DOMAINS)));

			$domain_list = array_merge($domain_list, $extra_domains_list);

		} // end if;

		return $domain_list;

	} // end get_domain_list;

	/**
	 * Fetches and saves a Cloudways access token.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_cloudways_access_token() {

		$token = get_site_transient('wu_cloudways_token');

		if (!$token) {

			$response = wp_remote_post('https://api.cloudways.com/api/v1/oauth/access_token', array(
				'blocking' => true,
				'method'   => 'POST',
				'headers'  => array(
					'cache-control' => 'no-cache',
					'content-type'  => 'application/x-www-form-urlencoded',
				),
				'body'     => array(
					'email'   => defined('WU_CLOUDWAYS_EMAIL') ? WU_CLOUDWAYS_EMAIL : '',
					'api_key' => defined('WU_CLOUDWAYS_API_KEY') ? WU_CLOUDWAYS_API_KEY : '',
				),
			));

			if (!is_wp_error($response)) {

				$body = json_decode(wp_remote_retrieve_body($response), true);

				if (isset($body['access_token'])) {

					$expires_in = isset($body['expires_in']) ? $body['expires_in'] : 50 * MINUTE_IN_SECONDS;

					set_site_transient('wu_cloudways_token', $body['access_token'], $expires_in);

					$token = $body['access_token'];

				} // end if;

			} // end if;

		} // end if;

		return $token;

	} // end get_cloudways_access_token;

	/**
	 * Sends a request to the Cloudways API.
	 *
	 * @since 2.0.0
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $data The data to send.
	 * @param string $method The HTTP verb.
	 * @return object|\WP_Error
	 */
	protected function send_cloudways_request($endpoint, $data = array(), $method = 'POST') {

		$token = $this->get_cloudways_access_token();

		$endpoint_url = "https://api.cloudways.com/api/v1/$endpoint";

		if ($method === 'GET') {

			$endpoint_url = add_query_arg(array(
				'server_id' => defined('WU_CLOUDWAYS_SERVER_ID') ? WU_CLOUDWAYS_SERVER_ID : '',
				'app_id'    => defined('WU_CLOUDWAYS_APP_ID') ? WU_CLOUDWAYS_APP_ID : '',
			), $endpoint_url);

		} else {

			$data['server_id'] = defined('WU_CLOUDWAYS_SERVER_ID') ? WU_CLOUDWAYS_SERVER_ID : '';
			$data['app_id']    = defined('WU_CLOUDWAYS_APP_ID') ? WU_CLOUDWAYS_APP_ID : '';

		} // end if;

		$response = wp_remote_post($endpoint_url, array(
			'blocking' => true,
			'method'   => $method,
			'body'     => $data,
			'headers'  => array(
				'cache-control' => 'no-cache',
				'content-type'  => 'application/x-www-form-urlencoded',
				'authorization' => "Bearer $token",
			),
		));

		if (is_wp_error($response)) {

			return $response;

		} // end if;

		$response_data = wp_remote_retrieve_body($response);

		return json_decode($response_data);

	} // end send_cloudways_request;

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_instructions() {

		wu_get_template('wizards/host-integrations/cloudways-instructions');

	} // end get_instructions;

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Focus on your business and avoid all the web hosting hassles. Our managed hosting guarantees unmatched performance, reliability and choice with 24/7 support that acts as your extended team, making Cloudways an ultimate choice for growing agencies and e-commerce businesses.', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('cloudways.png', 'img/hosts');

	} // end get_logo;

} // end class Cloudways_Host_Provider;
