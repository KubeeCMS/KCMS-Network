<?php
/**
 * Handles modifications to the wp-config.php file, if permissions allow.
 *
 * @package WP_Ultimo
 * @subpackage Helper
 * @since 2.0.0
 */

namespace WP_Ultimo\Helpers;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Handles modifications to the wp-config.php file, if permissions allow.
 *
 * @since 2.0.0
 */
class WP_Config {

	use \WP_Ultimo\Traits\Singleton;

	/**
	 * Inject the constant into the wp-config.php file.
	 *
	 * @since 2.0.0
	 *
	 * @param string     $constant The name of the constant. e.g. WP_ULTIMO_CONSTANT.
	 * @param string|int $value The value of that constant.
	 * @return boolean|WP_Error
	 */
	public function inject_wp_config_constant($constant, $value) {

		$config_path = $this->get_wp_config_path();

		if (!is_writeable($config_path)) {

			// translators: %s is the file name.
			return new \WP_Error('not-writeable', sprintf(__('The file %s is not writable', 'wp-ultimo'), $config_path));

		} // end if;

		$config = file($config_path);

		$line = $this->find_injected_line($config, $constant);

		$content = str_pad(sprintf("define( '%s', '%s' );", $constant, $value), 50) . '// Automatically injected by WP Ultimo;';

		if ($line === false) {

			// no defined, we just need to inject
			$hook_line = $this->find_reference_hook_line($config);

			if ($hook_line === false) {

				return new \WP_Error('unknown-wpconfig', __("WP Ultimo can't recognize your wp-config.php, please revert it to original state for further process.", 'wp-ultimo'));

			} // end if;

			$config = $this->inject_contents($config, $hook_line + 1, PHP_EOL . $content);

			return file_put_contents($config_path, implode(null, $config), LOCK_EX);

		} else {

			list($value, $line) = $line;

			if ($value === true) {

				return;

			} else {

				$config[$line] = $content . PHP_EOL;

				return file_put_contents($config_path, implode(null, $config), LOCK_EX);

			} // end if;

		} // end if;

		return false;

	} // end inject_wp_config_constant;

	/**
	 * Actually inserts the new lines into the array of wp-config.php lines.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $content_array Array containing the original lines of the file being edited.
	 * @param int    $line Line number to inject the new content at.
	 * @param string $value Value to add to that specific line.
	 * @return array New array containing the lines of the modified file.
	 */
	public function inject_contents($content_array, $line, $value) {

		if (!is_array($value)) {

			$value = array($value);

		} // end if;

		array_splice($content_array, $line, 0, $value);

		return $content_array;

	} // end inject_contents;

	/**
	 * Gets the correct path to the wp-config.php file.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_wp_config_path() {

		if (file_exists(ABSPATH . 'wp-config.php')) {

			return (ABSPATH . 'wp-config.php');

		} elseif (@file_exists(dirname(ABSPATH) . '/wp-config.php') && !@file_exists(dirname(ABSPATH) . '/wp-settings.php')) {

			return (dirname(ABSPATH) . '/wp-config.php');

		} elseif (defined('WP_TESTS_MULTISITE') && constant('WP_TESTS_MULTISITE') === true) {

			return '/tmp/wordpress-tests-lib/wp-tests-config.php';

		} // end if;

	} // end get_wp_config_path;

	/**
	 * Find reference line for injection.
	 *
	 * We need a hook point we can use as reference to inject our constants.
	 * For now, we are using the line defining the $table_prefix.
	 * e.g. $table_prefix = 'wp_';
	 * We retrieve that line via RegEx.
	 *
	 * @since 2.0.0
	 *
	 * @param array $config Array containing the lines of the config file, for searching.
	 * @return false|int Line number.
	 */
	public function find_reference_hook_line($config) {

		global $wpdb;

		$pattern = '/^\$table_prefix\s*=\s*[\'|\"]' . $wpdb->prefix . '[\'|\"]/';

		foreach ($config as $k => $line) {

			if (preg_match($pattern, $line)) {

				return $k;

			} // end if;

		} // end foreach;

		return false;

	} // end find_reference_hook_line;

	/**
	 * Revert the injection of a constant in wp-config.php
	 *
	 * @since 2.0.0
	 *
	 * @param string $constant Constant name.
	 * @return mixed
	 */
	public function revert($constant) {

		$config_path = $this->get_wp_config_path();

		if (!is_writeable($config_path)) {

			// translators: %s is the file name.
			return new \WP_Error('not-writeable', sprintf(__('The file %s is not writable', 'wp-ultimo'), $config_path));

		} // end if;

		$config = file($config_path);

		$line = $this->find_injected_line($config, $constant);

		if ($line === false) {

			return;

		} else {

			$value = $line[0];

			$line = $line[1];

			if ($value === 'true' || $value === '1') {

				// value is true, we will remove this
				unset($config[$line]);

				// save it
				return file_put_contents($config_path, implode(null, $config), LOCK_EX);

			} // end if;

		} // end if;

	} // end revert;

	/**
	 * Checks for the injected line inside of the wp-config.php file.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $config Array containing the lines of the config file, for searching.
	 * @param string $constant The constant name.
	 * @return array|boolean
	 */
	public function find_injected_line($config, $constant) {

		$pattern = "/^define\(\s*['|\"]" . $constant . "['|\"],(.*)\)/";

		foreach ($config as $k => $line) {

			if (preg_match($pattern, $line, $matches)) {

				return array(trim($matches[1]), $k);

			} // end if;

		} // end foreach;

		return false;

	} // end find_injected_line;

} // end class WP_Config;
