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
	protected $version = '2.0.1-revision.20210607';

	/**
	 * List of table upgrades.
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'2.0.1-revision.20210508' => 20210508,
		'2.0.1-revision.20210607' => 20210607,
	);

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
			date_modified datetime NULL,
			date_registered datetime NULL,
			last_login datetime NULL,
			has_trialed smallint unsigned DEFAULT NULL,
			vip smallint unsigned DEFAULT '0',
			ips longtext,
			signup_form varchar(40) DEFAULT 'by-admin',
			PRIMARY KEY (id),
			KEY user_id (user_id)";

	} // end set_schema;

	/**
	 * Adds the signup_form column.
	 *
	 * This does not work on older versions of MySQl, so we needed
	 * the other migration below.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function __20210508() { // phpcs:ignore

		$result = $this->column_exists('signup_form');

		// Maybe add column
		if (empty($result)) {

			$query = "ALTER TABLE {$this->table_name} ADD COLUMN `signup_form` varchar(40) default 'by-admin' AFTER `ips`;";

			$result = $this->get_db()->query($query);

		} // end if;

		// Return success/fail
		return $this->is_success($result);

	} // end __20210508;

	/**
	 * Adds the signup_form column.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function __20210607() { // phpcs:ignore

		$result = $this->column_exists('signup_form');

		// Maybe add column
		if (empty($result)) {

			$query_set = "SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';";

			$result_set = $this->get_db()->query($query_set);

			if ($this->is_success($result_set) === false) {

				return false;

			} // end if;

			$query = "ALTER TABLE {$this->table_name} ADD COLUMN `signup_form` varchar(40) default 'by-admin' AFTER `ips`;";

			$result = $this->get_db()->query($query);

		} // end if;

		// Return success/fail
		return $this->is_success($result);

	} // end __20210607;

} // end class Customers_Table;
