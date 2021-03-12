<?php
/**
 * Abstract base model for our data types.
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Models;

use \WP_Ultimo\Helpers\Hash;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Abstract base model for our data types
 *
 * This class is the base class that is extended by all of our data types
 * such as plans, coupons, broadcasts, domains, etc.
 *
 * @since 2.0.0
 */
abstract class Base_Model implements \JsonSerializable {

	/**
	 * ID of the object
	 *
	 * @since 2.0.0
	 * @var integer
	 */
	protected $id = 0;

	/**
	 * Model name.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $model = '';

	/**
	 * Holds the Query Class for this particular object type.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $query_class = '';

	/**
	 * Holds meta fields we want to always save.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $meta_fields = array();

	/**
	 * Model creation date.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_created = '';

	/**
	 * Model last modification date.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $date_modified = '';

	/**
	 * Meta data holder.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $meta = array();

	/**
	 * The id of the original 1.X model that was used to generate this item on migration.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	public $migrated_from_id;

	/**
	 * Set this to true to skip validations when saving.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $skip_validation = false;

	/**
	 * Keeps a copy in memory of the object being edited.
	 *
	 * @since 2.0.0
	 * @var Base_Model
	 */
	protected $_original;

	/**
	 * Mocked status. Used to suppress errors.
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	public $_mocked = false;

	/**
	 * Constructs the object via the constructor arguments
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $object Std object with model parameters.
	 */
	public function __construct($object = null) {

		$this->model = sanitize_key((new \ReflectionClass($this))->getShortName());

		if (is_array($object)) {

			$object = (object) $object;

		} // end if;

		if (!is_object($object)) {

			return;

		} // end if;

		$this->setup_model($object);

	} // end __construct;

	/**
	 * Get the value of slug
	 *
	 * @return mixed
	 */
	public function get_slug() {

		return $this->slug;

	} // end get_slug;

	/**
	 * Set the value of slug
	 *
	 * @param mixed $slug The slug.
	 */
	public function set_slug($slug) {

		$this->slug = $slug;

	} // end set_slug;

	/**
	 * Returns a hashed version of the id. Useful for displaying data publicly.
	 *
	 * @param string $field Field to use to generate the hash.
	 * @since 2.0.0
	 * @return string
	 */
	public function get_hash($field = 'id') {

		$value = call_user_func(array($this, "get_{$field}"));

		if (!is_numeric($value)) {

			_doing_it_wrong(__METHOD__, __('You can only use numeric fields to generate hashes.', 'wp-ultimo'), '2.0.0');

			return false;

		} // end if;

		return Hash::encode($value, $this->model);

	} // end get_hash;

	/**
	 * Setup properties.
	 *
	 * @param object $object Row from the database.
	 *
	 * @access private
	 * @since  2.0.0
	 * @return bool
	 */
	private function setup_model($object) {

		if (!is_object($object)) {

			return false;

		} // end if;

		$vars = get_object_vars($object);

		$this->attributes($vars);

		if (empty($this->id)) {

			return false;

		} // end if;

		return true;

	} // end setup_model;

	/**
	 * Sets the attributes of the model using the setters available.
	 *
	 * @since 2.0.0
	 *
	 * @param array $atts Key-value pairs of model attributes.
	 * @return \WP_Ultimo\Models\Base_Model
	 */
	public function attributes($atts) {

		foreach ($atts as $key => $value) {

			if ($key === 'meta' && is_array($value)) {

				$this->meta = $value;

			} // end if;

			if (method_exists($this, "set_$key")) {

				call_user_func(array($this, "set_$key"), $value);

			} // end if;

		} // end foreach;

		/*
		 * Keeps the original.
		 */
		if ($this->_original === null) {

			$original = get_object_vars($this);

			unset($original['_original']);

			$this->_original = $original;

		} // end if;

		return $this;

	} // end attributes;

	/**
	 * Return the model schema. useful to list all models fields.
	 *
	 * @since 2.0.0
	 * @return Schema
	 */
	public static function get_schema() {

		$instance = new static();

		$query_class = new $instance->query_class;

		$reflector = new \ReflectionObject($query_class);

		$method = $reflector->getMethod('get_columns');

		$method->setAccessible(true);

		$columns = $method->invoke($query_class);

		return array_map(
			function ($column) {
				return $column->to_array();
			},
			$columns
		);

	} // end get_schema;

	/**
	 * Checks if this model was already saved to the database.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function exists() {

		return !empty($this->id);

	} // end exists;

	/**
	 * Gets a single database row by the primary column ID, possibly from cache.
	 *
	 * @since 2.0.0
	 *
	 * @param int $item_id The item id.
	 *
	 * @return object Base_Model|false
	 */
	public static function get_by_id($item_id) {

		if (empty($item_id)) {

			return false;

		} // end if;

		$instance = new static();

		$query_class = new $instance->query_class;

		return $query_class->get_item($item_id);

	} // end get_by_id;

	/**
	 * Gets a single database row by the hash, possibly from cache.
	 *
	 * @since 2.0.0
	 *
	 * @param int $item_hash The item hash.
	 *
	 * @return Base_Model|false
	 */
	public static function get_by_hash($item_hash) {

		$instance = new static();

		$item_id = Hash::decode($item_hash, sanitize_key((new \ReflectionClass(static::class))->getShortName()));

		$query_class = new $instance->query_class;

		return $query_class->get_item($item_id);

	} // end get_by_hash;

	/**
	 * Gets a model instance by a column value.
	 *
	 * @since 2.0.0
	 *
	 * @param string $column The name of the column to query for.
	 * @param string $value Value to search for.
	 * @return Base_Model|false
	 */
	public static function get_by($column, $value) {

		$instance = new static();

		$query_class = new $instance->query_class;

		return $query_class->get_item_by($column, $value);

	} // end get_by;

	/**
	 * Wrapper for a Query call.
	 *
	 * @since 2.0.0
	 *
	 * @param array $query Arguments for the query.
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public static function get_items($query) {

		$instance = new static();

		return (new $instance->query_class($query))->query();

	} // end get_items;

	/**
	 * Wrapper for a Query call, but returns the list as arrays.
	 *
	 * @since 2.0.0
	 *
	 * @param array $query Arguments for the query.
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public static function get_items_as_array($query = array()) {

		$instance = new static();

		$list = (new $instance->query_class($query))->query();

		return array_map(function($item) {

			return $item->to_array();

		}, $list);

	} // end get_items_as_array;

	/**
	 * Get the ID of the model.
	 *
	 * @access public
	 * @since  2.0.0
	 * @return int
	 */
	public function get_id() {

		return abs($this->id);

	} // end get_id;

	/**
	 * Check if this model has a job running.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function has_running_jobs() {

		$jobs = wu_get_scheduled_actions(array(
			'status' => \ActionScheduler_Store::STATUS_RUNNING,
			'args'   => array(
				"{$this->model}_id" => $this->get_id(),
			),
		));

		return $jobs;

	} // end has_running_jobs;

	/**
	 * Set iD of the object.
	 *
	 * @since 2.0.0
	 * @param integer $id ID of the object.
	 * @return void
	 */
	private function set_id($id) {

		$this->id = $id;

	} // end set_id;

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

		return array();

	} // end validation_rules;

	/**
	 * Validates the rules and make sure we only save models when necessary.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error
	 */
	public function validate() {

		if ($this->skip_validation) {

			return true;

		} // end if;

		$validator = new \WP_Ultimo\Helpers\Validator;

		$validator->validate($this->to_array(), $this->validation_rules());

		if ($validator->fails()) {

			return $validator->get_errors();

		} // end if;

		return true;

	} // end validate;

	/**
	 * Save (create or update) the model on the database.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function save() {

		$query_class = new $this->query_class;

		$data = get_object_vars($this);

		unset($data['_original']);

		$data_unserialized = $data;

		$meta = wu_get_isset($data, 'meta', array());

		$blocked_attributes = array(
			'query_class',
			'meta',
		);

		foreach ($blocked_attributes as $attribute) {

			unset($data[$attribute]);

		} // end foreach;

		$data = array_map('maybe_serialize', $data);

		$data = array_map(function($_data) {

			if (is_serialized($_data)) {

				$_data = addslashes($_data);

			} // end if;

			return $_data;

		}, $data);

		/**
		 * Filters the object data before it is stored into the database.
		 *
		 * @since 2.0.0
		 *
		 * @param array      $data The object data that will be stored, serialized.
		 * @param array      $data_unserialized The object data that will be stored.
		 * @param Base_Model $this The object instance.
		 */
		$data = apply_filters("wu_{$this->model}_pre_save", $data, $data_unserialized, $this);

		$is_valid_data = $this->validate();

		if (is_wp_error($is_valid_data) && !$this->skip_validation) {

			return $is_valid_data;

		} // end if;

		$saved = false;

		if (!$this->get_id()) {

			$new_id = $query_class->add_item($data);

			if ($new_id) {

				$this->id = $new_id;

				$saved = true;

			} // end if;

		} else {

			$saved = $query_class->update_item($this->get_id(), $data);

		} // end if;

		if (!empty($meta)) {

			$this->update_meta_batch($meta);

		} // end if;

		/**
		 * Fires after an object is stored into the database.
		 *
		 * @since 2.0.0
		 *
		 * @param array      $model The model slug.
		 * @param array      $data The object data that will be stored, serialized.
		 * @param array      $data_unserialized The object data that will be stored.
		 * @param Base_Model $this The object instance.
		 */
		do_action('wu_model_post_save', $this->model, $data, $data_unserialized, $this);

		/**
		 * Fires after an object is stored into the database.
		 *
		 * @since 2.0.0
		 *
		 * @param array      $data The object data that will be stored.
		 * @param Base_Model $this The object instance.
		 */
		do_action("wu_{$this->model}_post_save", $data, $this);

		return $saved;

	} // end save;

	/**
	 * Delete the model from the database.
	 *
	 * @since 2.0.0
	 *
	 * @return WP_Error|bool
	 */
	public function delete() {

		if (!$this->get_id()) {

			return new \WP_Error("wu_{$this->model}_delete_unsaved_item", __('Item not found.', 'wp-ultimo'));

		} // end if;

		/**
		 * Fires after an object is stored into the database.
		 *
		 * @since 2.0.0
		 *
		 * @param Base_Model $this The object instance.
		 */
		do_action("wu_{$this->model}_pre_delete", $this);

		$query_class = new $this->query_class;

		$result = $query_class->delete_item($this->get_id());

		/**
		 * Fires after an object is stored into the database.
		 *
		 * @since 2.0.0
		 *
		 * @param bool       $result True if the object was successfully deleted.
		 * @param Base_Model $this   The object instance.
		 */
		do_action("wu_{$this->model}_post_delete", $result, $this);

		return $result;

	} // end delete;

	/**
	 * Returns the meta type name.
	 *
	 * @since 2.0.0
	 *
	 * @return mixed Meta type name
	 */
	private function get_meta_type_name() {

		$query_class = new $this->query_class;

		// Maybe apply table prefix
		$table = !empty($query_class->prefix)
		? "{$query_class->prefix}_{$query_class->item_name}"
		: $query_class->item_name;

		// Return table if exists, or false if not
		return $table;

	} // end get_meta_type_name;

	/**
	 * Returns the meta table name.
	 *
	 * @since 2.0.0
	 *
	 * @return string|false Table name if exists, False if not
	 */
	private function get_meta_table_name() {

		$table = $this->get_meta_type_name();

		return _get_meta_table($table);

	} // end get_meta_table_name;

	/**
	 * Checks if metadata handling is available, i.e., if there is a meta table
	 * for this model and if the object already has an ID set.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	protected function is_meta_available() {

		if (!$this->get_meta_table_name()) {

			// _doing_it_wrong(__METHOD__, __('This model does not support metadata.', 'wp-ultimo'), '2.0.0');

			return false;

		} // end if;

		if (!$this->get_id() && !$this->_mocked) {

			// _doing_it_wrong(__METHOD__, __('Model metadata only works for already saved models.', 'wp-ultimo'), '2.0.0');

			return false;

		} // end if;

		return true;

	} // end is_meta_available;

	/**
	 * Returns the meta data, if set. Otherwise, returns the default.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key     The meta key.
	 * @param bool   $default The default value to be passed.
	 * @param bool   $single  To return single values or not.
	 * @return mixed
	 */
	public function get_meta($key, $default = false, $single = true) {

		if (!$this->is_meta_available()) {

			return $default;

		} // end if;

		$meta_type = $this->get_meta_type_name();

		if (metadata_exists($meta_type, $this->get_id(), $key)) {

			return get_metadata($meta_type, $this->get_id(), $key, $single);

		} // end if;

		return $default;

	} // end get_meta;

	/**
	 * Adds or updates meta data in batch.
	 *
	 * @since 2.0.0
	 *
	 * @param array $meta  An array of meta data in `'key' => 'value'` format.
	 * @return bool True on successful update, false on failure.
	 */
	public function update_meta_batch($meta) {

		if (!$this->is_meta_available()) {

			return false;

		} // end if;

		if (!is_array($meta)) {

			_doing_it_wrong(__METHOD__, __('This method expects an array as argument.', 'wp-ultimo'), '2.0.0');

			return false;

		} // end if;

		$meta_type = $this->get_meta_type_name();

		$success = true;

		foreach ($meta as $key => $value) {

			$success = update_metadata($meta_type, $this->get_id(), $key, $value);

			if (!$success) {
				// break;
			} // end if;

		} // end foreach;

		return $success;

	} // end update_meta_batch;

	/**
	 * Adds or updates the meta data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key  The meta key.
	 * @param mixed  $value The new meta value.
	 * @return int|bool The new meta field ID if a field with the given key didn't exist and was
	 *                  therefore added, true on successful update, false on failure.
	 */
	public function update_meta($key, $value) {

		if (!$this->is_meta_available()) {

			return false;

		} // end if;

		$meta_type = $this->get_meta_type_name();

		return update_metadata($meta_type, $this->get_id(), $key, $value);

	} // end update_meta;

	/**
	 * Deletes the meta data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key The meta key.
	 * @return bool True on successful delete, false on failure.
	 */
	public function delete_meta($key) {

		if (!$this->is_meta_available()) {

			return false;

		} // end if;

		$meta_type = $this->get_meta_type_name();

		return delete_metadata($meta_type, $this->get_id(), $key);

	} // end delete_meta;

	/**
	 * Queries object in the database.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Query arguments.
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public static function query($args = array()) {

		$instance = new static();

		$query_class = new $instance->query_class();

		$items = $query_class->query($args);

		return $items;

	} // end query;

	/**
	 * Transform the object into an assoc array.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_array() {

		$array = get_object_vars($this);

		unset($array['query_class']);
		unset($array['skip_validation']);
		unset($array['meta']);
		unset($array['meta_fields']);
		unset($array['_original']);
		unset($array['_mocked']);

		foreach ($array as $key => $value) {

			if (strpos('_', $key) === 0) {

				unset($array[$key]);

			} // end if;

		} // end foreach;

		return $array;

	} // end to_array;

	/**
	 * Convert data to Mapping instance
	 *
	 * Allows use as a callback, such as in `array_map`
	 *
	 * @param stdClass $data Raw mapping data.
	 * @return Mapping
	 */
	protected static function to_instance($data) {

		return new static($data);

	} // end to_instance;

	/**
	 * Convert list of data to Mapping instances
	 *
	 * @param stdClass[] $data Raw mapping rows.
	 * @return Domain[]
	 */
	protected static function to_instances($data) {

		return array_map(array(get_called_class(), 'to_instance'), $data);

	} // end to_instances;

	/**
	 * By default, we just use the to_array method, but you can rewrite this.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function to_search_results() {

		return $this->to_array();

	} // end to_search_results;

	/**
	 * Defines how we should encode this.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function jsonSerialize() {

		if (wp_doing_ajax() && wu_request('action') === 'wu_search') {

			return $this->to_search_results();

		} // end if;

		return $this->to_array();

	} // end jsonSerialize;

	/**
	 * Get the date when this model was created.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_created() {

		if (!wu_validate_date($this->date_created)) {

			return current_time('mysql');

		} // end if;

		return $this->date_created;

	} // end get_date_created;

	/**
	 * Get the date when this model was last modified.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_date_modified() {

		if (!wu_validate_date($this->date_modified)) {

			return current_time('mysql');

		} // end if;

		return $this->date_modified;

	} // end get_date_modified;

	/**
	 * Set model creation date.
	 *
	 * @since 2.0.0
	 * @param string $date_created Model creation date.
	 * @return void
	 */
	public function set_date_created($date_created) {

		$this->date_created = $date_created;

	} // end set_date_created;

	/**
	 * Set model last modification date.
	 *
	 * @since 2.0.0
	 * @param string $date_modified Model last modification date.
	 * @return void
	 */
	public function set_date_modified($date_modified) {

		$this->date_modified = $date_modified;

	} // end set_date_modified;

	/**
	 * Get the id of the original 1.X model that was used to generate this item on migration..
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_migrated_from_id() {

		return $this->migrated_from_id;

	} // end get_migrated_from_id;

	/**
	 * Set the id of the original 1.X model that was used to generate this item on migration..
	 *
	 * @since 2.0.0
	 * @param int $migrated_from_id The id of the original 1.X model that was used to generate this item on migration.
	 * @return void
	 */
	public function set_migrated_from_id($migrated_from_id) {

		$this->migrated_from_id = $migrated_from_id;

	} // end set_migrated_from_id;

	/**
	 * Checks if this model is a migration from 1.X.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_migrated() {

		return !empty($this->get_migrated_from_id());

	} // end is_migrated;

	/**
	 * Get all items.
	 *
	 * @since 2.0.0
	 *
	 * @param array $query_args If you need to select a type to get all.
	 * @return array With all items requested.
	 */
	public static function get_all($query_args = array()) {

		$instance = new static();

		$query_class = new $instance->query_class();

		$items = $query_class->query($query_args);

		return $items;

	} // end get_all;

	/**
	 * Creates a copy of the given model adn resets it's id to a 'new' state.
	 *
	 * @since 2.0.0
	 * @return \WP_Ultimo\Model\Base_Model
	 */
	public function duplicate() {

		$clone = clone $this;

		$clone->set_id(0);

		if (method_exists($clone, 'set_date_created')) {

			$clone->set_date_created(current_time('mysql'));

		} // end if;

		return $clone;

	} // end duplicate;

	/**
	 * Set set this to true to skip validations when saving..
	 *
	 * @since 2.0.0
	 * @param boolean $skip_validation Set this to true to skip validations when saving.
	 * @return void
	 */
	public function set_skip_validation($skip_validation = false) {

		$this->skip_validation = $skip_validation;

	} // end set_skip_validation;

	/**
	 * Returns the original parameters of the object.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function _get_original() {

		return $this->_original;

	} // end _get_original;

	/**
	 * Return arguments schema for the rest api.
	 *
	 * This function gets the information from the model and
	 * the database schema to automatic generate the arguments
	 * schema for the rest api.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $edit Context. In edit, some fields, like ids, are not mandatory.
	 * @return array The arguments for an endpoint.
	 */
	public static function get_arguments_for_rest($edit = false) {
		/*
		 * Check if validations rules are present.
		 */
		$required_fields = array();

		if (method_exists(static::class, 'validation_rules') && !$edit) {

			$validation_rules = (new static)->validation_rules();

			foreach ($validation_rules as $field => $validation_rule) {

				if (strpos($validation_rule, 'required|') !== false || $validation_rule === 'required') {

					$required_fields[] = $field;

				} // end if;

			} // end foreach;

		} // end if;

		$set_methods = array_filter(get_class_methods(static::class), function($method_name) {

			return preg_match('/^set_/', $method_name);

		});

		$name_methods_without_prefix_set = array_map(function($method_name) {

			return str_replace('set_', '', $method_name);

		}, $set_methods);

		$schema = array_filter(static::get_schema(), function($column) use ($name_methods_without_prefix_set) {

			return in_array($column['name'], $name_methods_without_prefix_set, true);

		});

		$reflector = new \ReflectionClass(static::class);

		$doc_block_factory = \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlockFactory::createInstance();

		$arguments = array();

		foreach ($schema as $column) {

			$doc_block = $doc_block_factory->create($reflector->getMethod("set_{$column['name']}")->getDocComment());

			$param = $doc_block->getTagsByName('param');

			if (isset($param[0]) && is_object($param[0])) {

				$arguments[$column['name']] = array(
					'description' => (string) $param[0]->getDescription(),
					'type'        => (string) $param[0]->getType(),
					'required'    => in_array($column['name'], $required_fields, true),
				);

				if (preg_match( '/^ENUM/', $column['type'])) {

					preg_match_all("/'(.*?)'/", $column['type'], $matches);

					if (isset($matches[1])) {

						$arguments[$column['name']]['enum'] = array_map('strtolower', $matches[1]);

					} // end if;

				} // end if;

			} // end if;

		} // end foreach;

		return $arguments;

	} // end get_arguments_for_rest;

} // end class Base_Model;
