<?php
/**
 * Class used for querying customers' meta data.
 *
 * @package WP_Ultimo
 * @subpackage Database\Customers
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Customers;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_customermeta" database table
 *
 * @since 2.0.0
 */
final class Customers_Meta_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'customermeta';

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
	 * Customers constructor.
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

		$max_index_length = 191;

		$this->schema = "meta_id bigint(20) unsigned NOT NULL auto_increment,
		wu_customer_id bigint(20) unsigned NOT NULL default '0',
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext DEFAULT NULL,
		PRIMARY KEY (meta_id),
		KEY wu_customer_id (wu_customer_id),
		KEY meta_key (meta_key({$max_index_length}))";

	} // end set_schema;

} // end class Customers_Meta_Table;
