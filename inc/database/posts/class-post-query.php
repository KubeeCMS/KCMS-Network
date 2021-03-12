<?php
/**
 * Class used for querying posts.
 *
 * @package WP_Ultimo
 * @subpackage Database\Posts
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Posts;

use WP_Ultimo\Database\Engine\Query;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Class used for querying posts.
 *
 * @since 2.0.0
 */
class Post_Query extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'posts';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'po';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\WP_Ultimo\\Database\\Posts\\Posts_Schema';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'post';

	/**
	 * Plural version for a group of items.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'posts';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\WP_Ultimo\\Models\\Post_Base_Model';

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'posts';

	/**
	 * Sets up the customer query, based on the query vars passed.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @param string|array $query Array of query arguments.
	 */
	public function query($query = array()) {

		if (!isset($query['type__in'])) {

			$query['type'] = $this->item_name;

		} // end if;

		return parent::query($query);

	} // end query;

} // end class Post_Query;
