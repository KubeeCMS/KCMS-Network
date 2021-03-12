<?php
/**
 * Support Agents Functions
 *
 * Public APIs to load and deal with WP Ultimo support_agents.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Support Agent
 * @version     2.0.0
 */

use \WP_Ultimo\Models\Support_Agent;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns a support_agent.
 *
 * @since 2.0.0
 *
 * @param int $support_agent_id The id of the support_agent. This is not the user ID.
 * @return \WP_Ultimo\Models\Support_Agent|false
 */
function wu_get_support_agent($support_agent_id) {

	return \WP_Ultimo\Models\Support_Agent::get_by_id($support_agent_id);

} // end wu_get_support_agent;

/**
 * Queries support agents.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Support_Agent[]
 */
function wu_get_support_agents($query = array()) {

	if (!empty($query['search'])) {

		$user_ids = get_users(array(
			'blog_id' => 0,
			'number'  => -1,
			'search'  => '*' . $query['search'] . '*',
			'fields'  => 'ids',
		));

		if (!empty($user_ids)) {

			$query['user_id__in'] = $user_ids;

			unset($query['search']);

		} // end if;

	} // end if;

	/*
	 * Force search limit to support agents only.
	 */
	$query['type__in'] = array('support-agent');

	return \WP_Ultimo\Models\Support_Agent::query($query);

} // end wu_get_support_agents;

/**
 * Returns a support_agent based on user_id.
 *
 * @since 2.0.0
 *
 * @param int $user_id The ID of the WP User associated with that support_agent.
 * @return \WP_Ultimo\Models\Support_Agent|false
 */
function wu_get_support_agent_by_user_id($user_id) {

	return \WP_Ultimo\Models\Support_Agent::get_by('user_id', $user_id);

} // end wu_get_support_agent_by_user_id;

/**
 * Returns the current support_agent.
 *
 * @since 2.0.0
 * @return \WP_Ultimo\Models\Support_Agent|false
 */
function wu_get_current_support_agent() {

	return wu_get_support_agent_by_user_id(get_current_user_id());

} // end wu_get_current_support_agent;

/**
 * Returns the Support Agent capabilities by id user.
 *
 * @since 2.0.0
 *
 * @param int $user_id The ID of the WP User associated with that support_agent.
 *
 * @return array
 */
function wu_get_support_agent_capabilities($user_id) {

	return \WP_Ultimo\Permission_Control::get_instance()->get_support_agent_capabilities_by_id($user_id);

} // end wu_get_support_agent_capabilities;

/**
 * Creates a new support_agent.
 *
 * Check the wp_parse_args below to see what parameters are necessary.
 * If the use_id is not passed, we try to create a new WP User.
 *
 * @since 2.0.0
 *
 * @param array $support_agent_data Support_Agent attributes.
 * @return \WP_Error|\WP_Ultimo\Models\Support_Agent
 */
function wu_create_support_agent($support_agent_data) {

	$support_agent_data = wp_parse_args($support_agent_data, array(
		'user_id'            => false,
		'email'              => false,
		'username'           => false,
		'password'           => false,
		'vip'                => false,
		'ip'                 => false,
		'email_verification' => 'none',
		'meta'               => array(),
		'date_registered'    => current_time('mysql'),
		'date_modified'      => current_time('mysql'),
	));

	$user = get_user_by('email', $support_agent_data['email']);

	if (!$user) {

		$user = get_user_by('ID', $support_agent_data['user_id']);

	} // end if;

	if (!$user) {

		$user_id = wpmu_create_user($support_agent_data['username'], $support_agent_data['password'], $support_agent_data['email']);

		if ($user_id === false) {

			return new \WP_Error('user', __('We were not able to create a new user.', 'wp-ultimo'), $support_agent_data);

		} // end if;

	} else {

		$user_id = $user->ID;

	} // end if;

	$support_agent = new Support_Agent(array(
		'user_id'            => $user_id,
		'email_verification' => 'none',
		'meta'               => $support_agent_data['meta'],
	));

	$saved = $support_agent->save();

	return is_wp_error($saved) ? $saved : $support_agent;

} // end wu_create_support_agent;
