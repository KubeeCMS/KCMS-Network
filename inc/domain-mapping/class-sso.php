<?php
/**
 * Handles Single Sign-On
 *
 * @package WP_Ultimo
 * @subpackage Domain_Mapping
 * @since 2.0.0
 */

namespace WP_Ultimo\Domain_Mapping;

use WP_Ultimo\Models\Domain;
use WP_Ultimo\Domain_Mapping\Magic_URL;
use WP_User;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles Single Sign-On
 *
 * @since 2.0.0
 */
class SSO {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Keeps the option name where we save sessions and settings.
	 */
	const OPTION = 'wp_ultimo_sso';

	/**
	 * Hooks the main function to the Domain Mapping load.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		if ($this->is_eligible_request() && $this->is_enabled()) {

			add_action('init', array($this, 'init_server_from_request'));

			add_filter('wu_redirect_to_primary_domain', array($this, 'add_magic'), 10, 2);

		} // end if;

	} // end init;

	/**
	 * Adds SSO magic parameters to primary domains.
	 *
	 * @since 2.0.0
	 *
	 * @param string                   $original_url Original mapped domain.
	 * @param \WP_Ultimo\Models\Domain $mapping The mapping.
	 * @return string
	 */
	public function add_magic($original_url, $mapping) {

		if (!is_user_logged_in()) {

			return $original_url;

		} // end if;

		$endpoint = $this->endpoint();

		$magic = new Magic_URL(wp_get_current_user(), $mapping->get_domain(), $original_url);

		$this->persist_magic_url($magic, $endpoint, 30);

		return $mapping->get_url($endpoint . '/' . $magic->get_key());

	} // end add_magic;

	/**
	 * Checks if we need to handle a SSO magic link
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init_server_from_request() {

		list($endpoint, $public) = SSO_Server::parse_uri(@$_SERVER['REQUEST_URI']); // phpcs:ignore

		SSO_Server::handle($endpoint, $public);

	} // end init_server_from_request;

	/**
	 * Checks if the current request is eligible for SSO Magic Links.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	private function is_eligible_request() {

		return !(
        (defined('WP_CLI') && WP_CLI)                       // ignore cli requests
        || (defined('DOING_AJAX') && DOING_AJAX)            // ignore ajax requests
        || (defined('DOING_CRON') && DOING_CRON)            // ignore cron requests
        || (defined('WP_INSTALLING') && WP_INSTALLING)      // WP ain't ready
        || 'GET' != strtoupper(@$_SERVER['REQUEST_METHOD']) // GET requests only // phpcs:ignore
        || count($_GET) > 0                                 // if there is any query string
        || is_admin()                                       // ignore admin requests
		);

	} // end is_eligible_request;

	/**
	 * Helper method to generate random numbers.
	 *
	 * @since 2.0.0
	 *
	 * @param int $min Min value.
	 * @param int $max Max value.
	 * @return string
	 */
	public static function randomness($min, $max = null) {

		$min = absint($min);
		$max = absint($max ? $max : $min);

		return bin2hex(random_bytes(random_int($min, $max)));

	} // end randomness;

	/**
	 * Reset the saved option with fresh data.
	 *
	 * @since 2.0.0
	 * @return stdClass
	 */
	private function reset_option() {

		$option = array(
			'endpoint' => self::randomness(4),
			'version'  => wu_get_version(),
		);

		update_site_option(static::OPTION, json_encode($option));

		return (object) $option;

	} // end reset_option;

	/**
	 * Create the endpoint if it does not exist, and return the current value.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function endpoint() {

		$saved = json_decode(get_site_option(static::OPTION));

		$version = isset($saved->version) ? $saved->version : false;

		if (!$saved) {

			$saved = $this->reset_option();

		} // end if;

		return $saved->endpoint;

	} // end endpoint;

	/**
	 * Create a magic login URL.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_User $user User to create login URL for.
	 * @param string  $domain Domain to create the magic link for.
	 * @param int     $expires Number of seconds from now until the magic link expires.
	 * @param string  $redirect_url URL to redirect to upon successfully logging in.
	 *
	 * @return string URL
	 */
	private function create_magic_link(WP_User $user, $domain, $expires, $redirect_url) {

		$endpoint = $this->endpoint();

		$magic = new Magic_URL($user, $domain, $redirect_url);

		$this->persist_magic_url($magic, $endpoint, $expires);

		return $this->home_url($endpoint . '/' . $magic->get_key());

	} // end create_magic_link;

	/**
	 * Get the home URL.
	 *
	 * @param string $path The path.
	 *
	 * @return string
	 */
	private function home_url($path = '') {

		$url = home_url($path);

		/**
		 * If the global --url is passed it will set the HTTP_HOST.
		 * Here we replace the hostname in the default home URL with the one set by the command.
		 * This preserves compatibility with sites installed as a subdirectory.
		 */
		if (!empty($_SERVER['HTTP_HOST'])) {

			return preg_replace('#//[^/]+#', '//' . $_SERVER['HTTP_HOST'], $url, 1);

		} // end if;

		return $url;

	} // end home_url;

	/**
	 * Store the Magic Url to be used later.
	 *
	 * @param Magic_URL $magic The magic URL object.
	 * @param string    $endpoint The endpoint.
	 * @param int       $expires The expiration ins econds.
	 */
	private function persist_magic_url(Magic_URL $magic, $endpoint, $expires) {

		set_site_transient(
			self::OPTION . '/' . $magic->get_key(),
			json_encode($magic->generate($endpoint)),
			ceil($expires)
		);

	} // end persist_magic_url;

	/**
	 * Is SSO enabled?
	 *
	 * @return boolean
	 */
	public function is_enabled() {

		$enabled = (bool) wu_get_setting_early('enable_sso');

		if (has_filter('mercator.sso.enabled')) {

			$enabled = apply_filters_deprecated('mercator.sso.enabled', $enabled, '2.0.0', 'wu_sso_enabled');

		} // end if;

		/**
		 * Enable/disable cross-domain single-sign-on capability.
		 *
		 * Filter this value to turn single-sign-on off completely, or conditionally
		 * enable it instead.
		 *
		 * @param bool $enabled Should SSO be enabled? (True for on, false-ish for off.)
		 */
		return apply_filters('wu_sso_enabled', $enabled);

	} // end is_enabled;

} // end class SSO;
