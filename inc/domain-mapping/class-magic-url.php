<?php
/**
 * Hanled the creation of SSO Magic links.
 *
 * This is an adapted version of the work done on the WP_CLI
 * magic link plugin on https://aaemnnost.tv/wp-cli-commands/login/
 *
 * @see https://aaemnnost.tv/wp-cli-commands/login/
 *
 * @package WP_Ultimo
 * @subpackage Domain_Mapping
 * @since 2.0.0
 */

namespace WP_Ultimo\Domain_Mapping;

use WP_User;
use WP_Ultimo\Domain_Mapping\SSO;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles Magic Link creation.
 *
 * @since 2.0.0
 */
class Magic_URL {

	/**
	 * Public key to check the login.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $key;

	/**
	 * WP User object for the user we want to log in as.
	 *
	 * @since 2.0.0
	 * @var \WP_User
	 */
	private $user;

	/**
	 * Domain name of the site being accessed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $domain;

	/**
	 * URL to redirect to upon successful login.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $redirect_url;

	/**
	 * MagicUrl constructor.
	 *
	 * @param WP_User $user The user object.
	 * @param string  $domain The domain.
	 * @param string  $redirect_url The redirect url.
	 */
	public function __construct(WP_User $user, $domain, $redirect_url = null) {

		$this->user         = $user;
		$this->domain       = $domain;
		$this->key          = $this->new_public_key();
		$this->redirect_url = $redirect_url;

	} // end __construct;

	/**
	 * Get the public key.
	 *
	 * @return string
	 */
	public function get_key() {

		return $this->key;

	} // end get_key;

	/**
	 * Generate a new magic URL for the given endpoint.
	 *
	 * @since 2.0.0
	 *
	 * @param string $endpoint The endpoint.
	 * @return array
	 */
	public function generate($endpoint) {

		return array(
			'user'         => $this->user->ID,
			'private'      => wp_hash_password($this->signature($endpoint)),
			'redirect_url' => $this->redirect_url,
			'time'         => time(),
		);

	} // end generate;

	/**
	 * Build the signature for the given endpoint.
	 *
	 * @since 2.0.0
	 * @param string $endpoint The endpoint.
	 * @return string
	 */
	private function signature($endpoint) {

		return join('|', array(
			$this->key,
			$endpoint,
			$this->domain,
			$this->user->ID,
		));

	} // end signature;

	/**
	 * Generate a new cryptographically sound public key.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function new_public_key() {

		return implode('-', array(
			SSO::randomness(3, 5),
			SSO::randomness(3, 5),
			SSO::randomness(3, 5),
		));

	} // end new_public_key;

} // end class Magic_URL;
