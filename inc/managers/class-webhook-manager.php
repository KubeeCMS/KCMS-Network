<?php
/**
 * Webhook Manager
 *
 * Handles processes related to Webhooks.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Webhook_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Models\Webhook;
use WP_Ultimo\Logger;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to webhooks.
 *
 * @since 2.0.0
 */
class Webhook_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'webhook';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Webhook';

	/**
	 * Holds the list of available events for webhooks.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $events = array();

	/**
	 * Holds the list of all webhooks.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $webhooks = array();

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		add_action('init', array($this, 'register_webhook_listeners'));

		add_action('wp_ajax_wu_send_test_event', array($this, 'send_test_event'));

	} // end init;

	/**
	 * Adds the listeners to the webhook callers, extend this by adding actions to wu_register_webhook_listeners
	 *
	 * @todo This needs to have a switch, allowing us to turn it on and off.
	 * @return void.
	 */
	public function register_webhook_listeners() {

		foreach (wu_get_event_types() as $key => $event) {

			add_action('wu_event_' . $key, array($this, 'send_webhooks'));

		} // end foreach;

	} // end register_webhook_listeners;

	/**
	 * Sends all the webhooks that are triggered by a specific event.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args with events slug and payload.
	 * @return void
	 */
	public function send_webhooks($args) {

		$webhooks = Webhook::get_all();

		foreach ($webhooks as $webhook) {

			if ('wu_event_' . $webhook->get_event() == current_filter()) {

				$this->send_webhook($webhook, $args, true);

			} // end if;

		} // end foreach;

	} // end send_webhooks;

	/**
	 * Sends a specific webhook.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Webhook $webhook The webhook to send.
	 * @param array                     $data Key-value array of data to send.
	 * @param boolean                   $blocking Decides if we want to wait for a response to keep a log.
	 * @return mixed.
	 */
	public function send_webhook($webhook, $data, $blocking = true) {

		if (!$data) {

			return false;

		} // end if;

		$response = wp_remote_post($webhook->get_webhook_url(), array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'cookies'     => array(),
			'body'        => $data['body'],
			'blocking'    => $blocking,
		), current_filter(), $webhook);

		$webhook_name = str_replace(' ', '_', $webhook->get_name());

		if (is_wp_error($response)) {

			$payload = array(
				'object_id'   => $webhook->get_id(),
				'object_type' => 'webhook',
				'type'        => 'WARNING',
				'error'       => $response->get_error_message(),
			);

			// Create a error event.
			wu_do_event('send_webhook_' . $webhook_name, $payload);

			return;

		} // end if;

		$response = $this->maybe_json_decode(wp_remote_retrieve_body($response));

		$response = wp_strip_all_tags($response);

		$this->log_event(
			$webhook->get_slug(),
			$webhook->get_id(),
			$webhook->get_webhook_url(),
			$data['body'],
			$response
		);

		$payload = array(
			'object_id'      => $webhook->get_id(),
			'object_type'    => 'webhook',
			'type'           => 'SUCCESS',
			'webhook_return' => $response
		);

		// create webhook event
		wu_do_event('send_webhook_' . $webhook_name, $payload);

		$new_count = $webhook->get_count() + 1;

		$webhook->set_count($new_count);

		$webhook->save();

	} // end send_webhook;

	/**
	 * Send a test event of the webhook
	 *
	 * @return void
	 */
	public function send_test_event() {

		if (!current_user_can('manage_network')) {

			wp_send_json(array(
				'response' => __('You do not have enough permissions to send a test event.', 'wp-ultimo'),
				'webhooks' => Webhook::get_items_as_array(),
			));

		} // end if;

		$event = wu_get_event_type($_POST['webhook_event']);

		$response = wp_remote_post($_POST['webhook_url'], array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'cookies'     => array(),
			'body'        => wp_json_encode(wu_maybe_lazy_load_payload($event['payload'])),
			'blocking'    => true,
		));

		$response = wp_remote_retrieve_body($response);

		wp_send_json(array(
			'response' => $response,
			'id'       => wu_request('webhook_id')
		));

	} // end send_test_event;

	/**
	 * Reads the log file and displays the content.
	 *
	 * @return void.
	 */
	public function serve_logs() {

		echo '<style>
			body {
				font-family: monospace;
				line-height: 20px;
			}
			pre {
				background: #ececec;
				border: solid 1px #ccc;
				padding: 10px;
				border-radius: 3px;
			}
			hr {
				margin: 25px 0;
				border-top: 1px solid #cecece;
				border-bottom: transparent;
			}
		</style>
		';

		if (!current_user_can('manage_network')) {

			echo __('You do not have enough permissions to read the logs of this webhook.', 'wp-ultimo');

			exit;

		} // end if;

		$id = absint($_REQUEST['id']);

		$logs = array_map(function($line) {

			$line = str_replace(' - ', ' </strong> - ', $line);

			$matches = array();

			$line = str_replace('\'', '\\\'', $line);
			$line = preg_replace('~(\{(?:[^{}]|(?R))*\})~', '<pre><script>document.write(JSON.stringify(JSON.parse(\'${1}\'), null, 2));</script></pre>', $line);

			return '<strong>' . $line . '<hr>';

		}, Logger::read_lines("webhook-$id", 5));

		echo implode('', $logs);

		exit;

	}  // end serve_logs;

	/**
	 * Maybe decodes JSON, if valid JSON.
	 *
	 * APIs are strange. Sometimes they don't return valid JSON content.
	 *
	 * @since 2.0.0
	 *
	 * @param string $string JSON to decode.
	 * @return string
	 */
	protected function maybe_json_decode($string) {

		$object = json_decode($string);

		return (json_last_error() === JSON_ERROR_NONE ? $object : $string);

	} // end maybe_json_decode;

	/**
	 * Log a webhook sent for later reference.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The name of the event.
	 * @param int    $id The id of the webhook sent.
	 * @param string $url The URL called by the webhook.
	 * @param array  $data The array with data to be sent.
	 * @param mixed  $response The response got on webhook call.
	 * @return void
	 */
	protected function log_event($slug, $id, $url, $data, $response) {

		$is_request_blocking = apply_filters('wu_should_log_webhook_calls', wu_get_setting('webhook_calls_blocking', false));

		$message = sprintf('Sent a %s event to the URL %s with data: %s ', $slug, $url, json_encode($data));

		$message .= $is_request_blocking ? sprintf('Got response: %s', json_encode($response)) : 'To debug the remote server response, turn the "Wait for Response" option on the WP Ultimo Settings > API & Webhooks Tab';

		wu_log_add("webhook-$id", $message);

	} // end log_event;

} // end class Webhook_Manager;
