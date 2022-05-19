<?php
/**
 * Class used for querying events.
 *
 * @package WP_Ultimo
 * @subpackage Database\Event
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Events;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_events" database table
 *
 * @since 2.0.0
 */
final class Events_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'events';

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
	 * Event constructor.
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

		$this->schema = "id bigint(20) NOT NULL auto_increment,
			severity tinyint(4),
			initiator enum('system', 'manual'),
			author_id bigint(20) NOT NULL default '0',
			object_id bigint(20) NOT NULL default '0',
			object_type varchar(20) DEFAULT 'network',
			slug varchar(255),
			payload longtext,
			date_created datetime NULL,
			PRIMARY KEY (id),
			KEY severity (severity),
			KEY author_id (author_id),
			KEY initiator (initiator)";

	} // end set_schema;

} // end class Events_Table;
