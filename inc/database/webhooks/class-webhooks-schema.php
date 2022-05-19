<?php
/**
 * Webhook schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Webhooks
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Webhooks;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Webhooks Schema Class.
 *
 * @since 2.0.0
 */
class Webhooks_Schema extends Schema {

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
			'sortable'   => true,
		),

		array(
			'name'       => 'webhook_url',
			'type'       => 'varchar',
			'searchable' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'event',
			'type'       => 'varchar',
			'searchable' => true,
			'sortable'   => true,
		),

		array(
			'name'     => 'event_count',
			'type'     => 'int',
			'length'   => '10',
			'default'  => 0,
			'sortable' => true,
			'aliases'  => array('sent_events_count'),
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
			'name'       => 'hidden',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 0,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'integration',
			'type'       => 'varchar',
			'searchable' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'date_last_failed',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true
		),

		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => null,
			'created'    => true,
			'date_query' => true,
			'sortable'   => true
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

} // end class Webhooks_Schema;
