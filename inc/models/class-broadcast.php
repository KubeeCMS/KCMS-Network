<?php
/**
 * The Broadcast model for the Broadcasts.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use WP_Ultimo\Models\Post_Base_Model;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Broadcast model class. Implements the Base Model.
 *
 * @since 2.0.0
 */
class Broadcast extends Post_Base_Model {
	/**
	 * Post model.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $model = 'broadcast';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var mixed
	 */
	protected $query_class = '\\WP_Ultimo\\Database\\Broadcasts\\Broadcast_Query';

	/**
	 * Post type.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $type = 'broadcast_notice';

	/**
	 * Set the allowed types to prevent saving wrong types.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_types = array('broadcast_email', 'broadcast_notice');

	/**
	 * Set the allowed status to prevent saving wrong status.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $allowed_status = array('publish', 'draft');

	/**
	 * Broadcast status.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $status = 'publish';

	/**
	 * Notice type
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $notice_type;

	/**
	 * Get name of the broadcast
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_name() {

		return $this->get_title();

	} // end get_name;

	/**
	 * Get title of the broadcast
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_title() {

		return $this->title;

	} // end get_title;

	/**
	 * Get notice type
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_notice_type() {

		if ($this->notice_type === null) {

			$this->notice_type = $this->get_meta('notice_type', 'success');

		} // end if;

		return $this->notice_type;

	} // end get_notice_type;

	/**
	 * Get the message targets.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_message_targets() {

		return $this->get_meta('message_targets');

	} // end get_message_targets;

	/**
	 * Set the message product and/or customer targets.
	 *
	 * @since 2.0.0
	 *
	 * @param string $message_targets The message targets.
	 * @return void
	 */
	public function set_message_targets($message_targets) {

		$this->meta['message_targets'] = $message_targets;

	} // end set_message_targets;

	/**
	 * Set the type of the notice.
	 *
	 * @since 2.0.0
	 *
	 * @param string $notice_type Can be info, success, warning, danger.
	 * @return void
	 */
	public function set_notice_type($notice_type) {

		$this->meta['notice_type'] = $notice_type;

		$this->notice_type = $this->meta['notice_type'];

	} // end set_notice_type;

	/**
	 * Set title using the name parameter.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The name being set as title.
	 * @return void
	 */
	public function set_name($name) {

		$this->set_title($name);

	} // end set_name;

	/**
	 * Adds checks to prevent saving the model with the wrong type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type The type being set.
	 * @return void
	 */
	public function set_type($type) {

		if (!in_array($type, $this->allowed_types, true)) {

			$type = 'broadcast_notice';

		} // end if;

		$this->type = $type;

	} // end set_type;

	/**
	 * * Adds checks to prevent saving the model with the wrong status.
	 *
	 * @since 2.0.0
	 *
	 * @param string $status The status being set.
	 * @return void
	 */
	public function set_status($status) {

		if (!in_array($status, $this->allowed_status, true)) {

			$status = 'publish';

		} // end if;

		$this->status = $status;

	} // end set_status;

} // end class Broadcast;
