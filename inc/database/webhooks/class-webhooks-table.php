<?php
/**
 * Class used for querying domain mappings.
 *
 * @package WP_Ultimo
 * @subpackage Database\Webhook
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Webhooks;

use WP_Ultimo\Database\Engine\Table;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Setup the "wu_webhooks" database table
 *
 * @since 2.0.0
 */
final class Webhooks_Table extends Table {

	/**
	 * Table name
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $name = 'webhooks';

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
	 * Webhook constructor.
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

		// phpcs:disable

		$this->schema = "id bigint(20) NOT NULL auto_increment,
			migrated_from_id bigint(20) DEFAULT NULL,
			name varchar(191) NOT NULL,
			webhook_url varchar(191) NOT NULL,
			event varchar(40) NOT NULL,
			event_count int(10) default 0,
			active tinyint(4) default 1,
			hidden tinyint(4) default 0,
			integration varchar(191) NOT NULL,
			date_last_failed datetime NOT NULL,
			date_created datetime NULL,
			date_modified datetime NULL,
			PRIMARY KEY (id),
			KEY event (event)";

			// phpcs:enable

	} // end set_schema;

} // end class Webhooks_Table;
