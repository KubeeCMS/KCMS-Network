<?php
/**
 * Webhook Functions
 *
 * Public APIs to load and deal with WP Ultimo webhook.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Webhooks
 * @version     2.0.0
 */

use \WP_Ultimo\Models\Webhook;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Queries webhook.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Webhook[]
 */
function wu_get_webhooks($query = array()) {

	return \WP_Ultimo\Models\Webhook::query($query);

} // end wu_get_webhooks;

/**
 * Gets a webhook on the ID.
 *
 * @since 2.0.0
 *
 * @param integer $webhook_id ID of the webhook to retrieve.
 * @return @return \WP_Ultimo\Models\Webhook|false
 */
function wu_get_webhook($webhook_id) {

	return \WP_Ultimo\Models\Webhook::get_by_id($webhook_id);

} // end wu_get_webhook;

/**
 * Creates a new webhook.
 *
 * Check the wp_parse_args below to see what parameters are necessary.
 *
 * @since 2.0.0
 *
 * @param array $webhook_data Webhook attributes.
 * @return \WP_Error|\WP_Ultimo\Models\Webhook
 */
function wu_create_webhook($webhook_data) {

	$webhook_data = wp_parse_args($webhook_data, array(
		'name'             => false,
		'webhook_url'      => false,
		'event'            => false,
		'active'           => false,
		'event_count'      => 0,
		'date_created'     => current_time('mysql'),
		'date_modified'    => current_time('mysql'),
		'migrated_from_id' => 0,
	));

	$webhook = new Webhook($webhook_data);

	$saved = $webhook->save();

	return is_wp_error($saved) ? $saved : $webhook;

} // end wu_create_webhook;
