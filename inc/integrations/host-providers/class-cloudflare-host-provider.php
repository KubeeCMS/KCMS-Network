<?php
/**
 * Adds domain mapping and auto SSL support to customer using Cloudflare.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/Cloudflare_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class Cloudflare_Host_Provider extends Base_Host_Provider {

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'cloudflare';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'Cloudflare';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = '#';

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
		'WU_CLOUDFLARE_API_KEY',
		'WU_CLOUDFLARE_ZONE_ID',
	);

	/**
	 * Add Cloudflare own DNS entries to the comparison table.
	 *
	 * @since 2.0.4
	 *
	 * @param array  $dns_records List of current dns records.
	 * @param string $domain The domain name.
	 * @return array
	 */
	public function add_cloudflare_dns_entries($dns_records, $domain) {

		$zone_ids = array();

		$default_zone_id = defined('WU_CLOUDFLARE_ZONE_ID') && WU_CLOUDFLARE_ZONE_ID ? WU_CLOUDFLARE_ZONE_ID : false;

		if ($default_zone_id) {

			$zone_ids[] = $default_zone_id;

		} // end if;

		$cloudflare_zones = $this->cloudflare_api_call('client/v4/zones', 'GET', array(
			'name'   => $domain,
			'status' => 'active',
		));

		foreach ($cloudflare_zones->result as $zone) {

			$zone_ids[] = $zone->id;

		} // end foreach;

		foreach ($zone_ids as $zone_id) {

			/**
			 * First, try to detect the domain as a proxied on the current zone,
			 * if applicable
			 */
			$dns_entries = $this->cloudflare_api_call("client/v4/zones/$zone_id/dns_records/", 'GET', array(
				'name'  => $domain,
				'match' => 'any',
				'type'  => 'A,AAAA,CNAME',
			));

			if (!empty($dns_entries->result)) {

				$proxied_tag = sprintf('<span class="wu-bg-orange-500 wu-text-white wu-p-1 wu-rounded wu-text-3xs wu-uppercase wu-ml-2 wu-font-bold" %s>%s</span>', wu_tooltip_text(__('Proxied', 'wp-ultimo')), __('Cloudflare', 'wp-ultimo'));

				$not_proxied_tag = sprintf('<span class="wu-bg-gray-700 wu-text-white wu-p-1 wu-rounded wu-text-3xs wu-uppercase wu-ml-2 wu-font-bold" %s>%s</span>', wu_tooltip_text(__('Not Proxied', 'wp-ultimo')), __('Cloudflare', 'wp-ultimo'));

				foreach ($dns_entries->result as $entry) {

					$dns_records[] = array(
						'ttl'  => $entry->ttl,
						'data' => $entry->content,
						'type' => $entry->type,
						'host' => $entry->name,
						'tag'  => $entry->proxied ? $proxied_tag : $not_proxied_tag,
					);

				} // end foreach;

			} // end if;

		} // end foreach;

		return $dns_records;

	} // end add_cloudflare_dns_entries;

	/**
	 * Picks up on tips that a given host provider is being used.
	 *
	 * We use this to suggest that the user should activate an integration module.
	 * Unfortunately, we don't have a good method of detecting if someone is running from cPanel.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function detect() {

		if (!function_exists('getallheaders')) {

			return false;

		} // end if;

		$headers = getallheaders();

		return wu_get_isset($headers, 'Cf-Ray', false) !== false;

	} // end detect;

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return array(
			'WU_CLOUDFLARE_ZONE_ID' => array(
				'title'       => __('Zone ID', 'wp-ultimo'),
				'placeholder' => __('e.g. 644c7705723d62e31f700bb798219c75', 'wp-ultimo'),
			),
			'WU_CLOUDFLARE_API_KEY' => array(
				'title'       => __('API Key', 'wp-ultimo'),
				'placeholder' => __('e.g. xKGbxxVDpdcUv9dUzRf4i4ngv0QNf1wCtbehiec_o', 'wp-ultimo'),
			),
		);

	} // end get_fields;

	/**
	 * Tests the connection with the Cloudflare API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection() {

		$results = $this->cloudflare_api_call('client/v4/user/tokens/verify');

		if (is_wp_error($results)) {

			wp_send_json_error($results);

		} // end if;

		wp_send_json_success($results);

	} // end test_connection;

	/**
	 * Lets integrations add additional hooks.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function additional_hooks() {

		add_filter('wu_domain_dns_get_record', array($this, 'add_cloudflare_dns_entries'), 10, 2);

	} // end additional_hooks;

	/**
	 * This method gets called when a new domain is mapped.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being mapped.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_add_domain($domain, $site_id) {} // end on_add_domain;

	/**
	 * This method gets called when a mapped domain is removed.
	 *
	 * @since 2.0.0
	 * @param string $domain The domain name being removed.
	 * @param int    $site_id ID of the site that is receiving that mapping.
	 * @return void
	 */
	public function on_remove_domain($domain, $site_id) {} // end on_remove_domain;

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

		global $current_site;

		$zone_id = defined('WU_CLOUDFLARE_ZONE_ID') && WU_CLOUDFLARE_ZONE_ID ? WU_CLOUDFLARE_ZONE_ID : '';

		if (!$zone_id) {

			return;

		} // end if;

		if (strpos($subdomain, $current_site->domain) === false) {

			return; // Not a sub-domain of the main domain.

		} // end if;

		$subdomain = rtrim(str_replace($current_site->domain, '', $subdomain), '.');

		if (!$subdomain) {

			return;

		} // end if;

		$should_add_www = apply_filters('wu_cloudflare_should_add_www', true, $subdomain, $site_id);

		$domains_to_send = array($subdomain);

		/**
		 * Adds the www version, if necessary.
		 */
		if (strpos($subdomain, 'www.') !== 0 && $should_add_www) {

			$domains_to_send[] = 'www.' . $subdomain;

		} // end if;

		foreach ($domains_to_send as $subdomain) {

			$should_proxy = apply_filters('wu_cloudflare_should_proxy', true, $subdomain, $site_id);

			$data = apply_filters('wu_cloudflare_on_add_domain_data', array(
				'type'    => 'CNAME',
				'name'    => $subdomain,
				'content' => '@',
				'proxied' => $should_proxy,
				'ttl'     => 1,
			), $subdomain, $site_id);

			$results = $this->cloudflare_api_call("client/v4/zones/$zone_id/dns_records/", 'POST', $data);

			if (is_wp_error($results)) {

				wu_log_add('integration-cloudflare', sprintf('Failed to add subdomain "%s" to Cloudflare. Reason: %s', $subdomain, $results->get_error_message()));

				return;

			} // end if;

			wu_log_add('integration-cloudflare', sprintf('Added sub-domain "%s" to Cloudflare.', $subdomain));

		} // end foreach;

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
	public function on_remove_subdomain($subdomain, $site_id) {

		global $current_site;

		$zone_id = defined('WU_CLOUDFLARE_ZONE_ID') && WU_CLOUDFLARE_ZONE_ID ? WU_CLOUDFLARE_ZONE_ID : '';

		if (!$zone_id) {

			return;

		} // end if;

		if (strpos($subdomain, $current_site->domain) === false) {

			return; // Not a sub-domain of the main domain.

		} // end if;

		$original_subdomain = $subdomain;

		$subdomain = rtrim(str_replace($current_site->domain, '', $subdomain), '.');

		if (!$subdomain) {

			return;

		} // end if;

		/**
		 * Created the list that we should remove.
		 */
		$domains_to_remove = array(
			$original_subdomain,
			'www.' . $original_subdomain,
		);

		foreach ($domains_to_remove as $original_subdomain) {

			$dns_entries = $this->cloudflare_api_call("client/v4/zones/$zone_id/dns_records/", 'GET', array(
				'name' => $original_subdomain,
				'type' => 'CNAME',
			));

			if (!$dns_entries->result) {

				return;

			} // end if;

			$dns_entry_to_remove = $dns_entries->result[0];

			$results = $this->cloudflare_api_call("client/v4/zones/$zone_id/dns_records/$dns_entry_to_remove->id", 'DELETE');

			if (is_wp_error($results)) {

				wu_log_add('integration-cloudflare', sprintf('Failed to remove subdomain "%s" to Cloudflare. Reason: %s', $subdomain, $results->get_error_message()));

				return;

			} // end if;

			wu_log_add('integration-cloudflare', sprintf('Removed sub-domain "%s" to Cloudflare.', $subdomain));

		} // end foreach;

	} // end on_remove_subdomain;

	/**
	 * Sends an API call to Cloudflare.
	 *
	 * @since 2.0.0
	 *
	 * @param string $endpoint The endpoint to call.
	 * @param string $method The HTTP verb. Defaults to GET.
	 * @param array  $data The date to send.
	 * @return object|\WP_Error
	 */
	protected function cloudflare_api_call($endpoint = 'client/v4/user/tokens/verify', $method = 'GET', $data = array()) {

		$api_url = 'https://api.cloudflare.com/';

		$endpoint_url = $api_url . $endpoint;

		$response = wp_remote_request($endpoint_url, array(
			'method'      => $method,
			'body'        => $method === 'GET' ? $data : wp_json_encode($data),
			'data_format' => 'body',
			'headers'     => array(
				'Authorization' => sprintf('Bearer %s', defined('WU_CLOUDFLARE_API_KEY') ? WU_CLOUDFLARE_API_KEY : ''),
				'Content-Type'  => 'application/json',
			),
		));

		if (!is_wp_error($response)) {

			$body = wp_remote_retrieve_body($response);

			if (wp_remote_retrieve_response_code($response) === 200) {

				return json_decode($body);

			} else {

				$error_message = wp_remote_retrieve_response_message($response);

				$response = new \WP_Error('cloudflare-error', sprintf('%s: %s', $error_message, $body));

			} // end if;

		} // end if;

		return $response;

	} // end cloudflare_api_call;

	/**
	 * Renders the instructions content.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function get_instructions() {

		wu_get_template('wizards/host-integrations/cloudflare-instructions');

	} // end get_instructions;

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('Cloudflare secures and ensures the reliability of your external-facing resources such as websites, APIs, and applications. It protects your internal resources such as behind-the-firewall applications, teams, and devices. And it is your platform for developing globally-scalable applications.', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('cloudflare.svg', 'img/hosts');

	} // end get_logo;

	/**
	 * Returns the explainer lines for the integration.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_explainer_lines() {

		$explainer_lines = array(
			'will'     => array(),
			'will_not' => array(),
		);

		if (is_subdomain_install()) {

			$explainer_lines['will']['send_sub_domains'] = __('Add a new proxied subdomain to the configured CloudFlare zone whenever a new site gets created', 'wp-ultimo');

		} else {

			$explainer_lines['will']['subdirectory'] = __('Do nothing! The CloudFlare integration has no effect in subdirectory multisite installs such as this one', 'wp-ultimo');

		} // end if;

		$explainer_lines['will_not']['send_domain'] = __('Add domain mappings as new CloudFlare zones', 'wp-ultimo');

		return $explainer_lines;

	} // end get_explainer_lines;

} // end class Cloudflare_Host_Provider;
