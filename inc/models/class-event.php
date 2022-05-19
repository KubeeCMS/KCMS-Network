<?php
/**
 * The Event model for the Event Mappings.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Base_Model;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Event model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Event extends Base_Model {

	const SEVERITY_SUCCESS = 1;
	const SEVERITY_NEUTRAL = 2;
	const SEVERITY_INFO    = 3;
	const SEVERITY_WARNING = 4;
	const SEVERITY_FATAL   = 5;

	/**
	 * Severity of the problem.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $severity;

	/**
	 * Date when the event was created.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * Initiator of this event.
	 *
	 * Events can be run by WP Ultimo, saved as 'system', or
	 * by people (admins, customers), saved as 'manual'.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $initiator;

	/**
	 * The author of the action, saved as the user_id.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $author_id = 0;

	/**
	 * Object type associated with this event.
	 *
	 * Can be one one the following:
	 * - network, for events concerning the entire network;
	 * - site, for events that concern a specific site;
	 * - customer, for events that concern a specific customer;
	 * - domain, for events that concern a specific domain;
	 * - membership, for events that concern a specific membership;
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $object_type;

	/**
	 * ID of the related objects.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	protected $object_id;

	/**
	 * Slug of the event.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $slug;

	/**
	 * Payload of the event.
	 *
	 * @since 2.0.0
	 * @var object
	 */
	protected $payload;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Events\\Event_Query';

	/**
	 * Set the validation rules for this particular model.
	 *
	 * To see how to setup rules, check the documentation of the
	 * validation library we are using: https://github.com/rakit/validation
	 *
	 * @since 2.0.0
	 * @link https://github.com/rakit/validation
	 * @return array
	 */
	public function validation_rules() {

		return array(
			'severity'    => 'required|numeric|between:1,5',
			'payload'     => 'required',
			'object_type' => 'required|alpha_dash|lowercase',
			'object_id'   => 'integer|default:0',
			'author_id'   => 'integer|default:0',
			'slug'        => 'required|alpha_dash',
			'initiator'   => 'required|in:system,manual'
		);

	} // end validation_rules;

	/**
	 * Get severity of the problem..
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_severity() {

		return (int) $this->severity;

	} // end get_severity;

	/**
	 * Returns the Label for a given severity level.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_severity_label() {

		$labels = array(
			Event::SEVERITY_SUCCESS => __('Success', 'wp-ultimo'),
			Event::SEVERITY_NEUTRAL => __('Neutral', 'wp-ultimo'),
			Event::SEVERITY_INFO    => __('Info', 'wp-ultimo'),
			Event::SEVERITY_WARNING => __('Warning', 'wp-ultimo'),
			Event::SEVERITY_FATAL   => __('Fatal', 'wp-ultimo'),
		);

		return isset($labels[$this->get_severity()]) ? $labels[$this->get_severity()] : __('Note', 'wp-ultimo');

	} // end get_severity_label;

	/**
	 * Gets the classes for a given severity level.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_severity_class() {

		$classes = array(
			Event::SEVERITY_SUCCESS => 'wu-bg-green-200 wu-text-green-700',
			Event::SEVERITY_NEUTRAL => 'wu-bg-gray-200 wu-text-gray-700',
			Event::SEVERITY_INFO    => 'wu-bg-blue-200 wu-text-blue-700',
			Event::SEVERITY_WARNING => 'wu-bg-yellow-200 wu-text-yellow-700',
			Event::SEVERITY_FATAL   => 'wu-bg-red-200 wu-text-red-700',
		);

		return isset($classes[$this->get_severity()]) ? $classes[$this->get_severity()] : '';

	} // end get_severity_class;

	/**
	 * Set severity of the problem..
	 *
	 * @since 2.0.0
	 * @param int $severity Severity of the problem.
	 * @return void
	 */
	public function set_severity($severity) {

		$this->severity = $severity;

	} // end set_severity;

	/**
	 * Get date when the event was created..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_created() {

		return $this->date_created;

	} // end get_date_created;

	/**
	 * Set date when the event was created..
	 *
	 * @since 2.0.0
	 * @param string $date_created Date when the event was created.
	 * @return void
	 */
	public function set_date_created($date_created) {

		$this->date_created = $date_created;

	} // end set_date_created;

	/**
	 * Get payload of the event..
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_payload() {

		$payload = (array) maybe_unserialize($this->payload);

		return $payload;

	} // end get_payload;

	/**
	 * Set payload of the event..
	 *
	 * @since 2.0.0
	 * @param object $payload Payload of the event.
	 * @return void
	 */
	public function set_payload($payload) {

		$this->payload = $payload;

	} // end set_payload;

	/**
	 * Get message for the event.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_message() {

		$message = self::get_default_system_messages($this->slug);

		return $this->interpolate_message($message, $this->get_payload());

	} // end get_message;

	/**
	 * Interpolates the value of a message and its placeholders with the contents of the payload.
	 *
	 * @since 2.0.0
	 *
	 * @param string $message The message with placeholders.
	 * @param array  $payload Key => value based array.
	 * @return string
	 */
	public function interpolate_message($message, $payload) {

		$payload = json_decode(json_encode($payload), true);

		$interpolation_keys = array();

		foreach ($payload as $key => &$value) {

			$interpolation_keys[] = "{{{$key}}}";

			if (is_array($value)) {

				$value = implode(' &rarr; ', wu_array_flatten($value));

			} // end if;

		} // end foreach;

		$interpolation = array_combine($interpolation_keys, $payload);

		$interpolation['{{payload}}'] = implode(' - ', wu_array_flatten($payload, true));

		$interpolation['{{model}}'] = wu_slug_to_name($this->object_type);

		$interpolation['{{object_id}}'] = $this->object_id;

		return strtr($message, $interpolation);

	} // end interpolate_message;

	/**
	 * Returns the default system messages for events.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug The slug of the event.
	 * @return string
	 */
	public static function get_default_system_messages($slug) {

		$default_messages = array();

		$default_messages['changed'] = __('The <strong>{{model}}</strong> #{{object_id}} was changed: {{payload}}', 'wp-ultimo');
		$default_messages['created'] = __('The <strong>{{model}}</strong> #{{object_id}} was created.', 'wp-ultimo');

		$default_messages = apply_filters('wu_get_default_system_messages', $default_messages);

		return wu_get_isset($default_messages, $slug, __('No Message', 'wp-ultimo'));

	} // end get_default_system_messages;


	/**
	 * Get by people (admins, customers), saved as 'manual'.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_initiator() {

		return $this->initiator;

	} // end get_initiator;

	/**
	 * Set by people (admins, customers), saved as 'manual'.
	 *
	 * @since 2.0.0
	 *
	 * @param string $initiator The type of user responsible for initiating the event. There are two options: Manual and System. By default, the event is saved as manual.
	 * @return void
	 */
	public function set_initiator($initiator) {

		$this->initiator = $initiator;

	} // end set_initiator;

	/**
	 * Get the author of the action, saved as the user_id.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_author_id() {

		return $this->author_id;

	} // end get_author_id;

	/**
	 * Returns the user associated with this author.
	 *
	 * @since 2.0.0
	 * @return WP_User
	 */
	public function get_author_user() {

		if ($this->author_id) {

			$user = get_user_by('id', $this->author_id);

			if ($user) {

				return $user;

			} // end if;

		} // end if;

	} // end get_author_user;

	/**
	 * Returns the authors' display name.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_author_display_name() {

		$user = $this->get_author_user();

		if ($user) {

			return $user->display_name;

		} // end if;

	} // end get_author_display_name;

	/**
	 * Returns the authors' email address.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_author_email_address() {

		$user = $this->get_author_user();

		if ($user) {

			return $user->user_email;

		} // end if;

	} // end get_author_email_address;

	/**
	 * Set the author of the action, saved as the user_id.
	 *
	 * @since 2.0.0
	 * @param int $author_id The user responsible for creating the event. By default, the event is saved with the current user_id.
	 * @return void
	 */
	public function set_author_id($author_id) {

		$this->author_id = $author_id;

	} // end set_author_id;

	/**
	 * Get the object of this event.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function get_object() {

		$object_type = $this->get_object_type();

		$function_name = "wu_get_{$object_type}";

		if (function_exists($function_name)) {

			return $function_name($this->get_object_id());

		} // end if;

		return false;

	} // end get_object;

	/**
	 * Polyfill for the get_object method.
	 *
	 * @since 2.0.0
	 * @return false|object
	 */
	public function get_membership() {

		$object_type = $this->get_object_type();

		if ($object_type !== 'membership') {

			return false;

		} // end if;

		return $this->get_object();

	} // end get_membership;

	/**
	 * Polyfill for the get_object method.
	 *
	 * @since 2.0.0
	 * @return false|object
	 */
	public function get_product() {

		$object_type = $this->get_object_type();

		if ($object_type !== 'product') {

			return false;

		} // end if;

		return $this->get_object();

	} // end get_product;

	/**
	 * Polyfill for the get_object method.
	 *
	 * @since 2.0.0
	 * @return false|object
	 */
	public function get_site() {

		$object_type = $this->get_object_type();

		if ($object_type !== 'site') {

			return false;

		} // end if;

		return $this->get_object();

	} // end get_site;

	/**
	 * Polyfill for the get_object method.
	 *
	 * @since 2.0.0
	 * @return false|object
	 */
	public function get_customer() {

		$object_type = $this->get_object_type();

		if ($object_type !== 'customer') {

			return false;

		} // end if;

		return $this->get_object();

	} // end get_customer;

	/**
	 * Polyfill for the get_object method.
	 *
	 * @since 2.0.0
	 * @return false|object
	 */
	public function get_payment() {

		$object_type = $this->get_object_type();

		if ($object_type !== 'payment') {

			return false;

		} // end if;

		return $this->get_object();

	} // end get_payment;

	/**
	 * Get the object type associated with this event.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_object_type() {

		return $this->object_type;

	} // end get_object_type;

	/**
	 * Set the object type associated with this event.
	 *
	 * @since 2.0.0
	 * @param string $object_type The type of object related to this event. It's usually the model name.
	 * @return void
	 */
	public function set_object_type($object_type) {

		$this->object_type = $object_type;

	} // end set_object_type;

	/**
	 * Get the object type associated with this event.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_slug() {

		return $this->slug;

	} // end get_slug;

	/**
	 * Set the object type associated with this event.
	 *
	 * @since 2.0.0
	 * @param string $slug The event slug. It needs to be unique and preferably make it clear what it is about. Example: account_created is about creating an account.
	 * @return void
	 */
	public function set_slug($slug) {

		$this->slug = $slug;

	} // end set_slug;

	/**
	 * Get iD of the related objects..
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_object_id() {

		return $this->object_id;

	} // end get_object_id;

	/**
	 * Set iD of the related objects.
	 *
	 * @since 2.0.0
	 * @param int $object_id The ID of the related objects.
	 * @return void
	 */
	public function set_object_id($object_id) {

		$this->object_id = $object_id;

	} // end set_object_id;

	/**
	 * Transform the object into an assoc array.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_array() {

		$array = parent::to_array();

		$array['payload'] = $this->get_payload();

		$array['message'] = $this->get_message();

		$array['severity_label'] = $this->get_severity_label();

		$array['severity_classes'] = $this->get_severity_class();

		$array['author'] = array();

		if ($this->get_initiator() === 'manual') {

			$user = get_user_by('ID', $this->get_author_id());

			if ($user) {

				$array['author'] = (array) $user->data;

				unset($array['author']['user_pass']);
				unset($array['author']['user_activation_key']);

				$array['author']['avatar'] = get_avatar_url($this->get_author_id(), array(
					'default' => 'identicon',
				));

			} // end if;

		} // end if;

		return $array;

	} // end to_array;

	/**
	 * Override to clear event count.
	 *
	 * @since 2.0.0
	 * @return int|false
	 */
	public function save() {

		if (!$this->exists() && function_exists('get_current_user_id')) {

			$user_id = get_current_user_id();

			delete_site_transient("wu_{$user_id}_unseen_events_count");

		} // end if;

		return parent::save();

	} // end save;

} // end class Event;
