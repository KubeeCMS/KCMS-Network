<?php
/**
 * Class used for querying products' meta data.
 *
 * @package WP_Ultimo
 * @subpackage Database\Sites
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Sites;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_productmeta" database table
 *
 * @since 2.0.0
 */
final class Sites_Meta_Table extends Table {

	/**
	 * Table prefix, including the site prefix.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $prefix = '';

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'blogmeta';

	/**
	 * Is this table global?
	 *
	 * @since 2.0.0
	 * @var boolean
	 */
	protected $global = true;

	/**
	 * Table current version
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $version = '2.0.0';

	/**
	 * Sites constructor.
	 *
	 * @access public
	 * @since  2.0.0
	 * @return void
	 */
	public function __construct() {

		parent::__construct();

	} // end __construct;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since  2.0.0
	 * @return void
	 */
	protected function set_schema() {

		$this->schema = false;

	} // end set_schema;

} // end class Sites_Meta_Table;
