<?php
/**
 * Class used for querying payment.
 *
 * @package WP_Ultimo
 * @subpackage Database\Payments
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Payments;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_payments" database table
 *
 * @since 2.0.0
 */
final class Payments_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'payments';

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
	 * Payments constructor.
	 *
	 * @access public
	 * @since  2.0.0
	 * @return void
	 */
	public function __construct() {

		parent::__construct();

	} // end __construct;

	/**
	 * Setup the database schema.
	 *
	 * @access protected
	 * @since  2.0.0
	 * @return void
	 */
	protected function set_schema() {

		$this->schema = "id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			status varchar(20) NOT NULL DEFAULT 'pending',
			customer_id bigint(20) unsigned NOT NULL default '0',
			membership_id bigint(20) unsigned NOT NULL default '0',
			parent_id bigint(20) unsigned NOT NULL default '0',
			product_id bigint(9) NOT NULL default '0',
			migrated_from_id bigint(20) DEFAULT NULL,
			discount_code tinytext NOT NULL default '',
			currency varchar(10) NOT NULL DEFAULT 'USD',
			subtotal decimal(13,4) default 0,
			tax_total decimal(13,4) default 0,
			discount_total decimal(13,4) default 0,
			total decimal(13,4) default 0,
			gateway tinytext NOT NULL default '',
			gateway_payment_id tinytext DEFAULT NULL,
			date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY customer_id (customer_id),
			KEY membership_id (membership_id),
			KEY parent_id (parent_id),
			KEY product_id (product_id),
			KEY status (status)";

	} // end set_schema;

} // end class Payments_Table;
