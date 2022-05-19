<?php
/**
 * The Session Handler.
 *
 * This class is responsible for setting up the
 * session of the SSO user, and resuming it
 * when the user is redirected back to the original
 * broker site.
 *
 * @package WP_Ultimo
 * @subpackage SSO
 * @since 2.0.11
 */

namespace WP_Ultimo\SSO;

use \WP_Ultimo\Dependencies\Jasny\SSO\Server\SessionInterface;
use \WP_Ultimo\Dependencies\Jasny\SSO\ServerException;
use \WP_Ultimo\SSO\Exception\SSO_Session_Exception;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * The SSO session handler.
 *
 * @since 2.0.11
 */
class SSO_Session_Handler implements SessionInterface {

	/**
	 * The SSO manager instance.
	 *
	 * @since 2.0.11
	 * @var \WP_Ultimo\SSO
	 */
	public $sso_manager;

	/**
	 * Build the handler with the SSO manager.
	 *
	 * @since 2.0.11
	 *
	 * @param \WP_Ultimo\SSO\SSO|null $sso_manager The sso manager.
	 */
	public function __construct(\WP_Ultimo\SSO\SSO $sso_manager = null) {

		$this->sso_manager = $sso_manager;

	} // end __construct;

	/**
	 * Returns the session id.
	 *
	 * @since 2.0.11
	 * @return string
	 */
	public function getId(): string { // phpcs:ignore

		return $this->sso_manager->input('broker');

	} // end getId;

	/**
	 * Start a new session.
	 *
	 * @since 2.0.11
	 *
	 * @throws ServerException If session can't be started.
	 * @throws SSO_Session_Exception If session can't be started.
	 *
	 * @return void
	 */
	public function start(): void { // phpcs:ignore

		$site_hash = $this->sso_manager->input('broker');

		if (!get_current_user_id()) {

			throw new SSO_Session_Exception('User not logged in.', 401);

		} // end if;

		$id = $this->sso_manager->decode($site_hash, $this->sso_manager->salt());

		set_site_transient("sso-{$site_hash}-{$id}", get_current_user_id(), 180);

	} // end start;

	/**
	 * Resume an existing session.
	 *
	 * @since 2.0.11
	 *
	 * @throws ServerException If session can't be started.
	 * @throws BrokerException If session is expired.
	 *
	 * @param string $id The session id.
	 *
	 * @return void
	 */
	public function resume(string $id): void { // phpcs:ignore

		$decoded_id = $this->sso_manager->decode($id, $this->sso_manager->salt());

		$user_id = get_site_transient("sso-{$id}-{$decoded_id}");

		if ($user_id) {

			$this->sso_manager->set_target_user_id($user_id);

		} // end if;

	} // end resume;

	/**
	 * Check if a session is active. (status PHP_SESSION_ACTIVE).
	 *
	 * @see session_status()
	 *
	 * @since 2.0.11
	 * @return boolean
	 */
	public function isActive(): bool { // phpcs:ignore

		return false;

	} // end isActive;

} // end class SSO_Session_Handler;
