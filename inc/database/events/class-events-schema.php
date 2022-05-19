<?php
/**
 * Event schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Events
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Events;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Events Schema Class.
 *
 * @since 2.0.0
 */
class Events_Schema extends Schema {

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
			'name'     => 'severity',
			'type'     => 'tinyint',
			'length'   => '1',
			'unsigned' => true,
			'sortable' => true,
		),

		array(
			'name'    => 'initiator',
			'type'    => 'enum(\'system\', \'manual\')',
			'default' => 'none',
		),

		array(
			'name'       => 'author_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'object_type',
			'type'       => 'varchar',
			'length'     => 20,
			'default'    => 'network',
			'sortable'   => true,
			'searchable' => true,
		),

		array(
			'name'       => 'object_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'    => 'slug',
			'type'    => 'longtext',
			'default' => '',
		),

		array(
			'name'    => 'payload',
			'type'    => 'longtext',
			'default' => '',
		),

		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => null,
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

	);

} // end class Events_Schema;
