<?php
/**
 * Class used for querying events.
 *
 * @package WP_Ultimo
 * @subpackage Database\Event
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Checkout_Forms;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_events" database table
 *
 * @since 2.0.0
 */
final class Checkout_Forms_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'forms';

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
			name tinytext NOT NULL DEFAULT '',
			slug varchar(255),
			active tinyint(4) default 1,
			settings longtext DEFAULT NULL,
			custom_css longtext DEFAULT NULL,
			allowed_countries text DEFAULT NULL,
			date_created datetime NULL,
			date_modified datetime NULL,
			PRIMARY KEY (id)";

	} // end set_schema;

} // end class Checkout_Forms_Table;
