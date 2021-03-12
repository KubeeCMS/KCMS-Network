<?php
/**
 * Helper class for domain mapping functionality.
 *
 * @package WP_Ultimo
 * @subpackage Domain_Mapping
 * @since 2.0.0
 */

namespace WP_Ultimo\Domain_Mapping;

use WP_Ultimo\Dependencies\Spatie\SslCertificate\SslCertificate;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Helper class for domain mapping functionality.
 *
 * @since 2.0.0
 */
class Helper {

	/**
	 * List of API endpoints we can use to check the remote IP address.
	 *
	 * @var array
	 */
	static $providers = array(
		'https://ipv4.canihazip.com/s',
		'https://ipv4.icanhazip.com/',
		'https://api.ipify.org/',
	);

	/**
	 * Static-only class.
	 */
	private function __construct() {} // end __construct;

	/**
	 * Checks if we are in development mode.
	 *
	 * @todo this needs to be migrate somewhere else, where it can be accessed by everyone.
	 * @since 2.0.0
	 * @return boolean
	 */
	public static function is_development_mode() {

		return preg_match('#(localhost|staging.*\.|\.local|\.dev)#', site_url());

	} // end is_development_mode;

	/**
	 * Gets the local IP address of the network.
	 *
	 * Sometimes, this will be the same address as the public one, but we need different methods.
	 *
	 * @since 2.0.0
	 * @return string|boolean
	 */
	public static function get_local_network_ip() {

		return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : false;

	} // end get_local_network_ip;

	/**
	 * Gets the public IP address of the network using an external HTTP call.
	 *
	 * The reason why this IP can't be determined locally is because proxies like
	 * Cloudflare and others will mask the real domain address.
	 * By default, we cache the values in a transient for 10 days.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public static function get_network_public_ip() {

		if (self::is_development_mode()) {

			return self::get_local_network_ip();

		} // end if;

		$_ip_address = get_site_transient('wu_public_network_ip');

		if (!$_ip_address) {

			$ip_address = false;

			foreach (self::$providers as $provider_url) {

				$response = wp_remote_get($provider_url, array(
					'timeout' => 5,
				));

				if (!is_wp_error($response)) {

					$ip_address = trim(wp_remote_retrieve_body($response));

					continue;

				} // end if;

			} // end foreach;

			set_site_transient('wu_public_network_ip', $ip_address, 10 * DAY_IN_SECONDS);

			$_ip_address = $ip_address;

		} // end if;

		return $_ip_address;

	} // end get_network_public_ip;

	/**
	 * Checks if a given domain name has a valid associated SSL certificate.
	 *
	 * @since 2.0.0
	 *
	 * @param string $domain Domain name, e.g. google.com.
	 * @return boolean
	 */
	public static function has_valid_ssl_certificate($domain = '') {

		$is_valid = false;

		try {

			$certificate = SslCertificate::createForHostName($domain);

			$is_valid = $certificate->isValid($domain); // returns bool;

		} catch (\Exception $e) {

			// translators: %s is the error message returned by the checker.
			wu_log_add('domain-ssl-checks', sprintf(__('Certificate Invalid: %s', 'wp-ultimo'), $e->getMessage()));

		} // end try;

		return $is_valid;

	} // end has_valid_ssl_certificate;

} // end class Helper;
