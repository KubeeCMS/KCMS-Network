<?php
/**
 * WP Ultimo Logger
 *
 * Log string messages to a file with a timestamp. Useful for debugging.
 *
 * @package WP_Ultimo
 * @subpackage Logger
 * @since 2.0.0
 */

namespace WP_Ultimo;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Logger
 *
 * @since 2.0.0
 */
class Logger {

	/**
	 * Stores open file _handles.
	 *
	 * @var array
	 * @access private
	 */
	static $_handles;

	/**
	 * Constructor for the logger.
	 */
	public function __construct() {

		self::$_handles = array();

	} // end __construct;

	/**
	 * Destructor.
	 */
	public function __destruct() {

		foreach (self::$_handles as $handle) {

			@fclose($handle); // phpcs:ignore

		} // end foreach;

	} // end __destruct;

	/**
	 * Returns the logs folder
	 *
	 * @return string
	 */
	public static function get_logs_folder() {

		return WP_Ultimo()->helper->maybe_create_folder('wu-logs');

	} // end get_logs_folder;

	/**
	 * Get the log contents
	 *
	 * @since  1.6.0
	 *
	 * @param  string  $handle File name to read.
	 * @param  integer $lines Number of lines to retrieve, defaults to 10.
	 * @return array
	 */
	public static function read_lines($handle, $lines = 10) {

		$results = array();

		// Open the file for reading
		if (self::open($handle, 'r') && is_resource(self::$_handles[$handle])) {

			while (!feof(self::$_handles[$handle])) {

				$line = fgets(self::$_handles[$handle], 4096);

				array_push($results, $line);

				if (count($results) > $lines + 1) {

					array_shift($results);

				} // end if;

			} // end while;

			if (@fclose(self::$_handles[$handle]) === false) { // phpcs:ignore

				return false;

			} // end if;

		} // end if;

		// Close the file handle; when you are done using a
		// resource you should always close it immediately
		return array_filter($results);

	} // end read_lines;

	/**
	 * Open log file for writing.
	 *
	 * @since  1.2.0 Checks if the directory exists.
	 * @since  0.0.1
	 *
	 * @access private
	 * @param string $handle Name of the log file to open.
	 * @param string $permission Permission to open the file with.
	 * @return bool success
	 */
	private static function open($handle, $permission = 'a') {

		// Get the path for our logs
		$path = self::get_logs_folder();

		if (self::$_handles[$handle] = @fopen($path . $handle . '.log', $permission)) { // phpcs:ignore

			return true;

		} // end if;

		return false;

	} // end open;

	/**
	 * Add a log entry to chosen file.
	 *
	 * @param string $handle Name of the log file to write to.
	 * @param string $message Log message to write.
	 */
	public static function add($handle, $message) {

		if (self::open($handle) && is_resource(self::$_handles[$handle])) {

			$time = date_i18n('m-d-Y @ H:i:s -');

			$result = @fwrite(self::$_handles[$handle], $time . ' ' . $message . "\n"); // phpcs:ignore

			@fclose(self::$_handles[$handle]); // phpcs:ignore

		} // end if;

		do_action('wu_log_add', $handle, $message);

	} // end add;

	/**
	 * Clear entries from chosen file.
	 *
	 * @param mixed $handle Name of the log file to clear.
	 */
	public static function clear($handle) {

		if (self::open($handle) && is_resource(self::$_handles[$handle])) {

			@ftruncate(self::$_handles[$handle], 0); // phpcs:ignore

		} // end if;

		do_action('wu_log_clear', $handle);

	} // end clear;

	/**
	 * Takes a callable as a parameter and logs how much time it took to execute it.
	 *
	 * @since 2.0.0
	 *
	 * @param string   $handle Name of the log file to write to.
	 * @param string   $message  Log message to write.
	 * @param callable $callback Function to track the execution time.
	 * @return array
	 */
	public static function track_time($handle, $message, $callback) {

		$start = microtime(true);

		$return = call_user_func($callback);

		$time_elapsed = microtime(true) - $start;

		// translators: the placeholder %s will be replaced by the time in seconds (float).
		$message .= ' - ' . sprintf(__('This action took %s seconds.', 'wp-ultimo'), $time_elapsed);

		self::add($handle, $message);

		return $return;

	} // end track_time;

} // end class Logger;
