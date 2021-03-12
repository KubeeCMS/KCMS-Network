<?php
/**
 * Hanled the validation of SSO Magic links.
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

use stdClass;
use WP_User;
use Exception;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles the validation and processing of magic links.
 *
 * @since 2.0.0
 */
class SSO_Server {

	/**
	 * Option key for the persisted-data.
	 */
	const OPTION = 'wp_ultimo_sso';

	/**
	 * The endpoint for the magic link.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $endpoint;

	/**
	 * Public key passed to identify the unique magic login.
	 *
	 * @var string
	 */
	private $public_key;

	/**
	 * Set up the handler.
	 *
	 * @param string $endpoint The endpoint.
	 * @param string $public_key The public key.
	 */
	public function __construct($endpoint, $public_key) {

		$this->endpoint   = $endpoint;
		$this->public_key = $public_key;

	} // end __construct;

	/**
	 * Parse the endpoint & key from the URI, if any.
	 *
	 * @since 2.0.0
	 *
	 * @param string $uri string Request URI.
	 * @return array [endpoint, key]
	 */
	public static function parse_uri($uri) {

		return array_slice(array_merge(array('', ''), explode('/', $uri)), -2);

	} // end parse_uri;

	/**
	 * Handle a new magic login request.
	 *
	 * @param string $endpoint The endpoint.
	 * @param string $public_key The public key.
	 */
	public static function handle($endpoint, $public_key) {

		$server = new static($endpoint, $public_key);

		if ($server->check_endpoint()) {

			$server->run();

		} // end if;

	} // end handle;

	/**
	 * Test if endpoints match.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function check_endpoint() {

		$saved = json_decode(get_site_option(static::OPTION));

		if ($saved) {

			return $this->endpoint === $saved->endpoint;

		} // end if;

		return false;

	} // end check_endpoint;

	/**
	 * Attempts to login.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function run() {

		try {

			$magic = $this->load_magic();
			$user  = $this->validate($magic);
			$this->login_user($user);

		} catch (Exception $e) {

			$this->abort($e);

		} // end try;

	} // end run;

	/**
	 * Validate the magic login, and return the user to login if successful.
	 *
	 * @since 2.0.0
	 * @param stdClass $magic The magic link object.
	 *
	 * @throws AuthenticationFailure When the keys do not match.
	 * @throws InvalidUser When the user is not found or is invalid.
	 * @return WP_User
	 */
	private function validate(stdClass $magic) {

		$user = new WP_User($magic->user);

		if (empty($magic->user) || (!$user) || !$user->exists()) {

			throw new InvalidUser('No user found or no longer exists.');

		} // end if;

		if (empty($magic->private) || !wp_check_password($this->signature($user), $magic->private)) {

			throw new AuthenticationFailure('Magic login authentication failed.');

		} // end if;

		return $user;

	} // end validate;

	/**
	 * Load the saved data for the current magic login.
	 *
	 * @since 2.0.0
	 * @throws BadMagic When the link is no longet valid.
	 *
	 * @return stdClass
	 */
	private function load_magic() {

		$magic = json_decode(get_site_transient($this->magic_key()));

		if (is_null($magic)) {

			throw new BadMagic('The attempted magic login has expired or already been used.');

		} // end if;

		return $magic;

	} // end load_magic;

	/**
	 * Login the given user and redirect them to wp-admin.
	 *
	 * @since 2.0.0
	 * @param WP_User $user The user to login.
	 * @return void
	 */
	private function login_user(WP_User $user) {

		delete_transient($this->magic_key());

		wp_set_auth_cookie($user->ID);

		/**
		 * Fires after the user has successfully logged in via SSO Magic Links
		 *
		 * @param string  $user_login Username.
		 * @param WP_User $user       WP_User object of the logged-in user.
		 */
		do_action('wu_sso_login', $user->user_login, $user);

		/**
		 * Fires after the user has successfully logged in.
		 *
		 * @param string  $user_login Username.
		 * @param WP_User $user       WP_User object of the logged-in user.
		 */
		do_action('wp_login', $user->user_login, $user); // phpcs:ignore

		$this->login_redirect($user);

	} // end login_user;

	/**
	 * Redirect the user after logging in.
	 *
	 * Mostly copied from wp-login.php
	 *
	 * @since 2.0.0
	 * @param WP_User $user The user object.
	 * @return void.
	 */
	private function login_redirect(WP_User $user) {

		/**
		 * Filters the login redirect URL.
		 *
		 * @param string           $redirect_to           The redirect destination URL.
		 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
		 * @param WP_User          $user                  WP_User object.
		 */
		$redirect_to = apply_filters('login_redirect', home_url(), '', $user); // phpcs:ignore

		/**
		 * Filters the login redirect URL for WP-CLI Login Server requests.
		 *
		 * @param string           $redirect_to           The redirect destination URL.
		 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
		 * @param WP_User          $user                  WP_User object.
		 */
		$redirect_to = apply_filters('wu_sso_login_redirect', $redirect_to, '', $user);

		/*
		 * Figure out where to redirect the user for the default wp-admin URL based on the user's capabilities.
		 */
		if ((empty($redirect_to) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url())) {
			/*
			 * If the user doesn't belong to a blog, send them to user admin.
			 * If the user can't edit posts, send them to their profile.
			 */
			if (is_multisite() && !get_active_blog_for_user($user->ID) && !is_super_admin($user->ID)) {

				$redirect_to = user_admin_url();

			} elseif (is_multisite() && !$user->has_cap('read')) {

				$redirect_to = get_dashboard_url($user->ID);

			} elseif (!$user->has_cap('edit_posts')) {

				$redirect_to = $user->has_cap('read') ? admin_url('profile.php') : home_url();

			} // end if;

			wp_redirect($redirect_to);

			exit;

		} // end if;

		/**
		 * Redirect safely to the URL provided.
		 */
		wp_safe_redirect($redirect_to);

		exit;

	} // end login_redirect;

	/**
	 * Abort the process; Explode with terrifying message.
	 *
	 * @since 2.0.0
	 * @param Exception $e The exception thrown.
	 * @return void
	 */
	private function abort(Exception $e) {

		$exception_message = $e->getMessage();

		$common = sprintf('Try again perhaps? or <a href="%s">Go Home &rarr;</a>', esc_url(home_url()));

		$message = "<strong>$exception_message</strong><p>$common</p>";

		wp_die($message, __('SSO Error', 'wp-ultimo'), array('response' => 410));

	} // end abort;

	/**
	 * Get the key for retrieving magic data for the current request.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function magic_key() {

		return self::OPTION . '/' . $this->public_key;

	} // end magic_key;

	/**
	 * Build the signature to check against the private key for this request.
	 *
	 * @since 2.0.0
	 * @param WP_User $user The user object.
	 *
	 * @return string
	 */
	private function signature(WP_User $user) {

		return join('|', array(
			$this->public_key,
			$this->endpoint,
			parse_url($this->home_url(), PHP_URL_HOST),
			$user->ID,
		));

	} // end signature;

	/**
	 * Gets the home url.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function home_url() {

  	/* wp-cli server-command filters home & siteurl to work and saves the original in a global. */
		return isset($GLOBALS['_wp_cli_original_url'])
		? $GLOBALS['_wp_cli_original_url']
		: home_url();

	} // end home_url;

} // end class SSO_Server;

/**
 * Extends default exception.
 *
 * @since 2.0.0
 */
class BadMagic extends Exception {} // end class BadMagic;

/**
 * Extends default exception.
 *
 * @since 2.0.0
 */
class AuthenticationFailure extends Exception {} // end class AuthenticationFailure;

/**
 * Extends default exception.
 *
 * @since 2.0.0
 */
class InvalidUser extends Exception {} // end class InvalidUser;
