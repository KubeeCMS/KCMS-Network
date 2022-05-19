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
	 * Constructs the object via the constructor arguments
	 *
	 * @since 2.0.7
	 *
	 * @param mixed $object Std object with model parameters.
	 */
	public function __construct($object = null) {

		$object = (array) $object;

		if (!wu_get_isset($object, 'migrated_from_id')) {

			unset($object['migrated_from_id']);

		} // end if;

		parent::__construct($object);

	} // end __construct;

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
			'notice_type' => 'in:info,success,warning,error',
			'status'      => 'default:publish',
			'name'        => 'default:title',
			'title'       => 'required|min:2',
			'content'     => 'required|min:3',
			'type'        => 'required|in:broadcast_email,broadcast_notice|default:broadcast_notice',
		);

	} // end validation_rules;

	/**
	 * Get the id of the original 1.X model that was used to generate this item on migration.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_migrated_from_id() {

		if ($this->migrated_from_id === null) {

			$this->migrated_from_id = $this->get_meta('migrated_from_id', 0);

		} // end if;

		return $this->migrated_from_id;

	} // end get_migrated_from_id;

	/**
	 * Set the id of the original 1.X model that was used to generate this item on migration.
	 *
	 * @since 2.0.0
	 * @param int $migrated_from_id The ID of the original 1.X model that was used to generate this item on migration.
	 * @return void
	 */
	public function set_migrated_from_id($migrated_from_id) {

		$this->meta['migrated_from_id'] = $migrated_from_id;

		$this->migrated_from_id = $this->meta['migrated_from_id'];

	} // end set_migrated_from_id;

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
	 * @param string $message_targets The targets for this broadcast.
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
	 * @param string $notice_type Can be info, success, warning or error.
	 * @options info,success,warning,danger
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
	 * @param string $name This broadcast name, which is used as broadcast title as well.
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
