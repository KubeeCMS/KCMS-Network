<?php
/**
 * The broker class.
 *
 * The broker implementation has a bug on the
 * method used to generate the attach url.
 *
 * @package WP_Ultimo
 * @subpackage SSO
 * @since 2.0.11
 */

namespace WP_Ultimo\SSO;

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Dependencies\Jasny\SSO\Broker\Broker;

/**
 * The SSO Broker implementation.
 *
 * @since 2.0.11
 */
class SSO_Broker extends Broker {

	/**
	 * Checks if the current SSO call is a must-redirect call.
	 *
	 * @since 2.0.11
	 * @return boolean
	 */
	public function is_must_redirect_call() {

		return $this->getVerificationCode() === 'must-redirect';

	} // end is_must_redirect_call;

	/**
	 * Get URL to attach session at SSO server.
	 *
	 * @param array<string,mixed> $params The params to be passed.
	 * @return string
	 */
	public function getAttachUrl(array $params = array()) : string { // phpcs:ignore

		if ($this->getToken() === null) {

			$this->generateToken();

		} // end if;

		$data = array(
			'broker'   => $this->broker,
			'token'    => $this->getToken(),
			'checksum' => $this->generateChecksum('attach')
		);

		return add_query_arg($data + $params, $this->url);

	} // end getAttachUrl;

} // end class SSO_Broker;
