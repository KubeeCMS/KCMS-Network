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

		if (!is_main_network()) {

			return false;

		} // end if;

		if (!is_main_site()) {

			return false;

		} // end if;

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
