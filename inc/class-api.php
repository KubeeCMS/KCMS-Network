<?php
/**
 * Default API hooks.
 *
 * @package WP_Ultimo
 * @subpackage API
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Adds a lighter ajax option to WP Ultimo.
 *
 * @since 1.9.14
 */
class API {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Namespace of our API endpoints
	 *
	 * @since 1.7.4
	 * @var string
	 */
	private $namespace = 'wu';

	/**
	 * Version fo the API, this is used to build the API URL
	 *
	 * @since 1.7.4
	 * @var string
	 */
	private $api_version = 'v2';

	/**
	 * Initiates the API hooks
	 *
	 * @since 1.7.4
	 * @return void
	 */
	public function __construct() {

		/**
		 * Add the admin settings for the API
		 *
		 * @since 1.7.4
		 */
		add_action('init', array($this, 'add_settings'));

		/**
		 * Refreshing API credentials
		 *
		 * @since 1.7.4
		 */
		add_action('wu_before_save_settings', array($this, 'refresh_API_credentials'), 10);

		/**
		 * Register the routes
     *
		 * @since 1.7.4
		 */
		add_action('rest_api_init', array($this, 'register_routes'));

		/**
		 * Log API errors
		 *
		 * @since 2.0.0
		 */
		add_action('rest_request_after_callbacks', array($this, 'log_api_errors'), 10, 3);

	} // end __construct;

	/**
	 * Allow admins to refresh their API credentials.
	 *
	 * @since 1.7.4
	 * @return void
	 */
	public function refresh_API_credentials() { // phpcs:ignore

		if (wu_request('submit_button') === 'refresh_api_credentials') {

			wu_save_setting('api_url', network_site_url());

			wu_save_setting('api_key', wp_generate_password(24, false));

			wu_save_setting('api_secret', wp_generate_password(24, false));

			wp_safe_redirect(network_admin_url('admin.php?page=wp-ultimo-settings&tab=api&api=refreshed&updated=1'));

			exit;

		} // end if;

	} // end refresh_API_credentials;

	/**
	 * Add the admin interface to create new webhooks
	 *
	 * @since 1.7.4
	 */
	public function add_settings() {
    /*
		 * API & Webhooks
		 * This section holds the API settings of the WP Ultimo Plugin.
		 */
		wu_register_settings_section('api', array(
			'title' => __('API & Webhooks', 'wp-ultimo'),
			'desc'  => __('API & Webhooks', 'wp-ultimo'),
			'icon'  => 'dashicons-wu-paper-plane',
			'order' => 95,
		));

		wu_register_settings_field('api', 'api_header', array(
			'title' => __('API Settings', 'wp-ultimo'),
			'desc'  => __('Options related to WP Ultimo API endpoints.', 'wp-ultimo'),
			'type'  => 'header',
		));

		wu_register_settings_field('api', 'enable_api', array(
			'title'   => __('Enable API', 'wp-ultimo'),
			'desc'    => __('Tick this box if you want WP Ultimo to add its own endpoints to the WordPress REST API. This is required for some integrations to work, most notabily, Zapier.', 'wp-ultimo'),
			'type'    => 'toggle',
			'default' => 1,
		));

		$refreshed_tag = '';

		if (wu_request('updated') && wu_request('api') === 'refreshed') {

			$refreshed_tag = sprintf('<span class="wu-ml-2 wu-text-green-600">%s</span>', __('Credentials Refreshed', 'wp-ultimo'));

		} // end if;

		wu_register_settings_field('api', 'api_url', array(
			'title'   => __('API URL', 'wp-ultimo'),
			'desc'    => '',
			'tooltip' => '',
			'copy'    => true,
			'type'    => 'text-display',
			'default' => network_site_url(),
			'require' => array(
				'enable_api' => true,
			),
		));

		wu_register_settings_field('api', 'api_key', array(
			'title'           => __('API Key', 'wp-ultimo') . $refreshed_tag,
			'desc'            => '',
			'tooltip'         => '',
			'type'            => 'text-display',
			'copy'            => true,
			'default'         => wp_generate_password(24, false),
			'wrapper_classes' => 'sm:wu-w-1/2 wu-float-left',
			'require'         => array(
				'enable_api' => true,
			),
		));

		wu_register_settings_field('api', 'api_secret', array(
			'title'           => __('API Secret', 'wp-ultimo') . $refreshed_tag,
			'tooltip'         => '',
			'type'            => 'text-display',
			'copy'            => true,
			'default'         => wp_generate_password(24, false),
			'wrapper_classes' => 'sm:wu-border-l-0 sm:wu-w-1/2 wu-float-left',
			'require'         => array(
				'enable_api' => 1,
			),
		));

		wu_register_settings_field('api', 'api_note', array(
			'desc'            => __('This is your API Key. You cannot change it directly. To reset the API key and secret, use the button "Refresh API credentials" below.', 'wp-ultimo'),
			'type'            => 'note',
			'classes'         => 'wu-text-gray-700 wu-text-xs',
			'wrapper_classes' => 'wu-bg-white sm:wu-border-t-0 sm:wu-mt-0 sm:wu-pt-0',
			'require'         => array(
				'enable_api' => 1,
			),
		));

		wu_register_settings_field('api', 'refresh_api_credentials', array(
			'title'           => __('Refresh API Credentials', 'wp-ultimo'),
			'type'            => 'submit',
			'classes'         => 'button wu-ml-auto',
			'wrapper_classes' => 'wu-bg-white sm:wu-border-t-0 sm:wu-mt-0 sm:wu-pt-0',
			'require'         => array(
				'enable_api' => 1,
			),
		));

		wu_register_settings_field('api', 'api_log_calls', array(
			'title'   => __('Log API calls (Advanced)', 'wp-ultimo'),
			'desc'    => __('Tick this box if you want to log all calls received via WP Ultimo API endpoints. You can access the logs on WP Ultimo &rarr; System Info &rarr; Logs.', 'wp-ultimo'),
			'type'    => 'toggle',
			'default' => 0,
			'require' => array(
				'enable_api' => 1,
			),
		));

		wu_register_settings_field('api', 'webhook_header', array(
			'title' => __('Webhook Settings', 'wp-ultimo'),
			'desc'  => __('Options related to WP Ultimo API webhooks.', 'wp-ultimo'),
			'type'  => 'header',
		));

		wu_register_settings_field('api', 'webhook_calls_blocking', array(
			'title'   => __('Wait for Response (Advanced)', 'wp-ultimo'),
			'desc'    => __('Tick this box if you want the WP Ultimo\'s webhook calls to wait for the remote server to respond. Keeping this option enabled can have huge effects on your network\'s performance, only enable it if you know what you are doing and need to debug webhook calls.', 'wp-ultimo'),
			'type'    => 'toggle',
			'default' => 0,
		));

	} // end add_settings;

	/**
	 * Returns the namespace of our API endpoints.
	 *
	 * @since 1.7.4
	 * @return string
	 */
	public function get_namespace() {

		return "$this->namespace/$this->api_version";

	} // end get_namespace;

	/**
	 * Returns the credentials.
	 *
	 * @since 1.7.4
	 * @return array
	 */
	public function get_auth() {

		return array(
			'api_key'    => wu_get_setting('api_key', 'prevent'),
			'api_secret' => wu_get_setting('api_secret', 'prevent'),
		);

	} // end get_auth;

	/**
	 * Validate a pair of API credentials
	 *
	 * @since 1.7.4
	 * @param string $api_key The API key.
	 * @param string $api_secret The API secret.
	 * @return boolean
	 */
	public function validate_credentials($api_key, $api_secret) {

		return compact('api_key', 'api_secret') === $this->get_auth(); // phpcs:ignore

	} // end validate_credentials;

	/**
	 * Check if we can log api calls.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function should_log_api_calls() {

		return apply_filters('wu_should_log_api_calls', wu_get_setting('api_log_calls', false));

	} // end should_log_api_calls;

	/**
	 * Checks if we should log api calls or not, and if we should, log them.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 */
	public function maybe_log_api_call($request) {

		if ($this->should_log_api_calls()) {

			$payload = array(
				'route'       => $request->get_route(),
				'method'      => $request->get_method(),
				'url_params'  => $request->get_url_params(),
				'body_params' => $request->get_body()
			);

			wu_log_add('api-calls', json_encode($payload, JSON_PRETTY_PRINT));

		} // end if;

	} // end maybe_log_api_call;

	/**
	 * Log api errors.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed            $result The result of the REST API call.
	 * @param string|array     $handler The callback.
	 * @param \WP_REST_Request $request The request object.
	 * @return mixed
	 */
	public function log_api_errors($result, $handler, $request) {

		if (is_wp_error($result) && strpos($request->get_route(), '/wu') === 0) {
			/*
			 * Log API call here if we didn't log it before.
			 */
			if (!$this->should_log_api_calls()) {

				$payload = array(
					'route'       => $request->get_route(),
					'method'      => $request->get_method(),
					'url_params'  => $request->get_url_params(),
					'body_params' => $request->get_body()
				);

				wu_log_add('api-errors', json_encode($payload, JSON_PRETTY_PRINT));

			} // end if;

			wu_log_add('api-errors', $result);

		} // end if;

		return $result;

	} // end log_api_errors;

	/**
	 * Tries to validate the API key and secret from the request
	 *
	 * @since 1.7.4
	 * @param \WP_REST_Request $request WP Request Object.
	 * @return boolean
	 */
	public function check_authorization($request) {

		if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER']) {

			$api_key    = $_SERVER['PHP_AUTH_USER'];
			$api_secret = $_SERVER['PHP_AUTH_PW'];

		} else {

			$params = $request->get_params();

			$api_key    = wu_get_isset($params, 'api_key', wu_get_isset($params, 'api-key'));
			$api_secret = wu_get_isset($params, 'api_secret', wu_get_isset($params, 'api-secret'));

		} // end if;

		if ($api_key === false) {

			return false;

		} // end if;

		return $this->validate_credentials($api_key, $api_secret);

	} // end check_authorization;

	/**
	 * Checks if the API routes are available or not, via the settings.
	 *
	 * @since 1.7.4
	 * @return boolean
	 */
	public function is_api_enabled() {

		/**
		 * Allow plugin developers to force a given state for the API.
		 *
		 * @since 1.7.4
		 * @return boolean
		 */
		return apply_filters('wu_is_api_enabled', wu_get_setting('enable_api', true));

	} // end is_api_enabled;

	/**
	 * Register the API routes.
	 *
	 * @since 1.7.4
	 * @return void
	 */
	public function register_routes() {

		if (!$this->is_api_enabled()) {

			return;

		} // end if;

		$namespace = $this->get_namespace();

		register_rest_route($namespace, '/auth', array(
			'methods'             => 'GET',
			'callback'            => array($this, 'auth'),
			'permission_callback' => array($this, 'check_authorization'),
		));

		/**
		 * Allow additional routes to be registered.
		 *
		 * This is used by our /register endpoint.
		 *
		 * @since 2.0.0
		 * @param self $this The current API instance.
		 */
		do_action('wu_register_rest_routes', $this);

	} // end register_routes;

	/**
	 * Dummy endpoint to low services to test the authentication method being used.
	 *
	 * @since 1.7.4
	 *
	 * @param \WP_REST_Request $request WP Request Object.
	 * @return void
	 */
	public function auth($request) {

		$current_site = get_current_site();

		wp_send_json(array(
			'success' => true,
			'label'   => $current_site->site_name,
			'message' => __('Welcome to our API', 'wp-ultimo'),
		));

	} // end auth;

} // end class API;
