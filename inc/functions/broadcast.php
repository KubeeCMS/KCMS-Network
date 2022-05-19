<?php
/**
 * Broadcast Functions
 *
 * @package WP_Ultimo\Functions
 * @since   2.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

use \WP_Ultimo\Models\Broadcast;

/**
 * Queries broadcast.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Broadcast[]
 */
function wu_get_broadcasts($query = array()) {

	if (!isset($query['type__in'])) {

		$query['type__in'] = array('broadcast_email', 'broadcast_notice');

	} // end if;

	return \WP_Ultimo\Models\Broadcast::query($query);

} // end wu_get_broadcasts;

/**
 * Returns a single broadcast defined by a particular column and value.
 *
 * @since 2.0.7
 *
 * @param string $column The column name.
 * @param mixed  $value The column value.
 * @return \WP_Ultimo\Models\Broadcast|false
 */
function wu_get_broadcast_by($column, $value) {

	$first_attempt = \WP_Ultimo\Models\Broadcast::get_by($column, $value);

	if ($first_attempt) {

		return $first_attempt;

	} // end if;

	$query = array(
		'number'   => 1,
		'type__in' => array('broadcast_email', 'broadcast_notice'),
	);

	$query['meta_query'] = array(
		array(
			'key'   => $column,
			'value' => $value,
		),
	);

	$results = \WP_Ultimo\Models\Broadcast::query($query);

	return !empty($results) ? array_pop($results) : false;

} // end wu_get_broadcast_by;

/**
 * Gets a broadcast on the ID.
 *
 * @since 2.0.0
 *
 * @param integer $broadcast_id ID of the broadcast to retrieve.
 * @return \WP_Ultimo\Models\Broadcast|false
 */
function wu_get_broadcast($broadcast_id) {

	return \WP_Ultimo\Models\Broadcast::get_by_id($broadcast_id);

} // end wu_get_broadcast;

/**
 * Gets a broadcast on the ID.
 *
 * @since 2.0.0
 *
 * @param integer $broadcast_id ID of the broadcast to retrieve.
 * @param string  $type Target type (customers or products).
 * @return array All targets, based on the type, from a specific broadcast.
 */
function wu_get_broadcast_targets($broadcast_id, $type) {

	$object = \WP_Ultimo\Models\Broadcast::get_by_id($broadcast_id);

	$targets = $object->get_message_targets();

	if (!is_array($targets)) {

		return array();

	} // end if;

	if (is_array($targets[$type])) {

		return $targets[$type];

	} elseif (is_string($targets[$type])) {

		return explode(',', $targets[$type]);

	} // end if;

	return array();

} // end wu_get_broadcast_targets;

/**
 * Creates a new broadcast.
 *
 * Check the wp_parse_args below to see what parameters are necessary.
 *
 * @since 2.0.0
 *
 * @param array $broadcast_data Broadcast attributes.
 * @return \WP_Error|\WP_Ultimo\Models\Broadcast
 */
function wu_create_broadcast($broadcast_data) {

	$broadcast_data = wp_parse_args($broadcast_data, array(
		'type'             => 'broadcast_notice',
		'notice_type'      => 'success',
		'date_created'     => wu_get_current_time('mysql', true),
		'date_modified'    => wu_get_current_time('mysql', true),
		'migrated_from_id' => 0,
		'skip_validation'  => false,
		'message_targets'  => array(
			'customers' => array(),
			'products'  => array(),
		),
	));

	$broadcast = new Broadcast($broadcast_data);

	$saved = $broadcast->save();

	return is_wp_error($saved) ? $saved : $broadcast;

} // end wu_create_broadcast;
