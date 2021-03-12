<?php
/**
 * Base Custom Database Table Query Class.
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
class Query extends \WP_Ultimo\Dependencies\BerlinDB\Database\Query {

	/**
	 * The prefix for the custom table.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $prefix = 'wu';

	/**
	 * Get the plural name.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_plural_name() {

		return $this->item_name_plural;

	} // end get_plural_name;

} // end class Query;
