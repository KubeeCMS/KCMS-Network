<?php
/**
 * Payment schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Payments
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Payments;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Payments Schema Class.
 *
 * @since 2.0.0
 */
class Payments_Schema extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var array
	 */
	public $columns = array(

		// id
		array(
			'name'       => 'id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'extra'      => 'auto_increment',
			'primary'    => true,
			'sortable'   => true,
			'searchable' => true,
		),

		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '12',
			'default'    => 'pending',
			'sortable'   => true,
			'transition' => true,
		),

		// customer_id
		array(
			'name'       => 'customer_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'membership_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'parent_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'product_id',
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

		// currency
		array(
			'name'     => 'currency',
			'type'     => 'varchar',
			'length'   => '20',
			'default'  => 'USD',
			'sortable' => true
		),

		array(
			'name'       => 'discount_code',
			'type'       => 'tinytext',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
		),

		array(
			'name'     => 'discount_total',
			'type'     => 'decimal(13,4)',
			'default'  => '',
			'sortable' => true,
		),

		array(
			'name'       => 'subtotal',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'refund_total',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'tax_total',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'total',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		// gateway
		array(
			'name'       => 'gateway',
			'type'       => 'tinytext',
			'default'    => '',
			'searchable' => true,
		),

		// gateway
		array(
			'name'       => 'gateway_payment_id',
			'type'       => 'tinytext',
			'default'    => '',
			'searchable' => true,
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// date_modified
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		),

	);

} // end class Payments_Schema;
