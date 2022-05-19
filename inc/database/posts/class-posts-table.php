<?php
/**
 * Class used for querying posts.
 *
 * @package WP_Ultimo
 * @subpackage Database\Posts
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Posts;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_post" database table
 *
 * @since 2.0.0
 */
final class Posts_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'posts';

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
	 * Posts constructor.
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
      author_id bigint(20) NOT NULL,
      type tinytext NOT NULL DEFAULT '',
			slug tinytext NOT NULL DEFAULT '',
			title tinytext NOT NULL DEFAULT '',
			content longtext NOT NULL default '',
			excerpt longtext NOT NULL default '',
			date_created datetime NULL,
			date_modified datetime NULL,
			list_order tinyint default 10,
			status varchar(100) NOT NULL default 'draft',
			PRIMARY KEY (id)";

	} // end set_schema;

} // end class Posts_Table;
