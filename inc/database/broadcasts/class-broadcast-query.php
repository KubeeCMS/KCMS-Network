<?php
/**
 * Class used for querying broadcasts.
 *
 * @package WP_Ultimo
 * @subpackage Database\Posts
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Broadcasts;

use WP_Ultimo\Database\Posts\Post_Query;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Class used for querying broadcasts.
 *
 * @since 2.0.0
 */
class Broadcast_Query extends Post_Query {

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
	protected $item_shape = '\\WP_Ultimo\\Models\\Broadcast';

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'posts';

	/**
	 * Modifies the query call to add our types.
	 *
	 * @since 2.0.0
	 *
	 * @param array $query Query parameters being passed.
	 * @return array
	 */
	public function query($query = array()) {

		$query['type__in'] = array('broadcast_email', 'broadcast_notice');

		return parent::query($query);

	} // end query;

} // end class Broadcast_Query;
