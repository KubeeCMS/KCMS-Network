<?php
/**
 * Base Custom Database Table Class.
 */

namespace WP_Ultimo\Database\Engine;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * The base class that all other database base classes extend.
 *
 * This class attempts to provide some universal immutability to all other
 * classes that extend it, starting with a magic getter, but likely expanding
 * into a magic call handler and others.
 *
 * @since 1.0.0
 */
abstract class Table extends \WP_Ultimo\Dependencies\BerlinDB\Database\Table {

	/**
	 * Table prefix.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $prefix = 'wu';

	/**
	 * Caches the SHOW TABLES result.
	 *
	 * @since 2.0.0
	 * @var bool
	 */
	protected $_exists;

	/**
	 * Overrides the is_upgradeable method.
	 *
	 * We need to do this because we are using the table object
	 * early in the lifecycle, which means that upgrade.php is not
	 * available.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function is_upgradeable() {

		$setup_finished = get_network_option(null, 'wu_setup_finished', false);

		if (!$setup_finished) {

			return true;

		} // end if;

		$check_time = 5 * HOUR_IN_SECONDS; // Every 5 Hours

		$last_check = get_network_option(null, 'wu_last_database_update');

		if ($last_check && $last_check <= time() + $check_time) {

			return false;

		} // end if;

		if ($this->global && !is_main_site()) {

			return false;

		} // end if;

		update_network_option(null, 'wu_last_database_update', time());

		return true;

	} // end is_upgradeable;

	/**
	 * Adds a caching layer to the parent exists method.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public function exists() {

		if ($this->_exists === null) {

			$this->_exists = parent::exists();

		} // end if;

		return $this->_exists;

	} // end exists;

} // end class Table;
