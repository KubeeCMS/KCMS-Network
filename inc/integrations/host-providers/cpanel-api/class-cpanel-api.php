<?php
/**
 * CPanel API wrapper to send the calls.
 *
 * @package WP_Ultimo
 * @subpackage Integrations/Host_Providers/CPanel_API
 * @since 2.0.0
 */

namespace WP_Ultimo\Integrations\Host_Providers\CPanel_API;

use WP_Ultimo\Logger;

/**
 * CPanel API wrapper to send the calls.
 */
class CPanel_API {

	/**
	 * Kepps the host url for the cPanel.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $host;

	/**
	 * Holds the port of the cPanel.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	private $port;

	/**
	 * The cPanel username.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $username;

	/**
	 * The cPanel password. Yep =(
	 *
	 * @since 2.0.0
	 * @var [type]
	 */
	private $password;

	/**
	 * Holds the log instance.
	 *
	 * @since 2.0.0
	 * @var boolean|mixed
	 */
	private $log;

	/**
	 * Holds the name of the cookis file.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $cookie_file;

	/**
	 * Holds the curl file.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $curlfile;

	/**
	 * Holds the cookie tokens
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $cpsess;

	/**
	 * Holds the cPanel url.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $homepage;

	/**
	 * Holds the execution url.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $ex_page;

	/**
	 * Creates the CPanel_API Object.
	 *
	 * @since 1.6.2
	 * @param string  $username cPanel username.
	 * @param string  $password cPanel password.
	 * @param string  $host cPanel URL.
	 * @param integer $port cPanel port.
	 * @param boolean $log Log.
	 */
	public function __construct($username, $password, $host, $port = 2083, $log = false) {

		// Generates the cookie file
		$this->generate_cookie();

		$this->host        = $host;
		$this->port        = $port;
		$this->username    = $username;
		$this->password    = $password;
		$this->log         = $log;
		$this->cookie_file = Logger::get_logs_folder() . 'integration-cpanel-cookie.log';

		// Signs up
		$this->sign_in();

	} // end __construct;

	/**
	 * Generate the Cookie File, that is used to make API requests to CPanel.
	 *
	 * @since 1.6.2
	 * @return void
	 */
	public function generate_cookie() {

		wu_log_add('integration-cpanel-cookie', '');

	} // end generate_cookie;

	/**
	 * Logs error or success messages.
	 *
	 * @since 1.6.2
	 * @param string $message Message to be logged.
	 * @return boolean
	 */
	public function log($message) {

		return wu_log_add('integration-cpanel', $message);

	} // end log;

	/**
	 * Sends the request to the CPanel API.
	 *
	 * @since 1.6.2
	 * @param string $url URL endpoint.
	 * @param array  $params Request parameters to send.
	 * @return mixed
	 */
	private function request($url, $params = array()) {

		if ($this->log) {

			$curl_log = fopen($this->curlfile, 'a+');

		} // end if;

		if (!file_exists($this->cookie_file)) {

			try {

				fopen($this->cookie_file, 'w');

			} catch (Exception $ex) {

				if (!file_exists($this->cookie_file)) {

					$this->log($ex . __('Cookie file missing.', 'wp-ultimo'));

					return false;

				} // end if;

			} // end try;

		} elseif (!is_writable($this->cookie_file)) {

			$this->log(__('Cookie file not writable', 'wp-ultimo'));

			return false;

		} // end if;

		$ch = curl_init();

		$curl_opts = array(
			CURLOPT_URL            => $url,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_COOKIEJAR      => realpath($this->cookie_file),
			CURLOPT_COOKIEFILE     => realpath($this->cookie_file),
			CURLOPT_HTTPHEADER     => array(
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0',
				'Host: ' . $this->host,
				'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language: en-US,en;q=0.5',
				'Accept-Encoding: gzip, deflate',
				'Connection: keep-alive',
				'Content-Type: application/x-www-form-urlencoded'
			),
		);

		if (!empty($params)) {

			$curl_opts[CURLOPT_POST]       = true;
			$curl_opts[CURLOPT_POSTFIELDS] = $params;

		} // end if;

		if ($this->log) {

			$curl_opts[CURLOPT_STDERR]      = $curl_log;
			$curl_opts[CURLOPT_FAILONERROR] = false;
			$curl_opts[CURLOPT_VERBOSE]     = true;

		} // end if;

		curl_setopt_array($ch, $curl_opts);

		$answer = curl_exec($ch);

		if (curl_error($ch)) {

			// translators: %s is the cURL error.
			$this->log(sprintf(__('cPanel API Error: %s', 'wp-ultimo'), curl_error($ch)));

			return false;

		} // end if;

		curl_close($ch);

		if ($this->log) {

			fclose($curl_log);

		} // end if;

		return (@gzdecode($answer)) ? gzdecode($answer) : $answer; // phpcs:ignore

	} // end request;

	/**
	 * Get the base URL to make the calls.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	private function get_base_url() {

		return sprintf('https://%s:%s', $this->host, $this->port);

	} // end get_base_url;

	/**
	 * Signs in on the cPanel.
	 *
	 * @since 1.6.2
	 * @return boolean
	 */
	private function sign_in() {

		$url  = $this->get_base_url() . '/login/?login_only=1';
		$url .= '&user=' . $this->username . '&pass=' . urlencode($this->password);

		$reply = $this->request($url);

		$reply = json_decode($reply, true);

		if (isset($reply['status']) && $reply['status'] == 1) { // phpcs:ignore

			$this->cpsess   = $reply['security_token'];
			$this->homepage = $this->get_base_url() . $reply['redirect'];
			$this->ex_page  = $this->get_base_url() . "/{$this->cpsess}/execute/";

		} else {

			return $this->log(__('Cannot connect to your cPanel server : Invalid Credentials', 'wp-ultimo'));

		} // end if;

	} // end sign_in;

	/**
	 * Executes API calls, taking the request to the right API version
	 *
	 * @since 1.6.2
	 * @throws Exception Throwns exception when the api is invalid.
	 * @param string $api API version.
	 * @param string $module Module name, to build the endpoint.
	 * @param string $function Endpoint function to call.
	 * @param array  $parameters Parameters to the API endpoint.
	 * @return boolean
	 */
	public function execute($api, $module, $function, array $parameters = array()) {

		switch ($api) {

			case 'api2':
				return $this->api2($module, $function, $parameters);
			break;
			case 'uapi':
				return $this->uapi($module, $function, $parameters);
			break;
			default:
				throw new Exception('Invalid API type : api2 and uapi are accepted', 1);

		} // end switch;

	} // end execute;

	/**
	 * Send the request if the API being used is the UAPI (newer version)
	 *
	 * @since 1.6.2
	 * @param string $module Module name, to build the endpoint.
	 * @param string $function Endpoint function to call.
	 * @param array  $parameters Parameters to the API endpoint.
	 * @return mixed
	 */
	public function uapi($module, $function, array $parameters = array()) {

		if (count($parameters) < 1) {

			$parameters = '';

		} else {

			$parameters = (http_build_query($parameters));

		} // end if;

		return json_decode($this->request($this->ex_page . $module . '/' . $function . '?' . $parameters));

	} // end uapi;

	/**
	 * Send the request if the API being used is the API2 (older version)
	 *
	 * @since 1.6.2
	 * @param string $module Module name, to build the endpoint.
	 * @param string $function Endpoint function to call.
	 * @param array  $parameters Parameters to the API endpoint.
	 * @return mixed
	 */
	public function api2($module, $function, array $parameters = array()) {

		if (count($parameters) < 1) {

			$parameters = '';

		} else {

			$parameters = (http_build_query($parameters));

		} // end if;

		$url = $this->get_base_url() . $this->cpsess . '/json-api/cpanel' .
		'?cpanel_jsonapi_version=2' .
		"&cpanel_jsonapi_func={$function}" .
		"&cpanel_jsonapi_module={$module}&" . $parameters;

		return json_decode($this->request($url, $parameters));

	} // end api2;

} // end class CPanel_API;
