<?php
/**
 * Note class
 *
 * @package WP_Ultimo
 * @subpackage Models
 * @since 2.0.0
 */

namespace WP_Ultimo\Objects;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Note class
 *
 * @since 2.0.0
 */
class Note {

	/**
	 * The Note content.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Initializes the object.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Array of key => values note fields.
	 */
	public function __construct($data = array()) {

		$this->attributes($data);

	} // end __construct;

	/**
	 * Loops through allowed fields and loads them.
	 *
	 * @since 2.0.0
	 *
	 * @param array $data Array of key => values note fields.
	 * @return void
	 */
	public function attributes($data) {

		$allowed_attributes = array_keys(self::fields());

		foreach ($data as $key => $value) {

			if (in_array($key, $allowed_attributes, true)) {

				$this->attributes[$key] = $value;

			} // end if;

		} // end foreach;

		$this->attributes['date_created'] = wu_get_current_time('mysql', true);

	} // end attributes;

	/**
	 * Checks if this note has any content at all.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function exists() {

		return !empty(array_filter($this->attributes));

	} // end exists;

	/**
	 * Checks if a parameter exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The parameter to check.
	 * @return boolean
	 */
	public function __isset($name) {

		return wu_get_isset($this->attributes, $name, '');

	} // end __isset;

	/**
	 * Gets a note field.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The parameter to return.
	 * @return string
	 */
	public function __get($name) {

		$value = wu_get_isset($this->attributes, $name, '');

		return apply_filters("wu_note_get_{$name}", $value, $this);

	} // end __get;

	/**
	 * Sets a note field.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Field name.
	 * @param string $value The field value.
	 */
	public function __set($name, $value) {

		$value = apply_filters("wu_note_set_{$name}", $value, $this);

		$this->attributes[$name] = $value;

	} // end __set;

	/**
	 * Returns the validation rules for new notes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function validation_rules() {

		return array();

	} // end validation_rules;

	/**
	 * Validates the fields following the validation rules.
	 *
	 * @since 2.0.0
	 * @return true|\WP_Error
	 */
	public function validate() {

		$validator = new \WP_Ultimo\Helpers\Validator;

		$validator->validate($this->to_array(), $this->validation_rules());

		if ($validator->fails()) {

			return $validator->get_errors();

		} // end if;

		return true;

	} // end validate;

	/**
	 * Returns a key => value representation of the notes fields.
	 *
	 * @since 2.0.0
	 *
	 * @param boolean $labels Wether or not to return labels as keys or the actual keys.
	 * @return array
	 */
	public function to_array($labels = false) {

		$address_array = array();

		$fields = self::fields();

		foreach ($fields as $field_key => $field) {

			if (!empty($this->{$field_key})) {

				$key = $labels ? $field['title'] : $field_key;

				$address_array[$key] = $this->{$field_key};

			} // end if;

		} // end foreach;

		return $address_array;

	}  // end to_array;

	/**
	 * Returns the contents of the note.
	 *
	 * @since 2.0.0
	 *
	 * @param string $delimiter Delimiter to glue address pieces together.
	 * @return string
	 */
	public function to_string($delimiter = PHP_EOL) {

		return implode($delimiter, $this->to_array());

	} // end to_string;

	/**
	 * Note field definitions.
	 *
	 * This is used to determine fields allowed on the note.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function fields() {

		$fields = array();

		$fields['text'] = array(
			'type'  => 'text',
			'title' => __('Text', 'wp-ultimo'),
		);

		$fields['author_id'] = array(
			'type'  => 'number',
			'title' => __('Author ID', 'wp-ultimo'),
		);

		$fields['note_id'] = array(
			'type'  => 'text',
			'title' => __('Note ID', 'wp-ultimo'),
		);

		uasort($fields, 'wu_sort_by_order');

		return $fields;

	} // end fields;

} // end class Note;
