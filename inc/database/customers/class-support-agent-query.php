<?php
/**
 * Class used for querying support_agents.
 *
 * @package WP_Ultimo
 * @subpackage Database\Customers
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Customers;

use WP_Ultimo\Database\Engine\Query;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Class used for querying webhooks.
 *
 * @since 2.0.0
 */
class Support_Agent_Query extends Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_name = 'customers';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_alias = 'c';

	/**
	 * Name of class used to setup the database schema
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $table_schema = '\\WP_Ultimo\\Database\\Customers\\Customers_Schema';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name = 'customer';

	/**
	 * Plural version for a group of items.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $item_name_plural = 'support_agents';

	/**
	 * Callback function for turning IDs into objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var mixed
	 */
	protected $item_shape = '\\WP_Ultimo\\Models\\Support_Agent';

	/**
	 * Group to cache queries and queried items in.
	 *
	 * @since  2.0.0
	 * @access public
	 * @var string
	 */
	protected $cache_group = 'support_agents';

	/**
	 * Sets up the customer query, based on the query vars passed.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @param string|array $query Array of query arguments.
	 */
	public function __construct($query = array()) {

		$query['type__in'] = array('support-agent');

		parent::__construct($query);

	} // end __construct;

} // end class Support_Agent_Query;
