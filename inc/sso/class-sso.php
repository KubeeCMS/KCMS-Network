<?php
/**
 * Handles Single Sign-On.
 *
 * This implementation tries to be detached
 * from the domain mapping and to be as flexible
 * as possible, in order to properly work with
 * WordPress native domain mapping, as well as
 * our Domain Mapping implementation.
 *
 * @package WP_Ultimo
 * @subpackage SSO
 * @since 2.0.11
 */

namespace WP_Ultimo\SSO;

use \WP_Ultimo\Helpers\Hash;
use \WP_Ultimo\Dependencies\Jasny\SSO\Server\Server;
use \WP_Ultimo\Dependencies\Jasny\SSO\Server\ServerException;
use \WP_Ultimo\Dependencies\Jasny\SSO\Server\BrokerException;
use \WP_Ultimo\Dependencies\Jasny\SSO\Broker\NotAttachedException;
use \WP_Ultimo\Dependencies\Symfony\Component\Cache\Adapter\FilesystemAdapter;
use \WP_Ultimo\Dependencies\Symfony\Component\Cache\Psr16Cache;
use \WP_Ultimo\Dependencies\Nyholm\Psr7\Factory\Psr17Factory;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles Sign-sign on.
 *
 * @since 2.0.11
 */
class SSO {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * The cache system for sessions.
	 *
	 * @since 2.0.11
	 * @var Psr16Cache
	 */
	protected $cache;

	/**
	 * The logger class to be used.
	 *
	 * @since 2.0.11
	 * @var \WP_Ultimo\Logger
	 */
	protected $logger;

	/**
	 * The target of the SSO user id.
	 *
	 * @since 2.0.11
	 * @var int|null
	 */
	protected $target_user_id;

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function init() {

		$this->is_enabled() && $this->startup();

	} // end init;

	/**
	 * Returns the status of SSO.
	 *
	 * @since 2.0.11
	 * @return boolean
	 */
	public function is_enabled() {

		$enabled = $this->get_setting('enable_sso', true);

		if (has_filter('mercator.sso.enabled')) {

			$enabled = apply_filters_deprecated('mercator.sso.enabled', $enabled, '2.0.0', 'wu_sso_enabled');

		} // end if;

		/**
		 * Enable/disable cross-domain single-sign-on capability.
		 *
		 * Filter this value to turn single-sign-on off completely, or conditionally
		 * enable it instead.
		 *
		 * @since 2.0.11
		 * @param bool $enabled Should SSO be enabled? True for on, false-ish for off.
		 * @return bool If SSO is enabled or not.
		 */
		return apply_filters('wu_sso_enabled', $enabled);

	} // end is_enabled;

	/**
	 * Encode a given string.
	 *
	 * @since 2.0.11
	 *
	 * @param string $content The content to be encoded.
	 * @param string $salt The salt string to be used.
	 * @return string The hashed content.
	 */
	public function encode($content, $salt) {

		return Hash::encode($content, $salt);

	} // end encode;

	/**
	 * Decode a given string.
	 *
	 * @since 2.0.11
	 *
	 * @param string $hash Hashed content to be decoded.
	 * @param string $salt The salt string to be used.
	 * @return string The original content.
	 */
	public function decode($hash, $salt) {

		return Hash::decode($hash, $salt);

	} // end decode;

	/**
	 * Get the current url.
	 *
	 * @since 2.0.11
	 * @return string
	 */
	public function get_current_url() {

		return wu_get_current_url();

	} // end get_current_url;

	/**
	 * Returns the content of a key inside the $_REQUEST array.
	 *
	 * @since 2.0.11
	 *
	 * @param string $key The key to retrieve.
	 * @param mixed  $default The default content.
	 * @return mixed
	 */
	public function input($key, $default = false) {

		return wu_request($key, $default);

	} // end input;

	/**
	 * Returns the content of a array key, if it exists.
	 *
	 * @since 2.0.11
	 *
	 * @param array  $array The array to check.
	 * @param string $key The key to test and return.
	 * @param mixed  $default The default content to return.
	 * @return mixed
	 */
	public function get_isset($array, $key, $default = false) {

		return wu_get_isset($array, $key, $default);

	} // end get_isset;

	/**
	 * Get settings and preferences.
	 *
	 * @since 2.0.11
	 *
	 * @param string $key The setting to retrieve.
	 * @param mixed  $default The default value to return, if no setting is found.
	 * @return mixed
	 */
	public function get_setting($key, $default = false) {

		return wu_get_setting($key, $default);

	} // end get_setting;

	/**
	 * Startup the SSO hooks and filters.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function startup() {

		/**
		 * Loads the modified auth functions we need
		 * in order to make SSO work.
		 */
		require_once wu_path('inc/sso/auth-functions.php');

		/**
		 * Modifying default WordPress behavior.
		 *
		 * The filters below make some changes to WordPress
		 * default behaviors that allows us to make SSO work.
		 *
		 * Force the login in cookie to be secure, even if the
		 * url does is not https on the database, as WordPress does.
		 *
		 * Uses the modified version of the auth_redirect function
		 * to prevent the redirect if we are in the middle of a SSO
		 * authentication flow.
		 *
		 * @see https://developer.wordpress.org/reference/functions/auth_redirect/
		 * @see https://developer.wordpress.org/reference/functions/wp_set_auth_cookie/
		 */
		add_filter('secure_logged_in_cookie', array($this, 'force_secure_login_cookie'));

		add_filter('wu_auth_redirect', array($this, 'handle_auth_redirect'));

		/**
		 * Install the SSO listeners for the Server and the Broker.
		 *
		 * For SSO to work we rely on two major components, the SSO
		 * Server, which is usually the main site, and the broker,
		 * which is the target site.
		 *
		 * We add a listener to plugins loaded, where we can
		 * hook into to deal with the specifics.
		 *
		 * Then, we need to hook WordPress's default send_origin_headers
		 * into de the custom listener.
		 *
		 * @see https://developer.wordpress.org/reference/functions/send_origin_headers/
		 * @see https://developer.wordpress.org/reference/hooks/allowed_http_origins/
		 */
		add_action('wu_sso_handle', 'wu_no_cache');

		add_action('wu_sso_handle', 'send_origin_headers');

		add_action('plugins_loaded', array($this, 'handle_requests'), 0);

		add_action('wu_sso_handle_sso_grant', array($this, 'handle_server'));

		add_action('wu_sso_handle_sso', array($this, 'handle_broker'), 20);

		add_filter('allowed_http_origins', array($this, 'add_additional_origins'));

		/**
		 * Authorize a user via a bearer, and converts it into a regular cookie
		 * authenticated user
		 *
		 * When the first connection happens after the flow finishes,
		 * we use the authentication bearer to determine the user.
		 *
		 * After that, we create a regular auth cookie and remove
		 * the other signs of the session.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/determine_current_user/
		 */
		add_filter('determine_current_user', array($this, 'determine_current_user'), 90);

		add_action('init', array($this, 'convert_bearer_into_auth_cookies'));

		add_filter('removable_query_args', array($this, 'add_sso_removable_query_args'));

		/**
		 * Adds the SSO scripts to the head of the front-end
		 * and the login page to try to perform a SSO flow.
		 *
		 * @see assets/js/sso.js
		 */
		add_action('wp_head', array($this, 'enqueue_script'));

		add_action('login_head', array($this, 'enqueue_script'));

		/**
		 * Allow plugin developers to add additional hooks, if needed.
		 *
		 * This needs to be delayed until the init as SSO is something that runs on sunrise.
		 *
		 * @param self $this The SSO class.
		 * @since 2.0.0
		 */
		do_action('wu_sso_loaded', $this);

		/*
		 * Schedule another loaded hook to be triggered
		 * on init, so later functionality can also hook into it.
		 */
		add_action('init', array($this, 'loaded_on_init'));

	} // end startup;

	/**
	 * Late loaded hook, triggered on init.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function loaded_on_init() {

		do_action('wu_sso_loaded_on_init', $this);

	} // end loaded_on_init;

	/**
	 * Changes the default WordPress requirements for setting the logged in cookie
	 * as secure.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/secure_logged_in_cookie/
	 *
	 * @since 2.0.11
	 * @return boolean
	 */
	public function force_secure_login_cookie() {

		return is_ssl();

	} // end force_secure_login_cookie;

	/**
	 * Bypasses the auth redirect on the wp-admin side of things.
	 *
	 * When SSO reaches a wp-admin url, it gets redirected to
	 * the login page before the flow can be concluded.
	 * Here we need to hook in and prevent the login redirect
	 * from happening.
	 *
	 * @since 2.0.11
	 * @return null|true
	 */
	public function handle_auth_redirect() {

		$broker = $this->get_broker();

		if (!$broker) {

		} // end if;

		if ($broker->is_must_redirect_call()) {

			return false;

		} // end if;

		$sso_path = $this->get_url_path();

		/**
		 * If we are performing a SSO flow already
		 * we don't need to do anything, but we
		 * need to return true to short-circuit
		 * the auth redirect function and prevent the
		 * login redirect.
		 */
		if ($this->input($sso_path) && $this->input($sso_path) !== 'done') {

			return true;

		} // end if;

		$should_skip_redirect = $this->get_isset($_COOKIE, 'wu_sso_denied', false);

		/**
		 * If we are on the wp-admin, we check for three criteria
		 * to decide if we need to try to perform a SSO redirect
		 * or not:
		 *
		 * 1. If the current domain is the same domain as the main site's;
		 * 2. If the user is logged in or not;
		 * 3. If we should skip the redirect, based on previous attempts.
		 */
		if (!wu_is_same_domain() && !is_user_logged_in() && !$should_skip_redirect) {

			nocache_headers();

			$redirect_url = $this->get_current_url() . "?{$sso_path}=login";

			wp_redirect($redirect_url);

			exit;

		} // end if;

		/**
		 * Fix the redirect URL, just to be sure
		 * removing the sso parameter.
		 *
		 * @since 2.0.11
		 */
		$_SERVER['REQUEST_URI'] = str_replace('https://a.com/', '', remove_query_arg('sso', 'https://a.com/' . $_SERVER['REQUEST_URI']));

	} // end handle_auth_redirect;

	/**
	 * Listens for SSO requests and route them to the correct handler.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function handle_requests() {

		$action = $this->get_sso_action();

		if (!$action) {

			return;

		} // end if;

		header('Access-Control-Allow-Headers: Content-Type');

		remove_filter('determine_current_user', array($this, 'determine_current_user'), 90);

		status_header(200);

		$return_type = wp_is_jsonp_request() ? 'jsonp' : 'redirect';

		$action = str_replace($this->get_url_path(), 'sso', $action);

		$action = trim(wu_replace_dashes($action), '/');

		do_action('wu_sso_handle', $action, $return_type, $this);

		do_action("wu_sso_handle_{$action}", $return_type, $this);

	} // end handle_requests;

	/**
	 * Handles the SSO server side of the auth protocol.
	 *
	 * @since 2.0.11
	 *
	 * @param string $response_type Redirect or jsonp.
	 * @return void
	 */
	public function handle_server($response_type = 'redirect') {

		$server = $this->get_server();

		try {

			$verification_code = $server->attach();
			$error             = null;

		} catch (Exception\SSO_Session_Exception $e) {

			if (is_ssl()) {

				$verification_code = null;

				$error = array(
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
				);

			} else {

				$verification_code = 'must-redirect';

			} // end if;

		} catch (\Throwable $th) {

			$verification_code = null;

			$error = array(
				'code'    => $th->getCode(),
				'message' => $th->getMessage(),
			);

		} // end try;

		if ($response_type === 'jsonp') {

			$data = json_encode($error ?? array( // phpcs:ignore
				'code'       => 200,
				'verify'     => $verification_code,
				'return_url' => $this->input('return_url', ''),
			));

			$response_code = 200; // phpcs:ignore

			echo "wu.sso($data, $response_code);";

			status_header($response_code);

			exit;

		} elseif ($response_type === 'redirect') {

			$args = array(
				'sso_verify' => $verification_code ? $verification_code : 'invalid',
			);

			if (isset($error) && $error) {

				$args['sso_error'] = $error['message'];

			} // end if;

			$return_url = remove_query_arg('sso_verify', $_GET['return_url']);

			$url = add_query_arg($args, $return_url);

			wp_redirect($url, 303, 'WP-Ultimo-SSO');

			exit;

		} // end if;

	} // end handle_server;

	/**
	 * Handles the broker side of the SSO protocol.
	 *
	 * @since 2.0.11
	 *
	 * @param string $response_type Redirect or jsonp.
	 * @return void
	 */
	public function handle_broker($response_type = 'redirect') {

		if (is_main_site()) {

			return;

		} // end if;

		if (is_user_logged_in()) {

			return;

		} // end if;

		$broker = $this->get_broker();

		$verify_code = $this->input('sso_verify');

		if ($verify_code) {

			$broker->verify($verify_code);

			$url = $this->input('return_url', $this->get_current_url());

			$redirect_url = $this->get_final_return_url($url);

			wp_redirect($redirect_url, 302, 'WP-Ultimo-SSO');

			exit;

		} // end if;

    // Attach through redirect if the client isn't attached yet.
		if (!$broker->isAttached()) {

			$return_url = $this->get_current_url();

			if ($response_type === 'jsonp') {

				$attach_url = $broker->getAttachUrl(array(
					'_jsonp' => '1',
				));

			} else {

				$attach_url = $broker->getAttachUrl(array(
					'return_url' => $return_url,
				));

			} // end if;

			wp_redirect($attach_url, 302, 'WP-Ultimo-SSO');

			exit();

		} // end if;

		if ($response_type === 'jsonp') {

			echo '// Nothing to see here.';

			exit;

		} // end if;

	} // end handle_broker;

	/**
	 * Filters the list of allowed origins to add
	 * mapped domains and the main site domain.
	 *
	 * @since 2.0.11
	 * @todo maybe move this to the domain mapping class.
	 *
	 * @param array $allowed_origins List of allowed origins.
	 * @return array The modified list of allowed origins.
	 */
	public function add_additional_origins($allowed_origins) {

		global $current_site;

		$additional_domains = array(
			"http://{$current_site->domain}",
			"https://{$current_site->domain}",
		);

		$origin_url = wp_parse_url(get_http_origin());

		$sites = get_sites(array(
			'network_id' => get_current_network_id(),
			'domain'     => $this->get_isset($origin_url, 'host', 'invalid'),
		));

		if ($sites) {

			$additional_domains[] = sprintf('http://%s', $this->get_isset($origin_url, 'host', 'invalid'));
			$additional_domains[] = sprintf('https://%s', $this->get_isset($origin_url, 'host', 'invalid'));

		} // end if;

		$site = get_site_by_path($this->get_isset($origin_url, 'host', 'invalid'), $this->get_isset($origin_url, 'path', '/'));

		if ($site) {

			$domains = wu_get_domains(array(
				'active'        => true,
				'blog_id'       => $site->blog_id,
				'stage__not_in' => \WP_Ultimo\Models\Domain::INACTIVE_STAGES,
				'fields'        => 'domain',
			));

			foreach ($domains as $domain) {

				$additional_domains[] = "http://{$domain}";
				$additional_domains[] = "https://{$domain}";

			} // end foreach;

		} // end if;

		return array_merge($allowed_origins, $additional_domains);

	} // end add_additional_origins;

	/**
	 * Determines the current user based on the Bearer token received.
	 *
	 * @since 2.0.11
	 *
	 * @param int $current_user_id The current user id.
	 * @return int
	 */
	public function determine_current_user($current_user_id) {

		$sso_path = $this->get_url_path();

		if (!$this->input($sso_path) || $this->input($sso_path) !== 'done') {

			return $current_user_id;

		} // end if;

		$broker = $this->get_broker();

		try {

			$bearer = $broker->getBearerToken();

			$server_request = $this->build_server_request('GET', $this->get_current_url())->withHeader('Authorization', "Bearer $bearer");

			$this->get_server()->startBrokerSession($server_request);

			if ($this->get_target_user_id()) {

				wp_set_auth_cookie($this->get_target_user_id(), true);

				return $this->get_target_user_id();

			} // end if;

		} catch (\Throwable $th) {

			/**
			 * We don't need to handle the exceptions here
			 * as we mostly just want to ignore this and move
			 * on if we are not able to validate the customer.
			 *
			 * @throws ServerException
			 * @throws SsoException
			 * @throws BrokerException
			 * @throws NotAttachedException
			 */

		} // end try;

		return $current_user_id;

	} // end determine_current_user;

	/**
	 * Convert a user determined by a bearer into a cookie-based auth.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function convert_bearer_into_auth_cookies() {

		$broker = $this->get_broker();

		if (is_user_logged_in() && $broker && $broker->isAttached()) {

			$broker->clearToken();

			$id = $this->decode($broker->getBrokerId(), $this->salt());

			delete_site_transient(sprintf('sso-%s-%s', $broker->getBrokerId(), $id));

		} // end if;

	} // end convert_bearer_into_auth_cookies;

	/**
	 * Add the SSO tags to the removable query args.
	 *
	 * @since 2.0.11
	 *
	 * @param array $removable_query_args The list of removable query args.
	 * @return array
	 */
	public function add_sso_removable_query_args($removable_query_args) {

		$removable_query_args[] = $this->get_url_path();

		return $removable_query_args;

	} // end add_sso_removable_query_args;

	/**
	 * Adds the front-end script to trigger SSO flows
	 * specially when caching is enabled.
	 *
	 * @since 2.0.11
	 * @return void
	 */
	public function enqueue_script() {

		global $pagenow;

		if (is_main_site()) {

			return;

		} // end if;

		if ($this->get_setting('restrict_sso_to_login_pages', false)) {

			if (wu_is_login_page() === false) {

				return;

			} // end if;

		} // end if;

		/*
		 * The visitor is actively trying to logout. Let them do it!
		 */
		if ($this->input('action', 'nothing') === 'logout' || $this->input('loggedout')) {

			return;

		} // end if;

		wp_register_script('wu-detect-incognito', wu_get_asset('detectincognito.js', 'js/lib'), false, wu_get_version());

		wp_register_script('wu-sso', wu_get_asset('sso.js', 'js'), array('wu-cookie-helpers', 'wu-detect-incognito'), wu_get_version());

		$sso_path = $this->get_url_path();

		$home_site = get_home_url(get_current_blog_id(), $this->get_url_path());

		$removable_query_args = array(
			$sso_path,
			"{$sso_path}-grant",
			'return_url',
		);

		$options = array(
			'server_url'            => $home_site,
			'return_url'            => $this->get_current_url(),
			'is_user_logged_in'     => is_user_logged_in() || $this->get_isset($_COOKIE, 'wu_sso_denied'),
			'expiration_in_minutes' => 5 / (24 * 60),
			'filtered_url'          => remove_query_arg($removable_query_args, $this->get_current_url()),
			'img_folder'            => dirname(wu_get_asset('img', 'img')),
			'use_overlay'           => $this->get_setting('enable_sso_loading_overlay', true),
		);

		wp_localize_script('wu-sso', 'wu_sso_config', $options);

		wp_enqueue_script('wu-sso');

	} // end enqueue_script;

	/**
	 * Gets the strategy to be used by default.
	 *
	 * Two options are available:
	 *
	 * - Ajax, to deal with caching issues.
	 * - Redirect, when caching is not in place.
	 *
	 * @since 2.0.11
	 * @return string The strategy to be used - ajax or redirect.
	 */
	public function get_strategy() {

		$env = 'development';

		if (function_exists('wp_get_environment_type')) {

			$env = wp_get_environment_type();

		} else {

			$env = defined('WP_DEBUG') && WP_DEBUG ? 'development' : 'production';

		} // end if;

		return apply_filters('wu_sso_get_strategy', $env === 'development' ? 'redirect' : 'ajax', $env, $this);

	} // end get_strategy;

	/**
	 * Gets the final return URL.
	 *
	 * @since 2.0.11
	 *
	 * @param string $return_url The return url.
	 * @return string
	 */
	public function get_final_return_url($return_url) {

		$parsed_url = wp_parse_url($return_url);

		$sso_path = $this->get_url_path();

		$parsed_url['path'] = preg_replace("/\/?{$sso_path}\/?$/", '', $parsed_url['path']);

		$parsed_url['path'] = trim($parsed_url['path'], '/');

		$fragments = array(
			$parsed_url['scheme'] . '://' . $parsed_url['host'],
			$parsed_url['path'],
		);

		$args = array(
			$sso_path => 'done',
		);

		return add_query_arg($args, implode('/', $fragments));

	} // end get_final_return_url;

	/**
	 * Get the return type we need to use.
	 *
	 * @since 2.0.11
	 * @return string One of two values - redirect or jsonp.
	 */
	public function get_return_type() {

		$allowed_return_types = array(
			'jsonp',
			'json',
			'redirect',
		);

		$received_type = $this->input('return_type', 'redirect');

		return in_array($received_type, $allowed_return_types, true) ? $received_type : 'redirect';

	}  // end get_return_type;

	/**
	 * Parses the request and gets the SSO action to perform.
	 *
	 * @since 2.0.11
	 * @return string
	 */
	protected function get_sso_action() {

		$sso_path = $this->get_url_path();

		$pattern = "/\/?{$sso_path}(-grant)?\/?$/";

		$m = array();

		$path = wp_parse_url($this->get_current_url(), PHP_URL_PATH);

		preg_match($pattern, $path, $m);

		$action = $this->get_isset($m, 0, '');

		if (!$action) {

			$action = $this->input($sso_path, 'done') !== 'done' ? $sso_path : '';

		} // end if;

		if (!$action) {

			$action = $this->input("$sso_path-grant", 'done') !== 'done' ? "$sso_path-grant" : '';

		} // end if;

		return $action;

	} // end get_sso_action;

	/**
	 * Returns the salt to be used on the hashing functions.
	 *
	 * @since 2.0.11
	 * @return string
	 */
	public function salt() {

		return apply_filters('wu_sso_salt', wp_salt(), $this);

	} // end salt;

	/**
	 * Returns a PSR16-compatible cache implementation.
	 *
	 * @since 2.0.11
	 * @return CacheInterface
	 */
	public function cache() {

		if ($this->cache === null) {

			$this->cache = new Psr16Cache(new FilesystemAdapter());

		} // end if;

		return apply_filters('wu_sso_cache', $this->cache, $this);

	} // end cache;

	/**
	 * Creates a PSR7 Server Request object.
	 *
	 * @since 2.0.11
	 *
	 * @param string $url The URL to call.
	 * @return ServerRequestInterface
	 */
	public function build_server_request($url = '') {

		$psr7_server_request_builder = new Psr17Factory();

		$request = $psr7_server_request_builder->createServerRequest('GET', $url);

		return apply_filters('wu_sso_server_request', $request, $url, $this);

	} // end build_server_request;

	/**
	 * Returns a PSR3 logger interface that we can use to log SSO results.
	 *
	 * @since 2.0.11
	 * @return LoggerInterface
	 */
	public function logger() {

		if ($this->logger === null) {

			return apply_filters('wu_sso_logger', $this->logger, $this);

		} // end if;

	} // end logger;

	/**
	 * Creates a secret based on the date of registration of a sub-site.
	 *
	 * @since 2.0.11
	 *
	 * @param string $date The date to use.
	 * @return string The hashed secret.
	 */
	public function calculate_secret_from_date($date) {

		$tz = new \DateTimeZone('GMT');

		try {

			$int_version = (int) \DateTime::createFromFormat('Y-m-d H:i:s', $date, $tz)->format('mdisY');

		} catch (\Throwable $th) {

			throw new Exception\SSO_Exception(__('SSO secret creation failed.', 'wp-ultimo'), 500);

		} // end try;

		return wp_hash($int_version);

	} // end calculate_secret_from_date;

	/**
	 * Returns the server object to be used on the SSO protocol.
	 *
	 * @since 2.0.11
	 * @return Server
	 */
	public function get_server() {

		$session_handler = new SSO_Session_Handler($this);

		$server = (new Server(array($this, 'get_broker_by_id'), $this->cache()))->withSession($session_handler);

		return apply_filters('wu_sso_get_server', $server, $this);

	} // end get_server;

	/**
	 * Gets a sub-site based on the broker id passed.
	 *
	 * @since 2.0.11
	 *
	 * @param string $id The broker id.
	 * @return array The broker domain list and secret.
	 */
	public function get_broker_by_id($id) {

		global $current_site;

		$site_id = $this->decode($id, $this->salt());

		$site = get_site($site_id ? $site_id : 'non-existent');

		if (!$site) {

			return null;

		} // end if;

		$main_domain = wp_parse_url(get_home_url($site_id), PHP_URL_HOST);

		$domain_list = array(
			$current_site->domain,
			$main_domain,
		);

		if (is_subdomain_install()) {

			$domain_list[] = $site->domain;

		} // end if;

		$domain_list = apply_filters('wu_sso_site_allowed_domains', $domain_list, $site_id, $site, $this);

		return array(
			'secret'  => $this->calculate_secret_from_date($site->registered),
			'domains' => $domain_list,
		);

	} // end get_broker_by_id;

	/**
	 * Returns a broker instance.
	 *
	 * @since 2.0.11
	 * @return SSO_Broker
	 */
	public function get_broker() {

		global $current_blog;

		$secret = $this->calculate_secret_from_date($current_blog->registered);

		$home_sso_url = get_home_url(wu_get_main_site_id(), $this->get_url_path('grant'));

		$broker_id = $this->encode($current_blog->blog_id, $this->salt());

		$this->broker = new SSO_Broker($home_sso_url, $broker_id, $secret);

		return apply_filters('wu_sso_get_broker', $this->broker, $this);

	} // end get_broker;

	/**
	 * Set the target user after the bearer is passed.
	 *
	 * @since 2.0.11
	 *
	 * @param int $target_user_id The target user id to set.
	 * @return void
	 */
	public function set_target_user_id($target_user_id) {

		$this->target_user_id = $target_user_id;

	} // end set_target_user_id;

	/**
	 * Returns the target user id.
	 *
	 * @since 2.0.11
	 * @return int
	 */
	public function get_target_user_id() {

		return $this->target_user_id;

	} // end get_target_user_id;

	/**
	 * Get the url path for SSO.
	 *
	 * By default, it is set to "sso",
	 * but this can be changed via the "wu_sso_get_url_path" filter.
	 *
	 * @see wu_sso_get_url_path
	 * @since 2.0.11
	 *
	 * @param string $action The sub-action being get.
	 * @return string
	 */
	public function get_url_path($action = '') {

		$fragments = array(
			apply_filters('wu_sso_get_url_path', 'sso', $action, $this),
		);

		if ($action) {

			$fragments[] = $action;

		} // end if;

		return implode('-', $fragments);

	} // end get_url_path;

	/**
	 * Helper function to generate a sso url.
	 *
	 * @since 2.0.11
	 *
	 * @param string $url The url to add sso attributes to.
	 * @return string
	 */
	public static function with_sso($url) {

		$sso = SSO::get_instance();

		if ($sso->is_enabled() === false) {

			return $url;

		} // end if;

		$sso_path = $sso->get_url_path();

		$sso_params = array(
			$sso_path => 'login',
		);

		return add_query_arg($sso_params, $url);

	}  // end with_sso;

} // end class SSO;
