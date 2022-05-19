<?php
/**
 * Product schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Products
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Products;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Products Schema Class.
 *
 * @since 2.0.0
 */
class Products_Schema extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var array
	 */
	public $columns = array(

		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true
		),

		array(
			'name'       => 'slug',
			'type'       => 'varchar',
			'searchable' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'parent_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'     => 'migrated_from_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'sortable' => true,
		),

		array(
			'name'       => 'name',
			'type'       => 'varchar',
			'searchable' => true,
			'sortable'   => true
		),

		array(
			'name'       => 'description',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true
		),

		array(
			'name'       => 'product_group',
			'type'       => 'varchar',
			'searchable' => true,
			'sortable'   => true,
		),

		array(
			'name'     => 'currency',
			'type'     => 'varchar',
			'length'   => '10',
			'default'  => 'USD',
			'sortable' => true
		),

		array(
			'name'     => 'pricing_type',
			'type'     => 'varchar',
			'length'   => '10',
			'default'  => 'paid',
			'sortable' => true
		),

		array(
			'name'       => 'amount',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'setup_fee',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'recurring',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 1,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'trial_duration',
			'type'       => 'smallint',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true,
			'transition' => true
		),

		array(
			'name'    => 'trial_duration_unit',
			'type'    => 'enum(\'day\', \'month\', \'week\', \'year\')',
			'default' => 'none',
		),

		array(
			'name'       => 'duration',
			'type'       => 'smallint',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true,
			'transition' => true
		),

		array(
			'name'    => 'duration_unit',
			'type'    => 'enum(\'day\', \'month\', \'week\', \'year\')',
			'default' => 'none',
		),

		array(
			'name'       => 'billing_cycles',
			'type'       => 'smallint',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true,
			'transition' => true
		),

		array(
			'name'       => 'list_order',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 10,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'active',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 1,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => null,
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => null,
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'searchable' => true,
			'sortable'   => true
		),

	);

} // end class Products_Schema;
