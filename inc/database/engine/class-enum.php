<?php
/**
 * WP Ultimo ENUM base class.
 *
 * @package WP_Ultimo
 * @subpackage WP_Ultimo\Database\Engine
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Engine;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo ENUM base class.
 *
 * @since 2.0.0
 */
abstract class Enum {

	/**
	 * The default value.
	 */
	const __default = false; // phpcs:ignore

	/**
	 * The options available.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	static $options = array();

	/**
	 * The value.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	private $value;

	/**
	 * Constructor method. Takes the value you want to set.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value The value to be set.
	 */
	public function __construct($value = '') {

		$this->value = $value;

	} // end __construct;

	// Needs to be Implemented

	/**
	 * Returns an array with values => CSS Classes.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	abstract protected function classes();

	/**
	 * Returns an array with values => labels.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	abstract protected function labels();

	/**
	 * Returns an array with values => labels.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	protected function icon_classes() {

		return array();

	} // end icon_classes;

	/**
	 * Returns the options available as constants.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public static function get_options() {

		$hook = static::get_hook_name();

		if (!isset(static::$options[$hook])) {

			$reflector = new \ReflectionClass(static::class);

			static::$options[$hook] = apply_filters("wu_available_{$hook}_options", $reflector->getConstants());

		} // end if;

		return static::$options[$hook];

	} // end get_options;

	public static function get_allowed_list($string = false) {

		$options = array_unique(self::get_options());

		return $string ? implode(',', $options) : $options;

	} // end get_allowed_list;

	/**
	 * Returns the value loaded here.
	 *
	 * This runs through is_valid and returns the
	 * default value if a invalid value is passed on.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_value() {

		if ($this->is_valid($this->value)) {

			return $this->value;

		} // end if;

		return static::__default;

	} // end get_value;

	/**
	 * Check for the the validity of the value passed.
	 *
	 * @since 2.0.0
	 *
	 * @param string $value The string.
	 * @return boolean
	 */
	public function is_valid($value) {

		$options = static::get_options();

		return in_array($value, $options, true);

	} // end is_valid;

	/**
	 * Returns the label of a given value.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_label() {

		$hook = static::get_hook_name();

		$labels = apply_filters("wu_available_{$hook}_labels", $this->labels());

		return $this->exists_or_default($labels, $this->get_value());

	} // end get_label;

	/**
	 * Returns the classes of a given value.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_classes() {

		$hook = static::get_hook_name();

		$classes = apply_filters("wu_available_{$hook}_classes", $this->classes());

		return $this->exists_or_default($classes, $this->get_value());

	} // end get_classes;

	/**
	 * Returns the classes of a given value.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_icon_classes() {

		$hook = static::get_hook_name();

		$classes = apply_filters("wu_available_{$hook}_icon_classes", $this->icon_classes());

		return $this->exists_or_default($classes, $this->get_value());

	} // end get_icon_classes;

	/**
	 * Returns an array of options.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function to_array() {

		static $instance;

		if ($instance === null) {

			$instance = new static;

		} // end if;

		$hook = $instance::get_hook_name();

		$labels = apply_filters("wu_{$hook}_to_array", $instance->labels());

		return $labels;

	} // end to_array;

	/**
	 * Get the hook name for this class, so we can add filters.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public static function get_hook_name() {

		$class_name = (new \ReflectionClass(static::class))->getShortName();

		return strtolower($class_name);

	} // end get_hook_name;

	/**
	 * Checks if a key exists on an array, otherwise returns a default value.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $array The array to check.
	 * @param string $key The key to check.
	 * @param string $default The default value.
	 * @return string
	 */
	public function exists_or_default($array, $key, $default = '') {

		if (empty($default)) {

			$default = isset($array[static::__default]) ? $array[static::__default] : '';

		} // end if;

		return isset($array[$key]) ? $array[$key] : $default;

	} // end exists_or_default;

	/**
	 * Converts this to string.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function __toString() {

		return $this->get_value();

	} // end __toString;

	/**
	 * Magic method to allow for constants to be called.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name The name of the constants.
	 * @param array  $arguments The list of arguments. Not really needed here.
	 * @return string
	 */
	public static function __callStatic($name, $arguments) {

		$class_name = static::class;

		return constant("$class_name::$name");

	} // end __callStatic;

} // end class Enum;
