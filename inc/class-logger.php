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

use \WP_Ultimo\Dependencies\Analog\Analog;
use \WP_Ultimo\Dependencies\Analog\Handler\Multi;
use \WP_Ultimo\Dependencies\Analog\Handler\File;
use \WP_Ultimo\Dependencies\Analog\Handler\PDO;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * WP Ultimo Logger
 *
 * @since 2.0.0
 */
class Logger {

	/**
	 * Logger implementation.
	 *
	 * @since 2.0.11
	 * @var \Psr\Log\LoggerInterface
	 */
	public static $psr3_logger;

	/**
	 * Builds a logger instance.
	 *
	 * @since 2.0.11
	 * @return \Psr\Log\LoggerInterface
	 */
	public static function build() {

		if (self::$psr3_logger === null) {

			self::$psr3_logger = new self();

		} // end if;

		return self::$psr3_logger;

	} // end build;

	/**
	 * Returns the logs folder
	 *
	 * @return string
	 */
	public static function get_logs_folder() {

		return wu_maybe_create_folder('wu-logs');

	} // end get_logs_folder;

	/**
	 * Add a log entry to chosen file.
	 *
	 * @param string           $handle Name of the log file to write to.
	 * @param string|\WP_Error $message Log message to write.
	 */
	public static function add($handle, $message) {

		return '';

		if (self::open($handle) && is_resource(self::$_handles[$handle])) {

			if (is_wp_error($message)) {

				$error   = $message;
				$message = $error->get_error_message();
				$code    = $error->get_error_code();
				$data    = $error->get_error_data();

				if (!empty($code)) {

					$message .= sprintf(' (%s)', $code);

				} // end if;

				if (!empty($data)) {

					$message .= sprintf(" - data:\n%s", json_encode($data, JSON_PRETTY_PRINT));

				} // end if;

			} // end if;

			$time = date_i18n('m-d-Y @ H:i:s -');

			$result = @fwrite(self::$_handles[$handle], $time . ' ' . $message . "\n"); // phpcs:ignore

			@fclose(self::$_handles[$handle]); // phpcs:ignore

		} // end if;

		do_action('wu_log_add', $handle, $message);

	} // end add;

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

		return array();

	} // end read_lines;

	/**
	 * Clear entries from chosen file.
	 *
	 * @param mixed $handle Name of the log file to clear.
	 */
	public static function clear($handle) {

		return;

		// if (self::open($handle) && is_resource(self::$_handles[$handle])) {

		// @ftruncate(self::$_handles[$handle], 0); // phpcs:ignore

		// } // end if;

		// do_action('wu_log_clear', $handle);
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
