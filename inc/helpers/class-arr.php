<?php
/**
 * Array Helpers
 *
 * Heavily inspired on Laravel's Arr helper class and Lodash's PHP implementation.
 *
 * @see https://github.com/laravel/framework/blob/8.x/src/Illuminate/Collections/Arr.php
 * @see https://github.com/me-io/php-lodash/blob/master/src/Traits/Collections.php
 *
 * @package WP_Ultimo\Helpers
 * @since 2.0.11
 */

namespace WP_Ultimo\Helpers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Helper Array class.
 *
 * @since 2.0.11
 */
class Arr {

	/**
	 * Returns all results.
	 */
	const RESULTS_ALL = 0;

	/**
	 * Return only the first result.
	 */
	const RESULTS_FIRST = 1;

	/**
	 * Result only the last result.
	 */
	const RESULTS_LAST = 2;

	/**
	 * Filter an array by property or key.
	 *
	 * @since 2.0.11
	 *
	 * @param array   $array The array to filter.
	 * @param string  $property The property to filter by. Dot notation is supported.
	 * @param mixed   $expected_value The expected value to filter by.
	 * @param integer $flag The flag determining the return type.
	 * @return mixed
	 */
	public static function filter_by_property($array, $property, $expected_value, $flag = 0) {

		$result = Arr::filter($array, function($value) use ($property, $expected_value) {

			return Arr::get($value, $property, null) == $expected_value; // phpcs:ignore

		});

		if ($flag) {

			$result = $flag === Arr::RESULTS_FIRST ? reset($result) : end($result);

		} // end if;

		return $result;

	} // end filter_by_property;

	/**
	 * Filters an array using a callback.
	 *
	 * @since 2.0.11
	 *
	 * @param array    $array The array to search inside.
	 * @param callable $closure The closure function to call.
	 * @return array
	 */
	public static function filter($array, $closure) {

		if ($closure) {

			$result = array();

			foreach ($array as $key => $value) {

				if (call_user_func($closure, $value, $key)) {

					$result[] = $value;

				} // end if;

			} // end foreach;

			return $result;

		} // end if;

		return array_filter($array);

	} // end filter;

	/**
	 * Get a nested value inside an array. Dot notation is supported.
	 *
	 * @since 2.0.11
	 *
	 * @param array  $array The array to get the value from.
	 * @param string $key The array key to get. Supports dot notation.
	 * @param mixed  $default The value to return ibn the case the key does not exist.
	 * @return mixed
	 */
	public static function get($array, $key, $default = null) {

		if (is_null($key)) {

			return $array;

		} // end if;

		if (isset($array[$key])) {

			return $array[$key];

		} // end if;

		foreach (explode('.', $key) as $segment) {

			if (!is_array($array) || !array_key_exists($segment, $array)) {

				return $default;

			} // end if;

			$array = $array[$segment];

		} // end foreach;

		return $array;

	} // end get;

	/**
	 * Set a nested value inside an array. Dot notation is supported.
	 *
	 * @since 2.0.11
	 *
	 * @param array  $array The array to modify.
	 * @param string $key The array key to set. Supports dot notation.
	 * @param mixed  $value The value to set.
	 * @return array
	 */
	public static function set(&$array, $key, $value) {

		if (is_null($key)) {

			return $array = $value; // phpcs:ignore

		} // end if;

		$keys = explode('.', $key);

		while (count($keys) > 1) {

			$key = array_shift($keys);

			if (!isset($array[$key]) || !is_array($array[$key])) {

				$array[$key] = array();

			} // end if;

			$array =& $array[$key];

		} // end while;

		$array[array_shift($keys)] = $value;

		return $array;

	} // end set;

	/**
	 * Static class only.
	 *
	 * @since 2.0.11
	 */
	private function __construct() {} // end __construct;

} // end class Arr;
