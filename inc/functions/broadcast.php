<?php
/**
 * Broadcast Functions
 *
 * Public APIs to load and deal with WP Ultimo broadcast.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Payment
 * @version     2.0.0
 */

use \WP_Ultimo\Models\Broadcast;

// Exit if accessed directly
defined('ABSPATH') || exit;

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

	if (isset($targets[$type][0])) {

		return explode(',', $targets[$type]);

	} // end if;

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
		'type'          => 'broadcast_notice',
		'notice_type'   => 'success',
		'date_created'  => current_time('mysql'),
		'date_modified' => current_time('mysql'),
	));

	$broadcast = new Broadcast($broadcast_data);

	$saved = $broadcast->save();

	return is_wp_error($saved) ? $saved : $broadcast;

} // end wu_create_broadcast;
