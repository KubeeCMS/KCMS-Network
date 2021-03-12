<?php
/**
 * The Webhook model.
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
 * Webhook model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Webhook extends Base_Model {

	/**
	 * The name of the webhook.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = '';

	/**
	 * URL to be called when this webhook is triggered.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $webhook_url = '';

	/**
	 * Event that should trigger this webhook.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $event = '';

	/**
	 * The number of times this webhook was triggered.
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $event_count = 0;

	/**
	 * Is this webhook active?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $active = true;

	/**
	 * Is this webhook hidden?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $hidden = false;

	/**
	 * Integration name.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $integration = 'manual';

	/**
	 * Date when this was created.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * Date when this webhook last failed.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_last_failed;

	/**
	 * Query Class to the static query methods.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Webhooks\\Webhook_Query';

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
			'name'        => 'required|min:2',
			'webhook_url' => 'required|url:http,https',
			'event'       => 'required',
			'event_count' => 'default:0',
			'active'      => 'default:1',
			'hidden'      => 'default:0',
			'integration' => 'required|min:2',
		);

	} // end validation_rules;

	/**
	 * Get the value of name.
	 *
	 * @return string
	 */
	public function get_name() {

		return $this->name;

	} // end get_name;

	/**
	 * Set the value of name.
	 *
	 * @param string $name The name of the Webhook.
	 */
	public function set_name($name) {

		$this->name = $name;

	} // end set_name;

	/**
	 * Get the value of webhook_url.
	 *
	 * @return string
	 */
	public function get_webhook_url() {

		return $this->webhook_url;

	} // end get_webhook_url;

	/**
	 * Set the value of webhook_url.
	 *
	 * @param string $webhook_url The new URL for the webhook call.
	 */
	public function set_webhook_url($webhook_url) {

		$this->webhook_url = $webhook_url;

	} // end set_webhook_url;

	/**
	 * Get the value of event.
	 *
	 * @return string
	 */
	public function get_event() {

		return $this->event;

	} // end get_event;

	/**
	 * Set the value of event.
	 *
	 * @param string $event Event of the webhook.
	 */
	public function set_event($event) {

		$this->event = $event;

	} // end set_event;

	/**
	 * Get the value of event_count.
	 *
	 * @return int The number of times this webhook was triggered and sent.
	 */
	public function get_event_count() {

		return (int) $this->event_count;

	} // end get_event_count;

	/**
	 * Set the value of event_count.
	 *
	 * @param int $event_count The new event_count.
	 */
	public function set_event_count($event_count) {

		$this->event_count = $event_count;

	} // end set_event_count;

	/**
	 * Check if this particular mapping is active.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_active() {

		return (bool) $this->active;

	} // end is_active;

	/**
	 * Sets the active state of this model object;
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $active Wether or not this domain is active.
	 * @return void
	 */
	public function set_active($active) {

		$this->active = (bool) wu_string_to_bool($active);

	} // end set_active;

	/**
	 * Get is this webhook hidden?
	 *
	 * @return boolean.
	 */
	public function is_hidden() {

		return (bool) $this->hidden;

	} // end is_hidden;

	/**
	 * Set is this webhook hidden?
	 *
	 * @param boolean $hidden Is this webhook hidden.
	 */
	public function set_hidden($hidden) {

		$this->hidden = $hidden;

	} // end set_hidden;

	/**
	 * Get integration name.
	 *
	 * @return string
	 */
	public function get_integration() {

		return $this->integration;

	} // end get_integration;

	/**
	 * Get date when this was created..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_created() {

		return $this->date_created;

	} // end get_date_created;

	/**
	 * Get date when this was created..
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_last_failed() {

		return $this->date_last_failed;

	} // end get_date_last_failed;

	/**
	 * Set date when this was created..
	 *
	 * @since 2.0.0
	 * @param string $date_created Date when this was created.
	 * @return void
	 */
	public function set_date_created($date_created) {

		$this->date_created = $date_created;

	} // end set_date_created;

	/**
	 * Set integration name.
	 *
	 * @param string $integration Integration name.
	 */
	public function set_integration($integration) {

		$this->integration = $integration;

	} // end set_integration;

	/**
	 * Get the last fail date.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_last_fail() {

		return $this->date_last_fail;

	} // end get_date_last_fail;

	/**
	 * Set the last fail date.
	 *
	 * @param string $date_last_fail Last fail date.
	 */
	public function set_date_last_fail($date_last_fail) {

		$this->date_last_fail = $date_last_fail;

	} // end set_date_last_fail;

} // end class Webhook;
