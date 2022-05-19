<?php
/**
 * Discount Code schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Discount_Codes
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Discount_Codes;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Discount Codes Schema Class.
 *
 * @since 2.0.0
 */
class Discount_Codes_Schema extends Schema {

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
			'sortable' => true,
		),

		array(
			'name'       => 'name',
			'type'       => 'varchar',
			'searchable' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'code',
			'type'       => 'varchar',
			'length'     => '20',
			'sortable'   => true,
			'searchable' => true,
			'transition' => true,
		),

		array(
			'name'       => 'description',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true,
		),

		array(
			'name'       => 'uses',
			'type'       => 'int',
			'unsigned'   => true,
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'max_uses',
			'type'       => 'int',
			'unsigned'   => true,
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'apply_to_renewals',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 0,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'type',
			'type'       => 'enum(\'percentage\', \'absolute\')',
			'default'    => 'percentage',
			'transition' => true,
		),

		array(
			'name'       => 'value',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'setup_fee_type',
			'type'       => 'enum(\'percentage\', \'absolute\')',
			'default'    => 'percentage',
			'transition' => true,
		),

		array(
			'name'       => 'setup_fee_value',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
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
			'name'       => 'date_start',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'date_expiration',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'transition' => true,
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

	);

} // end class Discount_Codes_Schema;
