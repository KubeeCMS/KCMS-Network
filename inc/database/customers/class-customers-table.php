<?php
/**
 * Class used for querying domain mappings.
 *
 * @package WP_Ultimo
 * @subpackage Database\Customer
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Customers;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_customers" database table
 *
 * @since 2.0.0
 */
final class Customers_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'customers';

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
	 * Customer constructor.
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

		$this->schema = "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT '0',
			type varchar(20) NOT NULL DEFAULT 'customer',
			email_verification enum('verified', 'pending', 'none') DEFAULT 'none',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			date_registered datetime NOT NULL default '0000-00-00 00:00:00',
			last_login datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			has_trialed smallint unsigned DEFAULT NULL,
			vip smallint unsigned DEFAULT '0',
			ips longtext,
			PRIMARY KEY (id),
			KEY user_id (user_id)";

	} // end set_schema;

} // end class Customers_Table;
