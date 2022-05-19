<?php
/**
 * WP Ultimo Rollback
 *
 * Allows users to rollback WP Ultimo to the previous stable version.
 *
 * @package WP_Ultimo
 * @subpackage Rollback
 * @since 2.0.0
 */

namespace WP_Ultimo\Rollback;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Our own upgrader skin implementation that does not display errors.
 *
 * @since 2.0.0
 */
class Quiet_Plugin_Upgrader_Skin extends \Plugin_Upgrader_Skin {

	/**
	 * Silence is golden...
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function header() {} // end header;

	/**
	 * Silence is golden...
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function footer() {} // end footer;

	/**
	 * Print errors.
	 *
	 * @since 2.0.0
	 *
	 * @param string|WP_Error $errors Errors.
	 * @return void
	 */
	public function error($errors) {

		if (is_string($errors)) {

			$this->feedback($errors);

		} elseif (is_wp_error($errors) && $errors->has_errors()) {

			foreach ($errors->get_error_messages() as $message) {

				if ($errors->get_error_data() && is_string($errors->get_error_data())) {

					$this->feedback($message . ' ' . esc_html(strip_tags($errors->get_error_data())));

				} else {

					$this->feedback($message);

				} // end if;

			} // end foreach;

		} // end if;

	} // end error;

	/**
	 * Passes messages down to the upgrader class.
	 *
	 * @since 2.0.0
	 *
	 * @param string $string The error message.
	 * @param mixed  ...$args other arguments.
	 * @return void
	 */
	public function feedback($string, ...$args) {

		if (isset($this->upgrader->strings[$string])) {

			$string = $this->upgrader->strings[$string];

		} // end if;

		if (strpos($string, '%') !== false) {

			if ($args ) {

				$args   = array_map('strip_tags', $args);
				$args   = array_map('esc_html', $args);
				$string = vsprintf($string, $args);

			} // end if;

		} // end if;

		if (empty($string)) {

			return;

		} // end if;

		$this->upgrader->messages[] = $string;

	} // end feedback;

	/**
	 * Removes output.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type it doesn't matter.
	 * @return void
	 */
	protected function decrement_update_count($type) {} // end decrement_update_count;

} // end class Quiet_Plugin_Upgrader_Skin;
