<?php
/**
 * Events Manager
 *
 * Handles processes related to events.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Event_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Managers;

use WP_Ultimo\Managers\Base_Manager;
use WP_Ultimo\Models\Event;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles processes related to events.
 *
 * @since 2.0.0
 */
class Event_Manager extends Base_Manager {

	use \WP_Ultimo\Apis\Rest_Api, \WP_Ultimo\Apis\WP_CLI, \WP_Ultimo\Traits\Singleton;

	/**
	 * The manager slug.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug = 'event';

	/**
	 * The model class associated to this manager.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $model_class = '\\WP_Ultimo\\Models\\Event';

	/**
	 * Holds the list of available events for webhooks.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $events = array();

	/**
	 * Instantiate the necessary hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {

		$this->enable_rest_api();

		$this->enable_wp_cli();

		add_action('init', array($this, 'register_all_events'));

		add_action('wp_ajax_wu_get_event_payload_preview', array($this, 'event_payload_preview'));

		add_action('rest_api_init', array($this, 'hooks_endpoint'));

		add_action('wu_model_post_save', array($this, 'log_transitions'), 10, 4);

	} // end init;

	/**
	 * Returns the payload to be displayed in the payload preview field.
	 * Log model transitions.
	 *
	 * @since 2.0.0
	 *
	 * @param string     $model The model name.
	 * @param array      $data The data being saved, serialized.
	 * @param array      $data_unserialized The data being saved, un-serialized.
	 * @param Base_Model $object The object being saved.
	 * @return void
	 */
	public function log_transitions($model, $data, $data_unserialized, $object) {

		if ($model === 'event') {

			return;

		} // end if;

		/*
		 * Editing Model
		 */
		if (wu_get_isset($data_unserialized, 'id')) {

			$original = $object->_get_original();

			$diff = \WP_Ultimo\Objects\Limitations::array_recursive_diff($data_unserialized, $original);

			$keys_to_remove = apply_filters('wu_exclude_transitions_keys', array(
				'meta',
				'last_login',
				'ips',
				'query_class',
				'settings',
			));

			foreach ($keys_to_remove as $key_to_remove) {

				unset($diff[$key_to_remove]);

			} // end foreach;

			/**
			 * If empty, go home.
			 */
			if (empty($diff)) {

				return;

			} // end if;

			$changed = array();

			/**
			 * Loop changed data.
			 */
			foreach ($diff as $key => $new_value) {

				$old_value = wu_get_isset($original, $key, '');

				$changed[$key] = array(
					'old_value' => $old_value,
					'new_value' => $new_value,
				);

			} // end foreach;

			$event_data = array(
				'severity'    => Event::SEVERITY_INFO,
				'slug'        => 'changed',
				'object_type' => $model,
				'object_id'   => $object->get_id(),
				'payload'     => $changed,
			);

		} else {

			$event_data = array(
				'severity'    => Event::SEVERITY_INFO,
				'slug'        => 'created',
				'object_type' => $model,
				'object_id'   => $object->get_id(),
				'payload'     => array(),
			);

		} // end if;

		if (!empty($_POST) && is_user_logged_in()) {

			$event_data['initiator'] = 'manual';
			$event_data['author_id'] = get_current_user_id();

		} // end if;

		return wu_create_event($event_data);

	} // end log_transitions;

	/**
	 * Returns the payload to be displayed in the payload preview field.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function event_payload_preview() {

		if (!wu_request('event')) {

			wp_send_json_error(new \WP_Error('error', __('No event was selected.', 'wp-ultimo')));

		} // end if;

		$slug = wu_request('event');

		if (!$slug) {

			wp_send_json_error(new \WP_Error('not-found', __('Event was not found.', 'wp-ultimo')));

		} // end if;

		$event = wu_get_event_type($slug);

		if (!$event) {

			wp_send_json_error(new \WP_Error('not-found', __('Data not found.', 'wp-ultimo')));

		} else {

			wp_send_json_success($event['payload']);

		} // end if;

	} // end event_payload_preview;

	/**
	 * Returns the list of event types to register.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_event_type_as_options() {
		/*
		 * We use this to order the options.
		*/
		$event_type_settings = wu_get_setting('saving_type', array());

		$types = array(
			'id'         => '$id',
			'title'      => '$title',
			'desc'       => '$desc',
			'class_name' => '$class_name',
			'active'     => 'in_array($id, $active_gateways, true)',
			'active'     => 'in_array($id, $active_gateways, true)',
			'gateway'    => '$class_name', // Deprecated.
			'hidden'     => false,
		);

		$types = array_filter($types, function($item) {

			return $item['hidden'] === false;

		});

		return $types;

	} // end get_event_type_as_options;

	/**
	 * Add a new event.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The slug of the event. Something like payment_received.
	 * @param array  $payload with the events information.
	 *
	 * @return array with returns message for now.
	 */
	public function do_event($slug, $payload) {

		$registered_event = $this->get_event($slug);

		if (!$registered_event) {

			return array('error' => 'Event not found');

		} // end if;

		$payload_diff = array_diff_key($registered_event['payload'], $payload);

		if (isset($payload_diff[0])) {

			foreach ($payload_diff[0] as $diff_key => $diff_value) {

				return array('error' => 'Param required:' . $diff_key);

			} // end foreach;

		} // end if;

		do_action('wu_event', $slug, $payload);

		do_action("wu_event_{$slug}", $payload);

		/**
		 * Saves in the database
		 */
		$this->save_event($slug, $payload);

	} // end do_event;

	/**
	 * Register a new event to be used as param.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The slug of the event. Something like payment_received.
	 * @param array  $args with the events information.
	 *
	 * @return true
	 */
	public function register_event($slug, $args) {

		$this->events[$slug] = $args;

		return true;

	} // end register_event;

	/**
	 * Returns the list of available webhook events.
	 *
	 * @since 2.0.0
	 * @return array $events with all events.
	 */
	public function get_events() {

		return $this->events;

	} // end get_events;

	/**
	 * Returns the list of available webhook events.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug of the event.
	 * @return array $event with event params.
	 */
	public function get_event($slug) {

		$events = $this->get_events();

		if ($events) {

			foreach ($events as $key => $event) {

				if ($key === $slug) {

					return $event;

				} // end if;

			} // end foreach;

		} // end if;

		return false;

	} // end get_event;

	/**
	 * Saves event in the database.
	 *
	 * @param string $slug of the event.
	 * @param array  $payload with event params.
	 * @return void.
	 */
	public function save_event($slug, $payload) {

		$event = new Event(array(
			'object_id'    => $payload['object_id'],
			'object_type'  => $payload['object_type'],
			'severity'     => $payload['type'],
			'date_created' => current_time('Y-m-d H:i:s'),
			'slug'         => strtolower($slug),
			'payload'      => $payload,
		));

		$event->save();

	} // end save_event;

	/**
	 * Registers the list of default events.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_all_events() {

		/**
		 * Payment Received.
		 */
		wu_register_event_type('payment_received', $args = array(
			'name'            => __('Payment Received', 'wp-ultimo'),
			'desc'            => __('This event is fired every time a new payment is received, regardless of the payment status.', 'wp-ultimo'),
			'payload'         => array_merge(
				wu_generate_event_payload('payment'),
				wu_generate_event_payload('membership'),
				wu_generate_event_payload('customer')
			),
			'deprecated_args' => array(
				'user_id' => 'customer_user_id',
				'amount'  => 'payment_total',
				'gateway' => 'payment_gateway',
				'status'  => 'payment_status',
				'date'    => 'payment_date_created',
			),
		));

		/**
		 * Site Published.
		 */
		wu_register_event_type('site_published', $args = array(
			'name'            => __('Site Published', 'wp-ultimo'),
			'desc'            => __('This event is fired every time a new site is created tied to a membership, or transitions from a pending state to a published state.', 'wp-ultimo'),
			'payload'         => array_merge(
				wu_generate_event_payload('site'),
				wu_generate_event_payload('customer'),
				wu_generate_event_payload('membership')
			),
			'deprecated_args' => array(),
		));

		/**
		 * Domain Mapping Added
		 */
		wu_register_event_type('domain_created', $args = array(
			'name'            => __('New Domain Mapping Added', 'wp-ultimo'),
			'desc'            => __('This event is fired every time a new domain mapping is added by a customer.', 'wp-ultimo'),
			'payload'         => array_merge(
				wu_generate_event_payload('domain'),
				wu_generate_event_payload('site'),
				wu_generate_event_payload('membership')
			),
			'deprecated_args' => array(
				'user_id'       => 1,
				'user_site_id'  => 1,
				'mapped_domain' => 'mydomain.com',
				'user_site_url' => 'http://test.mynetwork.com/',
				'network_ip'    => '125.399.3.23',
			),
		));

		do_action('wu_register_all_events');

	} // end register_all_events;

	/**
	 * Create a endpoint to retrieve all available event hooks.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed
	 */
	public function hooks_endpoint() {

		if (!wu_get_setting('enable_api', true)) {

			return;

		} // end if;

		$api = \WP_Ultimo\API::get_instance();

		register_rest_route($api->get_namespace(), '/hooks', array(
			'methods'             => 'GET',
			'callback'            => array($this, 'get_hooks_rest'),
			'permission_callback' => array($api, 'check_authorization'),
		));

	} // end hooks_endpoint;

	/**
	 * Return all event types for the REST API request.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_REST_Request $request The request sent.
	 * @return mixed
	 */
	public function get_hooks_rest($request) {

		$response = wu_get_event_types();

		return rest_ensure_response($response);

	} // end get_hooks_rest;

} // end class Event_Manager;
