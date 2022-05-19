<?php
/**
 * Class used for querying memberships.
 *
 * @package WP_Ultimo
 * @subpackage Database\Memberships
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Memberships;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_membership" database table
 *
 * @since 2.0.0
 */
final class Memberships_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'memberships';

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
	 * Memberships constructor.
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
			customer_id bigint(20) unsigned NOT NULL default '0',
			user_id bigint(20) unsigned DEFAULT NULL,
			migrated_from_id bigint(20) DEFAULT NULL,
			plan_id bigint(20) NOT NULL default '0',
			addon_products longtext,
			currency varchar(10) NOT NULL DEFAULT 'USD',
			initial_amount decimal(13,4) default 0,
			recurring smallint unsigned NOT NULL DEFAULT '0',
			auto_renew smallint unsigned NOT NULL DEFAULT '0',
			duration smallint default 0,
			duration_unit enum('day', 'week', 'month', 'year'),
			amount decimal(13,4) default 0,
			date_created datetime NULL,
			date_activated datetime NULL,
			date_trial_end datetime NULL,
			date_renewed datetime NULL,
			date_cancellation datetime NULL,
			date_expiration datetime NULL,
			date_payment_plan_completed datetime NULL,
			times_billed smallint unsigned NOT NULL DEFAULT '0',
			billing_cycles smallint unsigned NOT NULL DEFAULT '0',
			status varchar(12) NOT NULL DEFAULT 'pending',
			gateway_customer_id tinytext DEFAULT NULL,
			gateway_subscription_id tinytext DEFAULT NULL,
			gateway tinytext default '',
			signup_method tinytext default '',
			subscription_key varchar(32) default '',
			upgraded_from bigint(20) unsigned DEFAULT NULL,
			date_modified datetime NULL,
			disabled smallint unsigned DEFAULT NULL,
			PRIMARY KEY (id),
			KEY customer_id (customer_id),
			KEY plan_id (plan_id),
			KEY status (status),
			KEY disabled (disabled)";

	} // end set_schema;

} // end class Memberships_Table;
