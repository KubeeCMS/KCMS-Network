<?php
/**
 * Class used for querying products.
 *
 * @package WP_Ultimo
 * @subpackage Database\Products
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Products;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_product" database table
 *
 * @since 2.0.0
 */
final class Products_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'products';

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
	 * Products constructor.
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

		$this->schema = "id bigint(20) NOT NULL AUTO_INCREMENT,
			name tinytext NOT NULL DEFAULT '',
			slug tinytext NOT NULL DEFAULT '',
			parent_id bigint(20),
			migrated_from_id bigint(20) DEFAULT NULL,
			description longtext NOT NULL default '',
			currency varchar(10) NOT NULL DEFAULT 'USD',
			pricing_type varchar(10) NOT NULL DEFAULT 'paid',
			amount decimal(13,4) default 0,
			setup_fee decimal(13,4) default 0,
			recurring tinyint(4) default 1,
			trial_duration smallint default 0,
			trial_duration_unit enum('day', 'week', 'month', 'year'),
			duration smallint default 0,
			duration_unit enum('day', 'week', 'month', 'year'),
			billing_cycles smallint default 0,
			list_order tinyint default 10,
			active tinyint(4) default 1,
			type tinytext NOT NULL DEFAULT '',
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			PRIMARY KEY (id)";

	} // end set_schema;

} // end class Products_Table;
