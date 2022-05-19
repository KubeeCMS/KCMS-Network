<?php
/**
 * Domain schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Domains
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Domains;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Domains Schema Class.
 *
 * @since 2.0.0
 */
class Domains_Schema extends Schema {

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
			'name'       => 'blog_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'aliases'    => array('site_id', 'site'),
			'searchable' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'domain',
			'type'       => 'varchar',
			'searchable' => true,
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
			'name'       => 'primary_domain',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 0,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'secure',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 0,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'stage',
			'type'       => 'enum(\'checking-dns\', \'checking-ssl-cert\', \'done-without-ssl\', \'done\', \'failed\')',
			'default'    => 'checking-dns',
			'transition' => true,
			'sortable'   => true,
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

} // end class Domains_Schema;
