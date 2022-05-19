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
	protected $version = '2.0.1-revision.20210607';

	/**
	 * List of table upgrades.
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'2.0.1-revision.20210419' => 20210419,
		'2.0.1-revision.20210607' => 20210607,
	);

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
			product_group varchar(20) DEFAULT '',
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
			date_created datetime NULL,
			date_modified datetime NULL,
			PRIMARY KEY (id)";

	} // end set_schema;

	/**
	 * Adds the product_group column.
	 *
	 * This does not work on older versions of MySQL, so we needed
	 * the other migration below.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function __20210419() { // phpcs:ignore

		$result = $this->column_exists('product_group');

		// Maybe add column
		if (empty($result)) {

			$query = "ALTER TABLE {$this->table_name} ADD COLUMN `product_group` varchar(20) default '' AFTER `description`;";

			$result = $this->get_db()->query($query);

		} // end if;

		// Return success/fail
		return $this->is_success($result);

	} // end __20210419;

	/**
	 * Adds the product_group column.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function __20210607() { // phpcs:ignore

		$result = $this->column_exists('product_group');

		// Maybe add column
		if (empty($result)) {

			$query_set = "SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';";

			$result_set = $this->get_db()->query($query_set);

			if ($this->is_success($result_set) === false) {

				return false;

			} // end if;

			$query = "ALTER TABLE {$this->table_name} ADD COLUMN `product_group` varchar(20) default '' AFTER `description`;";

			$result = $this->get_db()->query($query);

		} // end if;

		// Return success/fail
		return $this->is_success($result);

	} // end __20210607;

} // end class Products_Table;
