<?php
/**
 * Customer schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Customers
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Customers;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customers Schema Class.
 *
 * @since 2.0.0
 */
class Customers_Schema extends Schema {

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

		// user_id
		array(
			'name'       => 'user_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'searchable' => true,
		),

		array(
			'name'       => 'type',
			'type'       => 'varchar',
			'default'    => 'customer',
			'searchable' => true,
			'sortable'   => true,
		),

		// date_registered
		array(
			'name'       => 'date_registered',
			'type'       => 'datetime',
			'default'    => null,
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// email_verification
		array(
			'name'       => 'email_verification',
			'type'       => 'enum(\'verified\', \'pending\', \'none\')',
			'default'    => 'none',
			'transition' => true,
		),

		// last_login
		array(
			'name'       => 'last_login',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
		),

		// has_trialed
		array(
			'name'       => 'has_trialed',
			'type'       => 'smallint',
			'length'     => '',
			'unsigned'   => true,
			'default'    => null,
			'transition' => true,
		),

		// vip
		array(
			'name'       => 'vip',
			'type'       => 'smallint',
			'length'     => '',
			'unsigned'   => true,
			'default'    => 0,
			'transition' => true,
			'sortable'   => true,
		),

		// ips
		array(
			'name'       => 'ips',
			'type'       => 'longtext',
			'default'    => '',
			'searchable' => true,
		),

		// Added on 2.0 beta 7
		array(
			'name'       => 'signup_form',
			'type'       => 'varchar',
			'default'    => 'by-admin',
			'searchable' => true,
			'sortable'   => true,
		),

	);

} // end class Customers_Schema;
