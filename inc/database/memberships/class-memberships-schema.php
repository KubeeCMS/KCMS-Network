<?php
/**
 * Membership schema class
 *
 * @package WP_Ultimo
 * @subpackage Database\Memberships
 * @since 2.0.0
 */

namespace WP_Ultimo\Database\Memberships;

use WP_Ultimo\Database\Engine\Schema;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Memberships Schema Class.
 *
 * @since 2.0.0
 */
class Memberships_Schema extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @since  2.0.0
	 * @access public
	 * @var array
	 */
	public $columns = array(

		// id
		array(
			'name'       => 'id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'extra'      => 'auto_increment',
			'primary'    => true,
			'sortable'   => true,
			'searchable' => true,
		),

		// customer_id
		array(
			'name'     => 'customer_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
		),

		// user_id
		array(
			'name'       => 'user_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'default'    => null,
			'allow_null' => true
		),

		array(
			'name'     => 'migrated_from_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'sortable' => true,
		),

		// object_id
		array(
			'name'       => 'plan_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'sortable'   => true,
			'transition' => true
		),

		// addons
		array(
			'name' => 'addon_products',
			'type' => 'longtext',
		),

		// currency
		array(
			'name'     => 'currency',
			'type'     => 'varchar',
			'length'   => '20',
			'default'  => 'USD',
			'sortable' => true
		),

		array(
			'name'       => 'initial_amount',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		array(
			'name'       => 'recurring',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 1,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'auto_renew',
			'type'       => 'tinyint',
			'length'     => '4',
			'unsigned'   => true,
			'default'    => 0,
			'transition' => true,
			'sortable'   => true,
		),

		array(
			'name'       => 'duration',
			'type'       => 'smallint',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true,
			'transition' => true
		),

		array(
			'name'    => 'duration_unit',
			'type'    => 'enum(\'day\', \'month\', \'week\', \'year\')',
			'default' => 'none',
		),

		array(
			'name'       => 'amount',
			'type'       => 'decimal(13,4)',
			'default'    => '',
			'sortable'   => true,
			'transition' => true,
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => null,
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// date_activated
		array(
			'name'       => 'date_activated',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true
		),

		// date_trial_end
		array(
			'name'       => 'date_trial_end',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true
		),

		// date_renewed
		array(
			'name'       => 'date_renewed',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true
		),

		// date_cancellation
		array(
			'name'       => 'date_cancellation',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true
		),

		// date_expiration
		array(
			'name'       => 'date_expiration',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'transition' => true
		),

		// date_payment_plan_completed
		array(
			'name'       => 'date_payment_plan_completed',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'transition' => true
		),

		// auto_renew
		array(
			'name'       => 'auto_renew',
			'type'       => 'smallint',
			'unsigned'   => true,
			'default'    => '0',
			'transition' => true
		),

		// times_billed
		array(
			'name'       => 'times_billed',
			'type'       => 'smallint',
			'unsigned'   => true,
			'default'    => '0',
			'sortable'   => true,
			'transition' => true
		),

		// billing_cycles
		array(
			'name'     => 'billing_cycles',
			'type'     => 'smallint',
			'unsigned' => true,
			'default'  => '0',
			'sortable' => true
		),

		// status
		array(
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '12',
			'default'    => 'pending',
			'sortable'   => true,
			'transition' => true,
		),

		// gateway_customer_id
		array(
			'name'       => 'gateway_customer_id',
			'type'       => 'tinytext',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
			'transition' => true
		),

		// gateway_subscription_id
		array(
			'name'       => 'gateway_subscription_id',
			'type'       => 'tinytext',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
			'transition' => true
		),

		// gateway
		array(
			'name'       => 'gateway',
			'type'       => 'tinytext',
			'default'    => '',
			'searchable' => true,
		),

		// signup_method
		array(
			'name'    => 'signup_method',
			'type'    => 'tinytext',
			'default' => '',
		),

		// subscription_key
		array(
			'name'       => 'subscription_key',
			'type'       => 'varchar',
			'length'     => '32',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true
		),

		// upgraded_from
		array(
			'name'     => 'upgraded_from',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => ''
		),

		// date_modified
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => null,
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true
		),

		// disabled
		array(
			'name'     => 'disabled',
			'type'     => 'smallint',
			'unsigned' => true,
			'default'  => '',
			'pattern'  => '%d'
		),

	);

} // end class Memberships_Schema;
