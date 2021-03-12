<?php
/**
 * Domains Functions
 *
 * Public APIs to load and deal with WP Ultimo domains.
 *
 * @author      Arindo Duque
 * @category    Admin
 * @package     WP_Ultimo/Domain
 * @version     2.0.0
 */

use \WP_Ultimo\Models\Domain;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Returns a domain.
 *
 * @since 2.0.0
 *
 * @param int $domain_id The id of the domain. This is not the user ID.
 * @return \WP_Ultimo\Models\Domain|false
 */
function wu_get_domain($domain_id) {

	return \WP_Ultimo\Models\Domain::get_by_id($domain_id);

} // end wu_get_domain;

/**
 * Queries domains.
 *
 * @since 2.0.0
 *
 * @param array $query Query arguments.
 * @return \WP_Ultimo\Models\Domain[]
 */
function wu_get_domains($query = array()) {

	return \WP_Ultimo\Models\Domain::query($query);

} // end wu_get_domains;

/**
 * Returns a domain based on domain.
 *
 * @since 2.0.0
 *
 * @param int $domain The domain url.
 * @return \WP_Ultimo\Models\Domain|false
 */
function wu_get_domain_by_domain($domain) {

	return \WP_Ultimo\Models\Domain::get_by('domain', $domain);

} // end wu_get_domain_by_domain;

/**
 * Creates a new domain.
 *
 * Check the wp_parse_args below to see what parameters are necessary.
 *
 * @since 2.0.0
 *
 * @param array $domain_data Domain attributes.
 * @return \WP_Error|\WP_Ultimo\Models\Domain
 */
function wu_create_domain($domain_data) {

	$domain_data = wp_parse_args($domain_data, array(
		'blog_id'        => false,
		'domain'         => false,
		'active'         => true,
		'primary_domain' => false,
		'secure'         => false,
		'stage'          => 'checking-dns',
		'date_created'   => current_time('mysql'),
		'date_modified'  => current_time('mysql'),
	));

	$domain = new Domain($domain_data);

	$saved = $domain->save();

	if (is_wp_error($saved)) {

		return $saved;

	} // end if;

	/*
	 * Add the processing.
	 */
	wu_enqueue_async_action('wu_async_process_domain_stage', array('domain_id' => $domain->get_id()), 'domain');

	return $domain;

} // end wu_create_domain;
