<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on RunCloud.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Runcloud_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class Runcloud_Host_Provider extends Base_Host_Provider {

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'runcloud';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'RunCloud';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://help.wpultimo.com/en/articles/2636845-configuring-automatic-domain-syncing-with-runcloud-io';

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
		'WU_RUNCLOUD_API_KEY',
		'WU_RUNCLOUD_API_SECRET',
		'WU_RUNCLOUD_SERVER_ID',
		'WU_RUNCLOUD_APP_ID',
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

		return strpos(ABSPATH, 'runcloud') !== false;

	} // end detect;

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return array(
			'WU_RUNCLOUD_API_KEY'    => array(
				'title'       => __('RunCloud API Key', 'wp-ultimo'),
				'desc'        => __('The API Key retrieved in the previous step.', 'wp-ultimo'),
				'placeholder' => __('e.g. Sx9tHAn5XMrkeyZKS1a7uj8dGTLgKnlEOaJEFRt1m95L', 'wp-ultimo'),
			),
			'WU_RUNCLOUD_API_SECRET' => array(
				'title'       => __('RunCloud API Secret', 'wp-ultimo'),
				'desc'        => __('The API secret retrieved in the previous step.', 'wp-ultimo'),
				'placeholder' => __('e.g. ZlAebXp2sa6J5xsrPoiPcMXZRIVsHJ2rEkNCNGknZnF0UK5cSNSePS8GBW9FXIQd', 'wp-ultimo'),
			),
			'WU_RUNCLOUD_SERVER_ID'  => array(
				'title'       => __('RunCloud Server ID', 'wp-ultimo'),
				'desc'        => __('The Server ID retrieved in the previous step.', 'wp-ultimo'),
				'placeholder' => __('e.g. 11667', 'wp-ultimo'),
			),
			'WU_RUNCLOUD_APP_ID'     => array(
				'title'       => __('RunCloud App ID', 'wp-ultimo'),
				'desc'        => __('The App ID retrieved in the previous step.', 'wp-ultimo'),
				'placeholder' => __('e.g. 940288', 'wp-ultimo'),
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

		$domain_list = array($domain);

		if (strpos($domain, 'www.') !== 0) {

			$domain_list[] = "www.$domain";

		} // end if;

		$success = false;

		foreach ($domain_list as $domain) {

			$response = $this->send_runcloud_request($this->get_runcloud_base_url('domains'), array(
				'name' => $domain
			), 'POST');

			if (is_wp_error($response)) {

				wu_log_add('integration-runcloud', $response->get_error_message());

			} else {

				$success = true; // At least one of the calls was successful;

				wu_log_add('integration-runcloud', wp_remote_retrieve_body($response));

			} // end if;

		} // end foreach;

		/**
		 * Only redeploy SSL if at least one of the domains were successfully added
		 */
		if ($success) {

			$ssl_id = $this->get_runcloud_ssl_id();

			if ($ssl_id) {

				$this->redeploy_runcloud_ssl($ssl_id);

			} // end if;

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

		$domain_list = array($domain);

		if (strpos($domain, 'www.') !== 0) {

			$domain_list[] = "www.$domain";

		} // end if;

		foreach ($domain_list as $domain) {

			$domain_id = $this->get_runcloud_domain_id($domain);

			if (!$domain_id) {

				wu_log_add('integration-runcloud', __('Domain name not found on runcloud', 'wp-ultimo'));

			} // end if;

			$response = $this->send_runcloud_request($this->get_runcloud_base_url("domains/$domain_id"), array(), 'DELETE');

			if (is_wp_error($response)) {

				wu_log_add('integration-runcloud', $response->get_error_message());

			} else {

				wu_log_add('integration-runcloud', wp_remote_retrieve_body($response));

			} // end if;

		} // end foreach;

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
	 * Tests the connection with the RunCloud API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection() {

		$response = $this->send_runcloud_request($this->get_runcloud_base_url('domains'), array(), 'GET');

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {

			wp_send_json_error($response);

		} else {

			wp_send_json_success($this->maybe_return_runcloud_body($response));

		} // end if;

	} // end test_connection;

	/**
	 * Returns the base domain API url to our calls.
	 *
	 * @since 1.7.0
	 * @param string $path Path relative to the main endpoint.
	 * @return string
	 */
	public function get_runcloud_base_url($path = '') {

		$serverid = defined('WU_RUNCLOUD_SERVER_ID') ? WU_RUNCLOUD_SERVER_ID : '';

		$appid = defined('WU_RUNCLOUD_APP_ID') ? WU_RUNCLOUD_APP_ID : '';

		return "https://manage.runcloud.io/api/v2/servers/{$serverid}/webapps/{$appid}/{$path}";

	} // end get_runcloud_base_url;

	/**
	 * Sends the request to a given runcloud URL with a given body.
	 *
	 * @since 1.7.0
	 * @param string $url Endpoinbt to send the request to.
	 * @param array  $data Data to be sent.
	 * @param string $method HTTP Method to send. Defaults to POST.
	 * @return array
	 */
	public function send_runcloud_request($url, $data = array(), $method = 'POST') {

		$username = defined('WU_RUNCLOUD_API_KEY') ? WU_RUNCLOUD_API_KEY : '';

		$password = defined('WU_RUNCLOUD_API_SECRET') ? WU_RUNCLOUD_API_SECRET : '';

		$response = wp_remote_request($url, array(
			'timeout'     => 100,
			'redirection' => 5,
			'body'        => $data,
			'method'      => $method,
			'headers'     => array(
				'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
			),
		));

		return $response;

	} // end send_runcloud_request;

	/**
	 * Treats the response, maybe returning the json decoded version
	 *
	 * @since 1.7.0
	 * @param array $response The response.
	 * @return mixed
	 */
	public function maybe_return_runcloud_body($response) {

		if (is_wp_error($response)) {

			return $response->get_error_message();

		} else {

			return json_decode(wp_remote_retrieve_body($response));

		} // end if;

	} // end maybe_return_runcloud_body;

	/**
	 * Returns the RunCloud.io domain id to remove.
	 *
	 * @since 1.7.0
	 * @param string $domain The domain name being removed.
	 * @return string
	 */
	public function get_runcloud_domain_id($domain) {

		$domains_list = $this->send_runcloud_request($this->get_runcloud_base_url('domains'), array(), 'GET');

		$list = $this->maybe_return_runcloud_body($domains_list);

		if (is_object($list) && !empty($list->data)) {

			foreach ($list->data as $remote_domain) {

				if ($remote_domain->name === $domain) {

					return $remote_domain->id;

				} // end if;

			} // end foreach;

		} // end if;

		return false;

	} // end get_runcloud_domain_id;

	/**
	 * Checks if RunCloud has a SSL cert installed or not, and returns the ID.
	 *
	 * @since 1.10.4
	 * @return bool|int
	 */
	public function get_runcloud_ssl_id() {

		$ssl_id = false;

		$response = $this->send_runcloud_request($this->get_runcloud_base_url('ssl'), array(), 'GET');

		if (is_wp_error($response)) {

			wu_log_add('integration-runcloud', $response->get_error_message());

		} else {

			$data = $this->maybe_return_runcloud_body($response);

			wu_log_add('integration-runcloud', json_encode($data));

			if (property_exists($data, 'id')) {

				$ssl_id = $data->id;

			} // end if;

		} // end if;

		return $ssl_id;

	} // end get_runcloud_ssl_id;

	/**
	 * Redeploys the SSL cert when a new domain is added.
	 *
	 * @since 1.10.4
	 * @param int $ssl_id The SSL id on RunCloud.
	 * @return void
	 */
	public function redeploy_runcloud_ssl($ssl_id) {

		$response = $this->send_runcloud_request($this->get_runcloud_base_url("ssl/$ssl_id"), array(), 'PUT');

		if (is_wp_error($response)) {

			wu_log_add('integration-runcloud', $response->get_error_message());

		} else {

			wu_log_add('integration-runcloud', wp_remote_retrieve_body($response));

		} // end if;

	} // end redeploy_runcloud_ssl;

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_instructions() {

		wu_get_template('wizards/host-integrations/runcloud-instructions');

	} // end get_instructions;

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('With RunCloud, you don’t need to be a Linux expert to build a website powered by DigitalOcean, AWS, or Google Cloud. Use our graphical interface and build a business on the cloud affordably.', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('runcloud.svg', 'img/hosts');

	} // end get_logo;

} // end class Runcloud_Host_Provider;
