<?php
/**
 * Adds domain mapping and auto SSL support to customer hosting networks on cPanel.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/CPanel_Host_Provider
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers;

use WP_Ultimo\Integrations\Host_Providers\Base_Host_Provider;
use WP_Ultimo\Integrations\Host_Providers\CPanel_API\CPanel_API;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * This base class should be extended to implement new host integrations for SSL and domains.
 */
class CPanel_Host_Provider extends Base_Host_Provider {

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $id = 'cpanel';

	/**
	 * Keeps the title of the integration.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $title = 'cPanel';

	/**
	 * Link to the tutorial teaching how to make this integration work.
	 *
	 * @var string
	 * @since 2.0.0
	 */
	protected $tutorial_link = 'https://help.wpultimo.com/article/295-configuring-automatic-domain-syncing-with-cpanel';

	/**
	 * Array containing the features this integration supports.
	 *
	 * @var array
	 * @since 2.0.0
	 */
	protected $supports = array(
		'autossl',
		'no-instructions',
	);

	/**
	 * Constants that need to be present on wp-config.php for this integration to work.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $constants = array(
		'WU_CPANEL_USERNAME',
		'WU_CPANEL_PASSWORD',
		'WU_CPANEL_HOST',
	);

	/**
	 * Constants that are optional on wp-config.php.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $optional_constants = array(
		'WU_CPANEL_PORT',
		'WU_CPANEL_ROOT_DIR'
	);

	/**
	 * Holds the API object.
	 *
	 * @since 2.0.0
	 * @var WP_Ultimo\Integrations\Host_Providers\CPanel_API\CPanel_API
	 */
	protected $api = null;

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

		return false;

	} // end detect;

	/**
	 * Returns the list of installation fields.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_fields() {

		return array(
			'WU_CPANEL_USERNAME' => array(
				'title'       => __('cPanel Username', 'wp-ultimo'),
				'placeholder' => __('e.g. username', 'wp-ultimo'),
			),
			'WU_CPANEL_PASSWORD' => array(
				'type'        => 'password',
				'title'       => __('cPanel Password', 'wp-ultimo'),
				'placeholder' => __('password', 'wp-ultimo'),
			),
			'WU_CPANEL_HOST'     => array(
				'title'       => __('cPanel Host', 'wp-ultimo'),
				'placeholder' => __('e.g. yourdomain.com', 'wp-ultimo'),
			),
			'WU_CPANEL_PORT'     => array(
				'title'       => __('cPanel Port', 'wp-ultimo'),
				'placeholder' => __('Defaults to 2083', 'wp-ultimo'),
				'value'       => 2083,
			),
			'WU_CPANEL_ROOT_DIR' => array(
				'title'       => __('Root Directory', 'wp-ultimo'),
				'placeholder' => __('Defaults to /public_html', 'wp-ultimo'),
				'value'       => '/public_html',
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

		// Root Directory
		$root_dir = defined('WU_CPANEL_ROOT_DIR') && WU_CPANEL_ROOT_DIR ? WU_CPANEL_ROOT_DIR : '/public_html';

		// Send Request
		$results = $this->load_api()->api2('AddonDomain', 'addaddondomain', array(
			'dir'       => $root_dir,
			'newdomain' => $domain,
			'subdomain' => $this->get_subdomain($domain),
		));

		$this->log_calls($results);

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

		// Send Request
		$results = $this->load_api()->api2('AddonDomain', 'deladdondomain', array(
			'domain'    => $domain,
			'subdomain' => $this->get_subdomain($domain) . '_' . $this->get_site_url(),
		));

		$this->log_calls($results);

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

		// Root Directory
		$root_dir = defined('WU_CPANEL_ROOT_DIR') && WU_CPANEL_ROOT_DIR ? WU_CPANEL_ROOT_DIR : '/public_html';

		$subdomain = $this->get_subdomain($subdomain, false);

		$rootdomain = str_replace($subdomain . '.', '', $this->get_site_url($site_id));

		// Send Request
		$results = $this->load_api()->api2('SubDomain', 'addsubdomain', array(
			'dir'        => $root_dir,
			'domain'     => $subdomain,
			'rootdomain' => $rootdomain,
		));

		// Check the results
		$this->log_calls($results);

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
	 * Load the CPanel API.
	 *
	 * @since 2.0.0
	 * @return WU_CPanel
	 */
	public function load_api() {

		if ($this->api === null) {

			$username = defined('WU_CPANEL_USERNAME') ? WU_CPANEL_USERNAME : '';
			$password = defined('WU_CPANEL_PASSWORD') ? WU_CPANEL_PASSWORD : '';
			$host     = defined('WU_CPANEL_HOST') ? WU_CPANEL_HOST : '';
			$port     = defined('WU_CPANEL_PORT') && WU_CPANEL_PORT ? WU_CPANEL_PORT : 2083;

			/*
			 * Set up the API.
			 */
			$this->api = new CPanel_API($username, $password, preg_replace('#^https?://#', '', $host), $port);

		} // end if;

		return $this->api;

	} // end load_api;

	/**
	 * Returns the Site URL.
	 *
	 * @since  1.6.2
	 * @param null|int $site_id The site id.
	 * @return string
	 */
	public function get_site_url($site_id = null) {

		return trim(preg_replace('#^https?://#', '', get_site_url($site_id)), '/');

	} // end get_site_url;

	/**
	 * Returns the sub-domain version of the domain.
	 *
	 * @since 1.6.2
	 * @param string $domain The domain to be used.
	 * @param string $mapped_domain If this is a mapped domain.
	 * @return string
	 */
	public function get_subdomain($domain, $mapped_domain = true) {

		if ($mapped_domain === false) {

			$domain_parts = explode('.', $domain);

			return array_shift($domain_parts);

		} // end if;

		$subdomain = str_replace(array('.', '/'), '', $domain);

		return $subdomain;

	} // end get_subdomain;

	/**
	 * Logs the results of the calls for debugging purposes
	 *
	 * @since 1.6.2
	 * @param object $results Results of the cPanel call.
	 * @return bool
	 */
	public function log_calls($results) {

		if (is_object($results->cpanelresult->data)) {

			return wu_log_add('integration-cpanel', $results->cpanelresult->data->reason);

		} elseif (!isset($results->cpanelresult->data[0])) {

			return wu_log_add('integration-cpanel', __('Unexpected error ocurred trying to sync domains with CPanel', 'wp-ultimo'));

		} // end if;

		return wu_log_add('integration-cpanel', $results->cpanelresult->data[0]->reason);

	} // end log_calls;

	/**
	 * Returns the description of this integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_description() {

		return __('cPanel is the management panel being used on a large number of shared and dedicated hosts across the globe.', 'wp-ultimo');

	} // end get_description;

	/**
	 * Returns the logo for the integration.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_logo() {

		return wu_get_asset('cpanel.svg', 'img/hosts');

	} // end get_logo;

	/**
	 * Tests the connection with the Cloudflare API.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function test_connection() {

		$results = $this->load_api()->api2('Cron', 'fetchcron', array());

		$this->log_calls($results);

		if (isset($results->cpanelresult->data) && !isset($results->cpanelresult->error)) {

			wp_send_json_success($results);

			exit;

		} // end if;

		wp_send_json_error($results);

	} // end test_connection;

	/**
	 * Returns the explainer lines for the integration.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_explainer_lines() {

		$explainer_lines = array(
			'will'     => array(
				'send_domains' => __('Add a new Addon Domain on cPanel whenever a new domain mapping gets created on your network', 'wp-ultimo'),
			),
			'will_not' => array(),
		);

		if (is_subdomain_install()) {

			$explainer_lines['will']['send_sub_domains'] = __('Add a new SubDomain on cPanel whenever a new site gets created on your network', 'wp-ultimo');

		} // end if;

		return $explainer_lines;

	} // end get_explainer_lines;

} // end class CPanel_Host_Provider;
